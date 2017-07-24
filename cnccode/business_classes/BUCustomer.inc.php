<?php /**
 * Customer business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBESite.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerType.inc.php");
require_once($cfg["path_dbe"] . "/DBELeadStatus.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
define('BUCUSTOMER_NAME_STR_NT_PASD', 'No name string passed');

class BUCustomer extends Business
{
    var $dbeCustomer = "";
    var $dbeSite = "";
    var $dbeContact = "";
    var $dbeCustomerType = "";
    var $buHeader = '';
    var $dsHeader = '';

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
        $this->buHeader = new BUHeader($this);
        $this->buHeader->getHeader($this->dsHeader);
        $this->dsHeader->fetchNext();
    }

    /**
     * Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter String $nameSearchString String to match against or numeric customerID
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getCustomersByNameMatch(
        $contactString = '',
        $phoneString = '',
        $nameMatchString = '',
        $town = '',
        $newCustomerFromDate = '',
        $newCustomerToDate = '',
        $droppedCustomerFromDate = '',
        $droppedCustomerToDate = '',
        &$dsResults
    )
    {
        $this->setMethodName('getCustomersByNameMatch');
        $nameMatchString = trim($nameMatchString);
        if (is_numeric($nameMatchString)) {
            $ret = ($this->getCustomerByID($nameMatchString, $dsResults));
        } else {
            $this->dbeCustomer->getRowsByNameMatch(
                $contactString,
                $phoneString,
                $nameMatchString,
                $town,
                $newCustomerFromDate,
                $newCustomerToDate,
                $droppedCustomerFromDate,
                $droppedCustomerToDate
            );
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
    function getSitesByCustomerID($customerID, &$dsResults, $showInactiveSites)
    {
        $this->setMethodName('getSitesByCustomerID');
        if ($customerID == '') {
            $this->raiseError('CustomerID not passed');
        }
        $this->dbeSite->setValue("CustomerID", $customerID);
        if ($showInactiveSites) {
            $activeFlag = 'N';
        } else {
            $activeFlag = 'Y';
        }
        $this->dbeSite->getRowsByCustomerID($activeFlag);
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
            $this->raiseError('CustomerID not passed');
        }
        $this->getCustomerByID($customerID, $dsCustomer);
        $this->dbeSite->setValue("CustomerID", $customerID);
        $this->dbeSite->setValue("SiteNo", $dsCustomer->getValue('InvoiceSiteNo'));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue('InvoiceContactID'), $dsContact);
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
            $this->raiseError('CustomerID not passed');
        }
        $this->getCustomerByID($customerID, $dsCustomer);
        $this->dbeSite->setValue("CustomerID", $customerID);
        $this->dbeSite->setValue("SiteNo", $dsCustomer->getValue('DeliverSiteNo'));
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData($this->dbeSite, $dsResults);
        $this->getContactByID($dsResults->getValue('DeliverContactID'), $dsContact);
    }

    /**
     * Get contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerID($customerID, &$dsResults, $includeInactive = false)
    {
        $this->setMethodName('getContactsByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->setValue("CustomerID", $customerID);
        $this->dbeContact->getRowsByCustomerID($includeInactive);
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
        $this->dbeCustomerType->getRows('description');
        return ($this->getData($this->dbeCustomerType, $dsResults));
    }

    /**
     * Get all lead status rows
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getLeadStatus(&$dsResults)
    {
        $this->setMethodName('getLeadStatus');
        $dbeLeadStatus = new DBELeadStatus($this);
        $dbeLeadStatus->getRows();
        return ($this->getData($dbeLeadStatus, $dsResults));
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
        if ($dsData->getValue('Name') == '') {
            $this->raiseError('Customer Name is empty!');
            exit;
        }
        if ($dsData->getValue('sectorID') == 0) {
            $this->raiseError('Sector not set set!');
            exit;
        }
        $dsData->setValue('modifyDate', date('Y-m-d H:i:s'));
        $dsData->setValue('modifyUserID', $GLOBALS ['auth']->is_authenticated());

        $this->dbeCustomer->setCallbackMethod(DA_BEFORE_POST, $this, 'beforeUpdateCustomer');

        return ($this->updateDataaccessObject($dsData, $this->dbeCustomer));
    }

    function beforeUpdateCustomer(&$newRow)
    {

        $customerID = $newRow->getPkValue();
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $first = $dbeCustomer->getValue('lastReviewMeetingDate');
        $second = $newRow->getValue('lastReviewMeetingDate');
        if ($dbeCustomer->getValue('lastReviewMeetingDate') != $newRow->getValue('lastReviewMeetingDate')) {
            $newRow->setValue('reviewMeetingEmailSentFlag', 'N');
        }

    }

    function updateModify($customerID)
    {
        if (!$customerID) {
            $this->raiseError('customerID not set');
        }
        $this->setMethodName('updateModify');
        $this->dbeCustomer->getRow($customerID);
        if ($this->dbeCustomer->getValue('Name') == '') {
            $this->raiseError('Customer Name is empty for customer ' . $customerID);
            exit;
        }
        $this->dbeCustomer->setValue('modifyDate', date('Y-m-d H:i:s'));
        $this->dbeCustomer->setValue('modifyUserID', $GLOBALS ['auth']->is_authenticated());
        $this->dbeCustomer->updateRow();
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
        $this->addNewSiteRow($dsSite, $dsData->getValue('CustomerID'));                        // New customerID
        $dsSite->initialise();
        $this->dbeSite->setCallbackMethod(DA_BEFORE_POST, $this, 'setSageRef');
        $ret = $ret & ($this->updateSite($dsSite));
        $this->dbeSite->resetCallbackMethod(DA_BEFORE_POST);
        $this->addNewContactRow($dsContact, $dsData->getValue('CustomerID'), '0'); // First siteno always zero
        $ret = $ret & ($this->updateContact($dsContact));
        $dsSite->setUpdateModeUpdate();
        $dsSite->setValue('DeliverContactID', $dsContact->getValue('ContactID'));
        $dsSite->setValue('InvoiceContactID', $dsContact->getValue('ContactID'));
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
        $this->updateModify($dsData->getValue('CustomerID'));
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
        $dbeSite->setValue('CustomerID', $source->getValue('CustomerID'));
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
        $customerName = $this->dbeCustomer->getValue('Name');
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
        $source->setValue('SageRef', $sageRef);
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
        $ret = $this->updateDataaccessObject($dsData, $this->dbeContact);
        $this->updateModify($dsData->getValue('CustomerID'));
        return $ret;

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
        $dsContact->setValue('ContactID', 0);
        $dsContact->setValue('CustomerID', $customerID);
        $dsContact->setValue('FirstName', 'First Name');
        $dsContact->setValue('LastName', 'Last Name');
        $dsContact->setValue('SiteNo', $siteNo);
        $dsContact->setValue('DiscontinuedFlag', 'N');
        $dsContact->setValue('SendMailshotFlag', 'Y');
        $dsContact->setValue('AccountsFlag', 'N');
        $dsContact->setValue('StatementFlag', 'N');
        $dsContact->setValue('Mailshot1Flag', $this->dsHeader->getValue("mailshot1FlagDef"));
        $dsContact->setValue('Mailshot2Flag', $this->dsHeader->getValue("mailshot2FlagDef"));
        $dsContact->setValue('Mailshot3Flag', $this->dsHeader->getValue("mailshot3FlagDef"));
        $dsContact->setValue('Mailshot4Flag', $this->dsHeader->getValue("mailshot4FlagDef"));
        $dsContact->setValue('Mailshot5Flag', $this->dsHeader->getValue("mailshot5FlagDef"));
        $dsContact->setValue('Mailshot6Flag', $this->dsHeader->getValue("mailshot6FlagDef"));
        $dsContact->setValue('Mailshot7Flag', $this->dsHeader->getValue("mailshot7FlagDef"));
        $dsContact->setValue('Mailshot8Flag', $this->dsHeader->getValue("mailshot8FlagDef"));
        $dsContact->setValue('Mailshot9Flag', $this->dsHeader->getValue("mailshot9FlagDef"));
        $dsContact->setValue('Mailshot10Flag', $this->dsHeader->getValue("mailshot10FlagDef"));
        $dsContact->post();
        $this->updateModify($dsContact->getValue('CustomerID'));
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
            $dsSite->setValue('CustomerID', $customerID);
            $dsSite->setValue('ActiveFlag', 'Y');
            $dsSite->setValue('SiteNo', -9);
            $dsSite->setValue('Add1', 'Address Line 1');
            $dsSite->setValue('Town', 'TOWN');
            $dsSite->setValue('MaxTravelHours', -1);    // means not set because 0 is now a valid distance
            $dsSite->setValue('Postcode', 'POSTCODE');
            $dsSite->post();
//			$this->updateModify($dsSite->getValue('CustomerID'));
            return TRUE;
        }
    }

    function addNewCustomerRow(&$dsCustomer)
    {
        $dsCustomer->clearCurrentRow();
        $dsCustomer->setUpdateModeInsert();
        $dsCustomer->setValue('CustomerID', 0);
        $dsCustomer->setValue('Name', 'New Customer');
        $dsCustomer->setValue('MailshotFlag', 'Y');
        $dsCustomer->setValue('ReferredFlag', 'N');
        $dsCustomer->setValue('ProspectFlag', 'Y');
        $dsCustomer->setValue('OthersEmailMainFlag', 'Y');
        $dsCustomer->setValue('WorkStartedEmailMainFlag', 'Y');
        $dsCustomer->setValue('AutoCloseEmailMainFlag', 'Y');
        $dsCustomer->setValue('CreateDate', date('Y-m-d'));
        $dsCustomer->setValue('InvoiceSiteNo', 0);
        $dsCustomer->setValue('DeliverSiteNo', 0);
        $dsCustomer->setValue('CustomerTypeID', 0);

        $dsCustomer->setValue('PCXFlag', 'N');          // 2nd site
        $dsCustomer->setValue('specialAttentionFlag', 'N');
        $dsCustomer->setValue('support24HourFlag', 'N');

        $dsCustomer->setValue('modifyDate', date('Y-m-d H:i:s'));
        $dsCustomer->post();
    }

    function setProspectFlagOff($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        $this->dbeCustomer->setValue('prospectFlag', 'N');
        $this->dbeCustomer->setValue('modifyDate', date('Y-m-d H:i:s'));
        return ($this->dbeCustomer->updateRow());
    }

    /**
     *    Check dependent tables:
     *    Calls
     *    Customer Items
     * Sales Orders
     * Invoices
     */
    function canDeleteCustomer($customerID, $userID)
    {
        global $cfg;

        if ($userID != USER_GJ) {
            return false;
        }

        // sales orders
        require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setValue('customerID', $customerID);
        if ($dbeOrdhead->countRowsByColumn('customerID') > 0) {
            return FALSE;
        }
        // calls
        require_once($cfg['path_dbe'] . '/DBEProblem.inc.php');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue('customerID', $customerID);
        if ($dbeProblem->countRowsByColumn('customerID') > 0) {
            return FALSE;
        }
        // customer items
        require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->setValue('customerID', $customerID);
        if ($dbeCustomerItem->countRowsByColumn('customerID') > 0) {
            return FALSE;
        }
        // invoices
        require_once($cfg['path_dbe'] . '/DBEInvhead.inc.php');
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setValue('customerID', $customerID);
        if ($dbeInvhead->countRowsByColumn('customerID') > 0) {
            return FALSE;
        }
        // customer notes
        require_once($cfg['path_dbe'] . '/DBECustomerNote.inc.php');
        $dbeCustomerNote = new DBECustomerNote($this);
        $dbeCustomerNote->setValue('customerID', $customerID);
        if ($dbeCustomerNote->countRowsByColumn('customerID') > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete customers, sites and contacts
     */
    function deleteCustomer($customerID)
    {
        $this->dbeContact->setValue('CustomerID', $customerID);
        $this->dbeContact->deleteRowsByCustomerID();
        $this->dbeSite->setValue('CustomerID', $customerID);
        $this->dbeSite->deleteRowsByCustomerID();
        $this->dbeCustomer->setPKValue($customerID);
        $this->dbeCustomer->deleteRow();
    }

    /**
     *    Check dependent tables:
     *    Calls
     *    Customer Items
     * Sales Orders
     * Invoices
     */
    function canDeleteSite($customerID, $siteNo)
    {
        global $cfg;
        // sales orders
        require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
        $dbeOrdhead = new DBEOrdhead($this);
        if ($dbeOrdhead->countRowsByCustomerSiteNo($customerID, $siteNo) > 0) {
            return FALSE;
        }
        // sales invoices
        require_once($cfg['path_dbe'] . '/DBEInvhead.inc.php');
        $dbeInvhead = new DBEInvhead($this);
        if ($dbeInvhead->countRowsByCustomerSiteNo($customerID, $siteNo) > 0) {
            return FALSE;
        }
        // calls
        require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
        $dbeCallActivity = new DBECallActivity($this);
        if ($dbeCallActivity->countRowsByCustomerSiteNo($customerID, $siteNo) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete sites and contacts
     */
    function deleteSite($customerID, $siteNo)
    {
        $this->dbeContact->setValue('CustomerID', $customerID);
        $this->dbeContact->setValue('SiteNo', $siteNo);
        $this->dbeContact->deleteRowsByCustomerIDSiteNo();
        $this->dbeSite->setValue('CustomerID', $customerID);
        $this->dbeSite->setValue('SiteNo', $siteNo);
        $this->dbeSite->deleteRow();
        $this->updateModify($customerID);
    }

    /**
     *    Check whether allowed to delete contact
     * Check dependent tables:
     * Calls
     *    Customer Items
     * Sales Orders
     * Invoices
     */
    function canDeleteContact($contactID)
    {
        global $cfg;
        // sales orders
        require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
        $dbeOrdhead = new DBEOrdhead($this);
        if ($dbeOrdhead->countRowsByContactID($contactID) > 0) {
            return FALSE;
        }
        // sales invoices
        require_once($cfg['path_dbe'] . '/DBEInvhead.inc.php');
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setValue('contactID', $contactID);
        if ($dbeInvhead->countRowsByColumn('contactID') > 0) {
            return FALSE;
        }
        // calls
        require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue('contactID', $contactID);
        if ($dbeCallActivity->countRowsByColumn('contactID') > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete contact
     */
    function deleteContact($contactID)
    {
        $this->dbeContact->setValue('ContactID', $contactID);
        $this->dbeContact->deleteRow();
    }

    function createCustomerFolder($customerID)
    {

        $dir = $this->getCustomerFolderPath($customerID);

        /* check to see if the folder exists */
        if (!is_dir($dir)) {

            mkdir($dir);
            /*
            Then sub/folders
            */
            $subfolders =
                array(
                    'Client Information Forms',
                    'CNC Internet',
                    'Current Documentation',
                    'E-Support Packs',
                    'PC Build Sheets',
                    'Projects',
                    'Review Meetings',
                    'Software Licencing',
                    'Vulnerability Scans'
                );

            foreach ($subfolders as $folder) {
                mkdir($dir . '/' . $folder);
            }
            /*
            Then these under Current Documentation
            */
            $subfolders =
                array(
                    'Documents and Forms',
                    'Old Documentation',
                    'Photos'
                );

            foreach ($subfolders as $folder) {
                mkdir($dir . '/Current Documentation/' . $folder);
            }

            mkdir($dir . '/Current Documentation/Documents and Forms/Starters & Leavers');

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

//	  if ( strpos( 'MSIE', $_SERVER['HTTP_USER_AGENT'] ) ) {
//      $customerDir = '/' . CUSTOMER_DIR;
//    }
//    else{
        $customerDir = CUSTOMER_DIR;
//    }
        return $customerDir . '/' . $this->dbeCustomer->getValue('Name');

    }

    function getCustomerFolderPathFromBrowser($customerID)
    {

        $this->dbeCustomer->getRow($customerID);

        return CUSTOMER_DIR_FROM_BROWSER . '/' . $this->dbeCustomer->getValue('Name');

    }

    /**
     * Get next prospect to be reviewed
     */
    function getNextReviewProspect(&$dsResults)
    {
        $this->dbeCustomer->getReviewProspectRow();
        $this->getData($this->dbeCustomer, $dsResults);
        $gotRow = $dsResults->fetchNext();

        return $gotRow;
    }

    /**
     * Count customers to be reviewed
     *
     * Based on the review date
     *
     */
    function getReviewCount()
    {
        return $this->dbeCustomer->countReviewRows();
    }

    function get24HourSupportCustomers(&$dsResults)
    {
        $this->dbeCustomer->get24HourSupportCustomers();
        return $this->getData($this->dbeCustomer, $dsResults);
    }

    function hasDefaultInvoiceContactsAtAllSites($customerID)
    {
        $db = new dbSweetcode (); // database connection for query

        $sql =
            "SELECT COUNT(*) AS recCount
			FROM customer
				JOIN address ON cus_custno = add_custno AND cus_inv_siteno = add_siteno
			WHERE
				add_inv_contno = 0
				AND cus_prospect = 'N'
				AND cus_mailshot = 'Y'
				AND cus_custno = " . $customerID;

        $db->query($sql);
        $db->next_record();

        return $db->Record ['recCount'];

    }

    function getSpecialAttentionCustomers(&$dsResults)
    {
        $this->dbeCustomer->getSpecialAttentionCustomers();
        return $this->getData($this->dbeCustomer, $dsResults);
    }

    function uploadPortalDocument($customerID, $description, $userfile, $startersFormFlag, $leaversFormFlag, $mainContactOnlyFlag)
    {

        return $this->addDocument(
            $customerID,
            $userfile ['tmp_name'],
            $userfile ['size'],
            $description,
            $userfile ['name'],
            $userfile ['type'],
            $startersFormFlag,
            $leaversFormFlag,
            $mainContactOnlyFlag
        );
    }

    function addDocument(
        $customerID,
        $filePath,
        $fileSizeBytes,
        $description,
        $fileName,
        $mimeType,
        $startersFormFlag,
        $leaversFormFlag,
        $mainContactOnlyFlag
    )
    {
        $dbePortalCustomerDocument = new DBEPortalCustomerDocument ($this);
        $dbePortalCustomerDocument->setPKValue('');
        $dbePortalCustomerDocument->setValue('problemID', $problemID);
        $dbePortalCustomerDocument->setValue('file', fread(fopen($filePath, 'rb'), $fileSizeBytes));
        $dbePortalCustomerDocument->setValue('description', ( string )$description);
        $dbePortalCustomerDocument->setValue('filename', ( string )$fileName);
        $dbePortalCustomerDocument->setValue('fileLength', ( int )$fileSizeBytes);
        $dbePortalCustomerDocument->setValue('createdUserID', ( string )$GLOBALS ['auth']->is_authenticated());
        $dbePortalCustomerDocument->setValue('createdDate', date(CONFIG_MYSQL_DATETIME));
        $dbePortalCustomerDocument->setValue('fileMIMEType', ( string )$mimeType);
        $dbePortalCustomerDocument->setValue('startersFormFlag', $startersFormFlag);
        $dbePortalCustomerDocument->setValue('leaversFormFlag', $leaversFormFlag);
        $dbePortalCustomerDocument->setValue('mainContactOnlyFlag', $mainContactOnlyFlag);

        return ($dbePortalCustomerDocument->insertRow());
    }

}// End of class
?>