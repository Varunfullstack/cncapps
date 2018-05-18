<?php /**
 * Contact business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBEContactNew.inc.php");
define('BUCONTACT_MATCH_STR_NT_PASD', 'No match string passed');

class BUContact extends Business
{
    var $dbeContact = "";

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeContact = new DBEContact($this);
    }

    /**
     * Get Contact rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter String $nameSearchString String to match against or numeric contactID
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getSupplierContactsByNameMatch($supplierID, $matchString, &$dsResults)
    {
        $this->setMethodName('getSupplierContactsByNameMatch');
        if ($matchString == '') {
            $this->raiseError(BUCONTACT_MATCH_STR_NT_PASD);
        }
        if ($supplierID == '') {
            $this->raiseError('supplierID not passed');
        }
        $matchString = trim($matchString);
        $ret = FALSE;
        if (is_numeric($matchString)) {
            $ret = ($this->getContactByID($matchString, $dsResults));
        }
        if (!$ret) {
            $this->dbeContact->setValue('supplierID', $supplierID);
            if ($matchString{0} == '?') {  // get all contacts for supplier
                $this->dbeContact->getSupplierRows();
            } else {                                                // try to match
                $this->dbeContact->getSupplierContactRowsByNameMatch($matchString);
            }
            $ret = ($this->getData($this->dbeContact, $dsResults));
            $dsResults->columnSort('lastName', 'firstName');
        }
        return $ret;
    }

    /**
     * Get Support Contact rows at all customers
     * @return bool : One or more rows
     * @access public
     */
    function getSupportContacts(&$dsResults, $customerID = false)
    {
        $this->setMethodName('getSupportContacts');
        $this->dbeContact->getSupportRows($customerID);
        $ret = ($this->getData($this->dbeContact, $dsResults));
        $dsResults->columnSort('lastName', 'firstName');
        return $ret;
    }

    function getTechnicalMailshotContacts(&$dsResults)
    {
        $this->setMethodName('getTechnicalMailshotContacts');
        $this->dbeContact->getTechnicalMailshotRows();
        $ret = ($this->getData($this->dbeContact, $dsResults));
        $dsResults->columnSort('lastName', 'firstName');
        return $ret;
    }

    /**
     * Get Contact rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter customerID
     * @parameter siteNo
     * @parameter String $nameSearchString String to match against or numeric contactID
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getCustomerContactsByNameMatch($customerID, $siteNo = '', $matchString, &$dsResults)
    {
        $this->setMethodName('getCustomerContactsByNameMatch');
        if ($matchString == '') {
            $this->raiseError(BUCONTACT_MATCH_STR_NT_PASD);
        }
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        /* for call contacts
                if ($siteNo==''){
                    $this->raiseError('siteNo not passed');
                }
        */
        $matchString = trim($matchString);
        $ret = FALSE;
        if (is_numeric($matchString)) {
            $ret = ($this->getContactByID($matchString, $dsResults));
            if ($ret) {
                if ($dsResults->getValue('customerID') != $customerID) {
                    $ret = false;
                }
            }
        }
        if (!$ret) {
            $this->dbeContact->setValue('customerID', $customerID);
            $this->dbeContact->setValue('siteNo', $siteNo);
            if ($matchString{0} == '?') {  // get all contacts for customer/site
                if ($siteNo != '') {
                    $this->dbeContact->getRowsByCustomerIDSiteNo();
                } else {
                    $this->dbeContact->getRowsByCustomerID(false);
                }
            } else {                                                // try to match
                $this->dbeContact->getCustomerRowsByNameMatch($matchString);
            }
            $ret = ($this->getData($this->dbeContact, $dsResults));
            $dsResults->columnSort('lastName', 'firstName');
        }
        return $ret;
    }

    /**
     * Get contact row by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getGSCContactByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getContactByCustomerID');
        $this->dbeContact->getGSCRowsByCustomerID($customerID);
        $ret = ($this->getData($this->dbeContact, $dsResults));
    }

    /**
     * Get general support contact statement row by customerID
     * @parameter integer $contactID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactByID($ID, &$dsResults)
    {
        $this->setMethodName('getContactByID');
        return ($this->getDatasetByPK($ID, $this->dbeContact, $dsResults));
    }

    /**
     * Create a new dataset containing defaults for new contact row
     * @parameter integer $supplierID for whom to create contact (optional)
     * @parameter integer $customerID for whom to create contact
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function initialiseNewContact($supplierID, $customerID, $siteNo, &$dsResults)
    {
        $this->setMethodName('initialiseNewContact');
        // create/populate new dataset
        if (($supplierID == '') AND ($customerID == '')) {
            $this->raiseError('a supplierID or customerID must be passed');
        }
        if (($customerID != '') AND ($siteNo == '')) {
            $this->raiseError('default siteNo must be passed');
        }
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dsResults->copyColumnsFrom($this->dbeContact);
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue('contactID', 0);
        $dsResults->setValue('siteNo', $siteNo);
        $dsResults->setValue('supplierID', $supplierID);
        $dsResults->setValue('customerID', $customerID);
        $dsResults->setValue('discontinuedFlag', 'N');
        $dsResults->setValue('notes', '');
        $dsResults->setValue('mailshot1Flag', $dsHeader->getValue('mailshot1FlagDef'));
        $dsResults->setValue('mailshot2Flag', $dsHeader->getValue('mailshot2FlagDef'));
        $dsResults->setValue('mailshot3Flag', $dsHeader->getValue('mailshot3FlagDef'));
        $dsResults->setValue('mailshot4Flag', $dsHeader->getValue('mailshot4FlagDef'));
        $dsResults->setValue('mailshot5Flag', $dsHeader->getValue('mailshot5FlagDef'));
        $dsResults->setValue('mailshot6Flag', $dsHeader->getValue('mailshot6FlagDef'));
        $dsResults->setValue('mailshot7Flag', $dsHeader->getValue('mailshot7FlagDef'));
        $dsResults->setValue('mailshot8Flag', $dsHeader->getValue('mailshot8FlagDef'));
        $dsResults->setValue('mailshot9Flag', $dsHeader->getValue('mailshot9FlagDef'));
        $dsResults->setValue('mailshot10Flag', $dsHeader->getValue('mailshot10FlagDef'));
        $dsResults->setValue('mailshot11Flag', $dsHeader->getValue('mailshot11FlagDef'));
        $dsResults->post();
        return TRUE;
    }

    /**
     * Create a new dataset containing defaults contact mailshot flags
     * this is done because HTML posts do not return values for unchecked
     * checkbox fields.
     * @parameter integer $supplierID for whom to create contact
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function initialiseUpdateContact(&$dsResults)
    {
        $this->setMethodName('initialiseUpdateContact');
        // create/populate new dataset
        $dsResults->copyColumnsFrom($this->dbeContact);
        $dsResults->setValue('discontinuedFlag', 'N');
        $dsResults->setValue('mailshot1Flag', 'N');
        $dsResults->setValue('mailshot2Flag', 'N');
        $dsResults->setValue('mailshot3Flag', 'N');
        $dsResults->setValue('mailshot4Flag', 'N');
        $dsResults->setValue('mailshot5Flag', 'N');
        $dsResults->setValue('mailshot6Flag', 'N');
        $dsResults->setValue('mailshot7Flag', 'N');
        $dsResults->setValue('mailshot8Flag', 'N');
        $dsResults->setValue('mailshot9Flag', 'N');
        $dsResults->setValue('mailshot10Flag', 'N');
        return TRUE;
    }

    /**
     * Update/Insert contact to DB
     *    Only handles one row in dataset.
     *
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function updateContact(&$dsContact)
    {
        $this->setMethodName('updateContact');
        return ($this->updateDataaccessObject($dsContact, $this->dbeContact));
    }
}// End of class
?>