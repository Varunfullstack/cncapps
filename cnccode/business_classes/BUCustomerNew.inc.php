<?php /**
 * Customer business class
 *
 * NOTE: uses new lower-case standard database classes
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerNew.inc.php");
require_once($cfg["path_dbe"] . "/DBESiteNew.inc.php");
require_once($cfg["path_dbe"] . "/DBEContactNew.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerType.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
define('BUCUSTOMER_NAME_STR_NT_PASD', 'No name string passed');

class BUCustomer extends Business
{
    protected $dbeCustomer;
    protected $dbeSite;
    protected $dbeContact;
    protected $dbeCustomerType;
    protected $dsHeader;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeCustomer = new DBECustomer($this);
        $this->dbeSite = new DBESite($this);
        $this->dbeContact = new DBEContact($this);
        $this->dbeCustomerType = new DBECustomerType($this);
    }

    /**
     * Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter String $nameSearchString String to match against or numeric customerID
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getCustomersByNameMatch($contactString, $phoneString, $nameMatchString, $town, &$dsResults)
    {
        $this->setMethodName('getCustomersByNameMatch');
        $nameMatchString = trim($nameMatchString);
        if (is_numeric($nameMatchString)) {
            $ret = ($this->getCustomerByID($nameMatchString, $dsResults));
        } else {
            $this->dbeCustomer->getRowsByNameMatch($contactString, $phoneString, $nameMatchString, $town);
            $ret = ($this->getData($this->dbeCustomer, $dsResults));
        }
        return $ret;
    }

    /**
     * Get customer row by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getCustomerByID($customerID, &$dsResults)
    {
        $this->setMethodName('getCustomerByID');
        return ($this->getDatasetByPK($customerID, $this->dbeCustomer, $dsResults));
    }

    /**
     * Get site rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getSitesByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getSitesByCustomerID');
        if ($customerID == '') {
            $this->raiseError('CustomerID not passed');
        }
        $this->dbeSite->setValue("customerID", $customerID);
        $this->dbeSite->getRowsByCustomerID();
        return ($this->getData($this->dbeSite, $dsResults));
    }

    /**
     * Get invoice site by customerID
     * @parameter integer $customerID
     * @parameter integer $siteNo
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getInvoiceSiteByCustomerID($customerID, &$dsResults, &$dsContact)
    {
        $this->setMethodName('getInvoiceSiteByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->getCustomerByID($customerID, $dsCustomer);
        $this->dbeSite->setValue("customerID", $customerID);
        $this->dbeSite->setValue("siteNo", $dsCustomer->getValue('invSiteNo'));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue('invContactID'), $dsContact);
        return TRUE;
    }

    /**
     * Get invoice site by customerID, siteNo
     * @parameter integer $customerID
     * @parameter integer $siteNo
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getDeliverSiteByCustomerID($customerID, &$dsResults, &$dsContact)
    {
        $this->setMethodName('getDeliverySiteByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->getCustomerByID($customerID, $dsCustomer);
        $this->dbeSite->setValue("customerID", $customerID);
        $this->dbeSite->setValue("siteNo", $dsCustomer->getValue('delSiteNo'));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue('delContactID'), $dsContact);
    }

    /**
     * Get site by customerID and SiteNo
     * @parameter integer $customerID
     * @parameter integer $siteNo
     * @parameter DataSet &$dsResults results
     * @parameter Boolean $supportContact flag to select support contacts only.
     * @return bool : Success
     * @access public
     */
    function getSiteByCustomerIDSiteNo($customerID, $siteNo, &$dsResults)
    {
        $this->setMethodName('getSiteByCustomerIDSiteNo');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->dbeSite->setValue("customerID", $customerID);
        $this->dbeSite->setValue("siteNo", $siteNo);
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        return TRUE;
    }

    /**
     * Get contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getContactsByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->setValue("customerID", $customerID);
        $this->dbeContact->getRowsByCustomerID();
        return ($this->getData($this->dbeContact, $dsResults));
    }

    /**
     * Get contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactByID($contactID, &$dsResults)
    {
        $this->setMethodName('getContactByID');
        if ($contactID == '') {
            $this->raiseError('contactID not passed');
        }
        return ($this->getDatasetByPK($contactID, $this->dbeContact, $dsResults));
    }

    /**
     * Get all customer types
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getCustomerTypes(&$dsResults)
    {
        $this->setMethodName('getCustomerTypes');
        $this->dbeCustomerType->getRows();
        return ($this->getData($this->dbeCustomerType, $dsResults));
    }

    /**
     * Update customer
     * @parameter DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function updateCustomer(&$dsData)
    {
        $this->setMethodName('updateCustomer');
        return ($this->updateDataaccessObject($dsData, $this->dbeCustomer));
    }

    /**
     * Insert customer
     * This also creates site and contact row to be completed. We pass dsSite and dsContact by ref for use afterwards
     * to avoid having to query the database from CTCustomer
     * @parameter DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function insertCustomer(&$dsData, &$dsSite, &$dsContact)
    {
        $this->setMethodName('insertCustomer');
        $ret = ($this->updateCustomer($dsData));
        $this->addNewSiteRow($dsSite, $dsData->getValue('customerID'));                        // New customerID
        $dsSite->initialise();
        $this->dbeSite->setCallbackMethod(DA_BEFORE_POST, $this, 'setSageRef');
        $ret = $ret & ($this->updateSite($dsSite));
        $this->dbeSite->resetCallbackMethod(DA_BEFORE_POST);
        $this->addNewContactRow($dsContact, $dsData->getValue('customerID'), '0'); // First siteno always zero
        $ret = $ret & ($this->updateContact($dsContact));
        $dsSite->setUpdateModeUpdate();
        $dsSite->setValue('delContactID', $dsContact->getValue('contactID'));
        $dsSite->setValue('invContactID', $dsContact->getValue('contactID'));
        $dsSite->post();
        $ret = $ret & ($this->updateSite($dsSite));        // Then update site delivery and invoice contacts
        return $ret;
    }

    /**
     * Update site
     * @parameter DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function updateSite(&$dsData)
    {
        $this->setMethodName('updateSite');
        $this->dbeSite->setCallbackMethod(DA_AFTER_COLUMNS_CREATED, $this, 'setCustomerID');
        $ret = ($this->updateDataaccessObject($dsData, $this->dbeSite));
        $this->dbeSite->resetCallbackMethod(DA_AFTER_COLUMNS_CREATED);
        return $ret;
    }

    /**
     * by default, replicate() function only sets the siteNo (PK column) before setUPdateModeUpdate
     * so we jump in to set the customerID as well because DBESite has a composite PK
     * @parameter DataSet &$dsData dataset Not used
     * @parameter dbeEntity &$dbeSite site database entity
     * @return bool : Success
     * @access public
     */
    function setCustomerID(&$source, &$dbeSite)
    {
        $dbeSite->setValue('customerID', $source->getValue('customerID'));
        return TRUE;
    }

    /**
     * Calculate a unique Sage Reference for new customer site
     * Based upon uppercase first two non-space characters of name plus integer starting at 1 (e.g. KA002)
     * @parameter DataSet &$source dataset
     * @parameter dbeEntity &$dbeSite site database entity
     * @return bool : Success
     * @access public
     */
    function setSageRef(&$source, &$dbeSite)
    {
        $customerName = $this->dbeCustomer->getValue('name');
        $shortCode = "";
        for ($ixChar = 0; $ixChar <= strlen($customerName); $ixChar++) {
            if (substr($customerName, $ixChar, 1) != " ") {
                $shortCode = $shortCode . strtoupper(substr($customerName, $ixChar, 1));
                if (strlen($shortCode) == 2) {
                    break;
                }
            }
        }
        $number = 1;
        $numberUnique = FALSE;
        $dbeSite = new DBESite($this);                // Just for sageRef check
        while (!$numberUnique) {
            $sageRef = $shortCode . str_pad($number, 3, "0", STR_PAD_LEFT);
            $numberUnique = $dbeSite->uniqueSageRef($sageRef);
            $number++;
        }
        $source->setValue('sageRef', $sageRef);
        return TRUE;
    }

    /**
     * Update contact
     * @parameter DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function updateContact(&$dsData)
    {
        $this->setMethodName('updateContact');
        return ($this->updateDataaccessObject($dsData, $this->dbeContact));
    }

    function addNewContactRow(&$dsContact, $customerID, $siteNo)
    {
        $this->setMethodName('addNewContactRow');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
            return FALSE;
        }
        if ($siteNo == '') {
            $this->raiseError('siteNo not passed');
            return FALSE;
        }
        $dsContact->clearCurrentRow();
        $dsContact->setUpdateModeInsert();
        $dsContact->setValue('contactID', 0);
        $dsContact->setValue('customerID', $customerID);
        $dsContact->setValue('firstName', 'First Name');
        $dsContact->setValue('lastName', 'Last Name');
        $dsContact->setValue('siteNo', $siteNo);
        $dsContact->setValue('discontinuedFlag', 'N');
        $dsContact->setValue('sendMailshotFlag', 'Y');
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $dsContact->setValue('mailshot2Flag', $dsHeader->getValue("mailshot2FlagDef"));
        $dsContact->setValue('mailshot3Flag', $dsHeader->getValue("mailshot3FlagDef"));
        $dsContact->setValue('mailshot4Flag', $dsHeader->getValue("mailshot4FlagDef"));
        $dsContact->setValue('mailshot5Flag', $dsHeader->getValue("mailshot5FlagDef"));
        $dsContact->setValue('mailshot8Flag', $dsHeader->getValue("mailshot8FlagDef"));
        $dsContact->setValue('mailshot9Flag', $dsHeader->getValue("mailshot9FlagDef"));
        $dsContact->setValue('mailshot10Flag', $dsHeader->getValue("mailshot10FlagDef"));
        $dsContact->post();
        return TRUE;
    }

    function addNewSiteRow(&$dsSite, $customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
            return FALSE;
        } else {
            $dsSite->clearCurrentRow();
            $dsSite->setUpdateModeInsert();
            $dsSite->setValue('customerID', $customerID);
            $dsSite->setValue('siteNo', -9);
            $dsSite->setValue('add1', 'Address Line 1');
            $dsSite->setValue('town', 'TOWN');
            $dsSite->setValue('postcode', 'POSTCODE');
            $dsSite->post();
            return TRUE;
        }
    }

    function addNewCustomerRow(&$dsCustomer)
    {
        $dsCustomer->clearCurrentRow();
        $dsCustomer->setUpdateModeInsert();
        $dsCustomer->setValue('customerID', 0);
        $dsCustomer->setValue('name', 'New Customer');
        $dsCustomer->setValue('mailshotFlag', 'Y');
        $dsCustomer->setValue('referredFlag', 'N');
        $dsCustomer->setValue('prospectFlag', 'Y');
        $dsCustomer->setValue('createDate', date('Y-m-d'));
        $dsCustomer->setValue('invSiteNo', 0);
        $dsCustomer->setValue('delSiteNo', 0);
        $dsCustomer->setValue('customerTypeID', 0);
        $dsCustomer->post();
    }

    /**
     * Get contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerIDSiteNo($customerID, $siteNo, &$dsResults, $supportContacts = false)
    {
        $this->setMethodName('getContactsByCustomerIDSiteNo');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        if ($siteNo == '') {
            $this->raiseError('siteNo not passed');
        }
        $this->dbeContact->setValue("customerID", $customerID);
        $this->dbeContact->setValue("siteNo", $siteNo);
        $this->dbeContact->getRowsByCustomerIDSiteNo($supportContacts);
        return ($this->getData($this->dbeContact, $dsResults));
    }

    /**
     * Get support contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getSupportContactsByCustomerIDSiteNo($customerID, $siteNo, &$dsResults)
    {
        $this->setMethodName('getSupportContactsByCustomerIDSiteNo');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        if ($siteNo == '') {
            $this->raiseError('siteNo not passed');
        }
        $this->dbeContact->setValue("customerID", $customerID);
        $this->dbeContact->setValue("siteNo", $siteNo);
        $this->dbeContact->getSupportRowsByCustomerIDSiteNo();
        return ($this->getData($this->dbeContact, $dsResults));
    }

    /**
     * Is contact a nominated support contact
     * @parameter integer $contactID
     * @return bool : True = support contact
     * @access public
     */
    function isASupportContact($contactID)
    {
        $this->setMethodName('isASupportContact');
        if ($contactID == '') {
            $this->raiseError('contactID not passed');
        }
        $this->dbeContact->getRow($contactID);
        if ($this->dbeContact->getValue('mailshot5Flag') == 'Y') {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

    function setProspectFlagOff($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        $this->dbeCustomer->setValue('prospectFlag', 'N');
        return ($this->dbeCustomer->updateRow());
    }

    function getContactPhone($contactID)
    {
        // if we have a contact then get all the phone details for display
        $this->getContactByID($contactID, $dsContact);
//		$dsContact->fetchNext();

        $this->getSiteByCustomerIDSiteNo($dsContact->getValue('customerID'), $dsContact->getValue('siteNo'), $dsSite);
        $dsContact->fetchNext();

        if ($dsSite->getValue('phone') != '') {
            $contactPhone = $dsSite->getValue('phone');
        }
        if ($dsContact->getValue('phone') != '') {
            $contactPhone .= ' DDI: ' . $dsContact->getValue('phone');
        }
        if ($dsContact->getValue('mobilePhone') != '') {
            $contactPhone .= ' Mobile: ' . $dsContact->getValue('mobilePhone');
        }
        return $contactPhone;
    }

    /**
     * This version includes tel: tags for soft phone dialing from browser
     */
    function getContactPhoneForHtml($contactID)
    {

        $this->getContactByID($contactID, $dsContact);

        $this->getSiteByCustomerIDSiteNo($dsContact->getValue('customerID'), $dsContact->getValue('siteNo'), $dsSite);
        $dsContact->fetchNext();

        if ($dsSite->getValue('phone') != '') {
            $contactPhone = '<a href="tel:' . str_replace(' ',
                                                          '',
                                                          $dsSite->getValue('phone')) . '">' . $dsSite->getValue('phone') . '</a>';
        }
        if ($dsContact->getValue('phone') != '') {
            $contactPhone .= ' DDI: <a href="tel:' . str_replace(' ',
                                                                 '',
                                                                 $dsContact->getValue('phone')) . '">' . $dsContact->getValue('phone') . '</a>';
        }
        if ($dsContact->getValue('mobilePhone') != '') {
            $contactPhone .= ' Mobile: <a href="tel:' . str_replace(' ',
                                                                    '',
                                                                    $dsContact->getValue('mobilePhone')) . '">' . $dsContact->getValue('mobilePhone') . '</a>';
        }
        return $contactPhone;
    }

    /**
     * Get all the invoice contacts
     * @parameter CustomerID CustomerID
     * @return bool : Success
     * @access public
     */
    function getInvoiceContactsByCustomerID($customerID, &$dsData)
    {
        $this->setMethodName('getInvoiceContactsByCustomerID');

        $this->dbeContact->getInvoiceContactsByCustomerID($customerID);

        $ret = $this->getData($this->dbeContact, $dsData);
        return $ret;

    }


    /**
     * Get main support contact rows by customerID
     * i.e. those contacts with mailFlag10 = Y
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @param $customerID
     * @param $excludeEmail
     * @param null $flag
     * @return bool : Success
     * @access public
     */
    function getMainSupportEmailAddresses($customerID, $excludeEmail, $flag = null)
    {
        $this->setMethodName('getMainSupportEmailAddresses');

        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }

        $this->dbeContact->getMainSupportRowsByCustomerID($customerID);

        $emailList = false;

        while ($this->dbeContact->fetchNext()) {

            // exclude excluded or duplicated emails
            if (
                ($this->dbeContact->getValue('email') != $excludeEmail)
                AND
                (strpos($this->dbeContact->getValue('email'), $emailList) == FALSE)
                and (!$flag or $this->dbeContact->getValue($flag) === 'Y')
            ) {
                $emailList .= $this->dbeContact->getValue('email') . ',';

            }

        }

        if ($emailList) {
            return substr($emailList, 0, -1);            // remove trailing comma
        } else {
            return false;
        }

    }

    /**
     * @param $customerID
     * @return array
     */
    function getMainSupportContacts($customerID)
    {
        $this->setMethodName('getMainSupportContacts');

        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }

        $this->dbeContact->getMainSupportRowsByCustomerID($customerID);
        $contacts = [];
        while ($this->dbeContact->fetchNext()) {
            $contacts[] = [
                "firstName" => $this->dbeContact->getValue('firstName'),
                "lastName"  => $this->dbeContact->getValue('lastName')
            ];
        }

        return $contacts;
    }

    function createCustomerFolder($customerID)
    {

        $dir = $this->getCustomerFolderPath($customerID);

        /* check to see if the folder exists */
        if (!is_dir($dir)) {

            mkdir($dir);

        }

    }

    function customerFolderExists($customerID)
    {

        $dir = $this->getCustomerFolderPath($customerID);

        if (is_dir($dir)) {
            return $this->getCustomerFolderPathFromBrowser($customerID);
        } else {
            return false;
        }

    }

    function getCurrentDocumentsFolderPath($customerID)
    {

        return $this->getCustomerFolderPath($customerID) . '/Current Documentation';

    }

    function getCurrentDocumentsFolderPathFromBrowser($customerID)
    {

        return $this->getCustomerFolderPathFromBrowser($customerID) . '/Current Documentation';

    }

    function checkCurrentDocumentsFolderExists($customerID)
    {

        $dir = $this->getCurrentDocumentsFolderPath($customerID);

        /* check to see if the folder exists */
        if (!is_dir($dir)) {

            mkdir($dir);

        }

        return $this->getCurrentDocumentsFolderPathFromBrowser($customerID);

    }

    function getCustomerFolderPath($customerID)
    {

        $this->dbeCustomer->getRow($customerID);

        return CUSTOMER_DIR . '/' . $this->dbeCustomer->getValue('name');

    }

    function getCustomerFolderPathFromBrowser($customerID)
    {

        $this->dbeCustomer->getRow($customerID);

        return CUSTOMER_DIR_FROM_BROWSER . '/' . $this->dbeCustomer->getValue('name');

    }

    function getDailyCallList(&$dsResults, $sortColumn = false)
    {
        if ($this->owner->hasPermissions(PHPLIB_PERM_TECHNICAL)) {
            $reviewUserID = false;
        } else {
            $reviewUserID = $GLOBALS['auth']->is_authenticated();
        }

        $this->dbeCustomer->getReviewList($reviewUserID, $sortColumn);

        $ret = $this->getData($this->dbeCustomer, $dsResults);

        return $ret;
    }
}// End of class
?>