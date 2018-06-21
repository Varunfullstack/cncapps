<?php /**
 * Customer business class
 *
 * NOTE: uses new lower-case standard database classes
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBESite.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerType.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
define('BUCUSTOMER_NAME_STR_NT_PASD', 'No name string passed');

class BUCustomer extends Business
{
    protected $dbeCustomer;
    protected $dbeSite;
    /**
     * @var DBEContact $dbeContact
     */
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
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
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
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
        $this->dbeSite->setValue(DBESite::siteNo, $dsCustomer->getValue(DBECustomer::invoiceSiteNo));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue(DBESite::invoiceContactID), $dsContact);
        return TRUE;
    }

    /**
     * Get invoice site by customerID, siteNo
     * @param $customerID
     * @param DataSet $dsResults
     * @param $dsContact
     * @return void : Success
     * @access public
     */
    function getDeliverSiteByCustomerID($customerID, &$dsResults, &$dsContact)
    {
        $this->setMethodName('getDeliverySiteByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        /** @var DataSet $dsCustomer */
        $this->getCustomerByID($customerID, $dsCustomer);
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
        $this->dbeSite->setValue(DBESite::siteNo, $dsCustomer->getValue(DBECustomer::deliverSiteNo));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue(DBESite::deliverContactID), $dsContact);
    }

    /**
     * Get site by customerID and SiteNo
     * @param integer $customerID
     * @param integer $siteNo
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function getSiteByCustomerIDSiteNo($customerID, $siteNo, &$dsResults)
    {
        $this->setMethodName('getSiteByCustomerIDSiteNo');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
        $this->dbeSite->setValue(DBESite::siteNo, $siteNo);
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
        $this->dbeContact->getRowsByCustomerID($customerID);
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
     * @param DataSet $dsData
     * @param DataSet $dsSite
     * @param DataSet $dsContact
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
        $dsSite->setValue(DBESite::deliverContactID, $dsContact->getValue('contactID'));
        $dsSite->setValue(DBESite::invoiceContactID, $dsContact->getValue('contactID'));
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
     * @param DataSet $source
     * @param DataSet $dbeSite
     * @return bool : Success
     * @access public
     */
    function setCustomerID(&$source, &$dbeSite)
    {
        $dbeSite->setValue(DBESite::customerID, $source->getValue(DBESite::customerID));
        return TRUE;
    }

    /**
     * Calculate a unique Sage Reference for new customer site
     * Based upon uppercase first two non-space characters of name plus integer starting at 1 (e.g. KA002)
     * @param DataSet $source
     * @param DataSet $dbeSite
     * @return bool : Success
     * @access public
     */
    function setSageRef(&$source, &$dbeSite)
    {
        $customerName = $this->dbeCustomer->getValue(DBECustomer::name);
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
        $source->setValue(DBESite::sageRef, $sageRef);
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

    /**
     * @param DataSet $dsContact
     * @param $customerID
     * @param DataSet $siteNo
     * @return bool
     */
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
        /** @var DataSet $dsHeader */
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $dsContact->setValue('mailshot1Flag', $dsHeader->getValue("mailshot1FlagDef"));
        $dsContact->setValue('mailshot2Flag', $dsHeader->getValue("mailshot2FlagDef"));
        $dsContact->setValue('mailshot3Flag', $dsHeader->getValue("mailshot3FlagDef"));
        $dsContact->setValue('mailshot4Flag', $dsHeader->getValue("mailshot4FlagDef"));
        $dsContact->setValue('mailshot5Flag', $dsHeader->getValue("mailshot5FlagDef"));
        $dsContact->setValue('mailshot6Flag', $dsHeader->getValue("mailshot6FlagDef"));
        $dsContact->setValue('mailshot7Flag', $dsHeader->getValue("mailshot7FlagDef"));
        $dsContact->setValue('mailshot8Flag', $dsHeader->getValue("mailshot8FlagDef"));
        $dsContact->setValue('mailshot9Flag', $dsHeader->getValue("mailshot9FlagDef"));
        $dsContact->setValue('mailshot10Flag', $dsHeader->getValue("mailshot10FlagDef"));
        $dsContact->post();
        return TRUE;
    }

    /**
     * @param DataSet $dsSite
     * @param $customerID
     * @return bool
     */
    function addNewSiteRow(&$dsSite, $customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
            return FALSE;
        } else {
            $dsSite->clearCurrentRow();
            $dsSite->setUpdateModeInsert();
            $dsSite->setValue(DBESite::customerID, $customerID);
            $dsSite->setValue(DBESite::siteNo, -9);
            $dsSite->setValue(DBESite::add1, 'Address Line 1');
            $dsSite->setValue(DBESite::town, 'TOWN');
            $dsSite->setValue(DBESite::postcode, 'POSTCODE');
            $dsSite->post();
            return TRUE;
        }
    }

    /**
     * @param DataSet $dsCustomer
     */
    function addNewCustomerRow(&$dsCustomer)
    {
        $dsCustomer->clearCurrentRow();
        $dsCustomer->setUpdateModeInsert();
        $dsCustomer->setValue(DBECustomer::customerID, 0);
        $dsCustomer->setValue(DBECustomer::name, 'New Customer');
        $dsCustomer->setValue(DBECustomer::mailshotFlag, 'Y');
        $dsCustomer->setValue(DBECustomer::referredFlag, 'N');
        $dsCustomer->setValue(DBECustomer::prospectFlag, 'Y');
        $dsCustomer->setValue(DBECustomer::createDate, date('Y-m-d'));
        $dsCustomer->setValue(DBECustomer::invoiceSiteNo, 0);
        $dsCustomer->setValue(DBECustomer::deliverSiteNo, 0);
        $dsCustomer->setValue(DBECustomer::customerTypeID, 0);
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
     * @param integer $customerID
     * @param integer $siteNo
     * @param DataSet $dsResults
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
        $this->dbeCustomer->setValue(DBECustomer::prospectFlag, 'N');
        return ($this->dbeCustomer->updateRow());
    }

    /**
     * @param int $contactID
     * @return string
     */
    function getContactPhone($contactID)
    {
        // if we have a contact then get all the phone details for display
        /** @var DataSet $dsContact */
        $this->getContactByID($contactID, $dsContact);
//		$dsContact->fetchNext();
        /** @var DataSet $dsSite */
        $this->getSiteByCustomerIDSiteNo(
            $dsContact->getValue('customerID'),
            $dsContact->getValue('siteNo'),
            $dsSite
        );
        $dsContact->fetchNext();

        if ($dsSite->getValue(DBESite::phone) != '') {
            $contactPhone = $dsSite->getValue(DBESite::phone);
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
        /** @var DataSet $dsContact */
        $this->getContactByID($contactID, $dsContact);
        /** @var DataSet $dsSite */
        $this->getSiteByCustomerIDSiteNo(
            $dsContact->getValue('customerID'),
            $dsContact->getValue('siteNo'),
            $dsSite
        );
        $dsContact->fetchNext();

        if ($dsSite->getValue(DBESite::phone) != '') {
            $contactPhone = '<a href="tel:' . str_replace(' ',
                                                          '',
                                                          $dsSite->getValue(DBESite::phone)) . '">' . $dsSite->getValue(DBESite::phone) . '</a>';
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
     * @return bool : Success
     * @access public
     */
    function getMainSupportEmailAddresses($customerID, $excludeEmail)
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

        return CUSTOMER_DIR . '/' . $this->dbeCustomer->getValue(DBECustomer::name);

    }

    function getCustomerFolderPathFromBrowser($customerID)
    {

        $this->dbeCustomer->getRow($customerID);

        return CUSTOMER_DIR_FROM_BROWSER . '/' . $this->dbeCustomer->getValue(DBECustomer::name);

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