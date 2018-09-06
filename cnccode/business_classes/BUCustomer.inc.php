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
require_once($cfg["path_dbe"] . "/DBECustomerLeadStatus.php");
require_once($cfg["path_dbe"] . "/DBELeadStatus.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once ($cfg['path_dbe'].'/DBEJContract.inc.php');
define('BUCUSTOMER_NAME_STR_NT_PASD', 'No name string passed');

class BUCustomer extends Business
{
    var $dbeCustomer = "";
    var $dbeSite = "";
    var $dbeContact = "";
    /**
     * @var DBECustomerType
     */
    var $dbeCustomerType;

    /**
     * @var DBECustomerLeadStatus
     */
    protected $dbeCustomerLeadStatuses;
    var $buHeader = '';
    /** @var DBEHeader */
    public $dsHeader;

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
        $this->dbeCustomerLeadStatuses = new DBECustomerLeadStatus($this);
        $this->buHeader = new BUHeader($this);
        $this->buHeader->getHeader($this->dsHeader);
        $this->dsHeader->fetchNext();
    }

    /**
     * Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @param String $nameSearchString String to match against or numeric customerID
     * @param DataSet &$dsResults results
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
     * @param integer $customerID
     * @param DataSet &$dsResults results
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
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getSitesByCustomerID($customerID, &$dsResults, $showInactiveSites)
    {
        $this->setMethodName('getSitesByCustomerID');
        if ($customerID == '') {
            $this->raiseError('CustomerID not passed');
        }
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
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
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param $dsContact
     * @return bool : Success
     * @access public
     */
    function getInvoiceSiteByCustomerID($customerID, &$dsResults, &$dsContact)
    {
        $this->setMethodName('getInvoiceSiteByCustomerID');
        if ($customerID == '') {
            $this->raiseError('CustomerID not passed');
        }
        /** @var DataSet $dsCustomer */
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
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param $dsContact
     * @return void : Success
     * @access public
     */
    function getDeliverSiteByCustomerID($customerID, &$dsResults, &$dsContact)
    {
        $this->setMethodName('getDeliverySiteByCustomerID');
        if ($customerID == '') {
            $this->raiseError('CustomerID not passed');
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
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param bool $includeInactive
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerID($customerID, &$dsResults, $includeInactive = false)
    {
        $this->setMethodName('getContactsByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->getRowsByCustomerID($customerID, $includeInactive);
        return ($this->getData($this->dbeContact, $dsResults));
    }

    /**
     * Get contact rows by customerID
     * @param integer $customerID
     * @param DataSet &$dsResults results
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
     * @param DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getCustomerTypes(&$dsResults)
    {
        $this->setMethodName('getCustomerTypes');
        $this->dbeCustomerType->getRows(DBECustomerType::description);
        return ($this->getData($this->dbeCustomerType, $dsResults));
    }

    /**
     * @param DataSet $dsResults
     * @return bool
     */
    function getCustomerLeadStatuses(&$dsResults)
    {
        $this->dbeCustomerLeadStatuses->getRows('name');
        return ($this->getData($this->dbeCustomerLeadStatuses, $dsResults));
    }

    /**
     * @param null $leadStatusID
     * @return DBEContact
     */
    function getMainContactsByLeadStatus($leadStatusID = null)
    {
        return $this->dbeContact->getMainContactsByLeadStatus($leadStatusID);
    }

    /**
     * Get all lead status rows
     * @param DataSet &$dsResults results
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
     * @param DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function updateCustomer(&$dsData)
    {
        $this->setMethodName('updateCustomer');
        if ($dsData->getValue(DBECustomer::name) == '') {
            $this->raiseError('Customer Name is empty!');
            exit;
        }
        $dsData->setValue(DBECustomer::modifyDate, date('Y-m-d H:i:s'));
        $dsData->setValue(DBECustomer::modifyUserID, $GLOBALS ['auth']->is_authenticated());

        $this->dbeCustomer->setCallbackMethod(DA_BEFORE_POST, $this, 'beforeUpdateCustomer');

        return ($this->updateDataaccessObject($dsData, $this->dbeCustomer));
    }

    /**
     * @param DataSet $newRow
     */
    function beforeUpdateCustomer(&$newRow)
    {
        $customerID = $newRow->getPkValue();
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        if ($dbeCustomer->getValue(DBECustomer::lastReviewMeetingDate) != $newRow->getValue(DBECustomer::lastReviewMeetingDate)) {
            $newRow->setValue(DBECustomer::reviewMeetingEmailSentFlag, 'N');
        }
    }

    function updateModify($customerID)
    {
        if (!$customerID) {
            $this->raiseError('customerID not set');
        }
        $this->setMethodName('updateModify');
        $this->dbeCustomer->getRow($customerID);
        if ($this->dbeCustomer->getValue(DBECustomer::name) == '') {
            $this->raiseError('Customer Name is empty for customer ' . $customerID);
            exit;
        }
        $this->dbeCustomer->setValue(DBECustomer::modifyDate, date('Y-m-d H:i:s'));
        $this->dbeCustomer->setValue(DBECustomer::modifyUserID, $GLOBALS ['auth']->is_authenticated());
        $this->dbeCustomer->updateRow();
    }

    /**
     * Insert customer
     * This also creates site and contact row to be completed. We pass dsSite and dsContact by ref for use afterwards
     * to avoid having to query the database from CTCustomer
     * @param DataSet &$dsData dataset to apply
     * @param DataSet $dsSite
     * @param DataSet $dsContact
     * @return bool : Success
     * @access public
     */
    function insertCustomer(&$dsData, &$dsSite, &$dsContact)
    {
        $this->setMethodName('insertCustomer');
        $ret = ($this->updateCustomer($dsData));
        $this->addNewSiteRow($dsSite,
                             $dsData->getValue(DBECustomer::customerID));                        // New customerID
        $dsSite->initialise();
        $this->dbeSite->setCallbackMethod(DA_BEFORE_POST, $this, 'setSageRef');
        $ret = $ret & ($this->updateSite($dsSite));
        $this->dbeSite->resetCallbackMethod(DA_BEFORE_POST);
        $this->addNewContactRow($dsContact,
                                $dsData->getValue(DBECustomer::customerID),
                                '0'); // First siteno always zero
        $ret = $ret & ($this->updateContact($dsContact));
        $dsSite->setUpdateModeUpdate();
        $dsSite->setValue(DBESite::deliverContactID, $dsContact->getValue(DBEContact::contactID));
        $dsSite->setValue(DBESite::invoiceContactID, $dsContact->getValue(DBEContact::contactID));
        $dsSite->post();
        $ret = $ret & ($this->updateSite($dsSite));        // Then update site delivery and invoice contacts
        return $ret;
    }

    /**
     * Update site
     * @param DataSet $dsData
     * @return bool : Success
     * @access public
     */
    function updateSite(&$dsData)
    {
        $this->setMethodName('updateSite');

        $this->dbeSite->setCallbackMethod(DA_AFTER_COLUMNS_CREATED, $this, 'setCustomerID');
        $ret = ($this->updateDataaccessObject($dsData, $this->dbeSite));

        $this->dbeSite->resetCallbackMethod(DA_AFTER_COLUMNS_CREATED);
        $this->updateModify($dsData->getValue(DBESite::customerID));
        return $ret;
    }

    /**
     * by default, replicate() function only sets the siteNo (PK column) before setUPdateModeUpdate
     * so we jump in to set the customerID as well because DBESite has a composite PK
     * @param DataSet &$source dataset Not used
     * @param DBESite &$dbeSite site database entity
     * @return bool : Success
     * @access public
     */
    function setCustomerID(&$source, &$dbeSite)
    {
        $dbeSite->setValue(DBESite::customerID, $source->getValue(DBECustomer::customerID));
        return TRUE;
    }

    /**
     * Calculate a unique Sage Reference for new customer site
     * Based upon uppercase first two non-space characters of name plus integer starting at 1 (e.g. KA002)
     * @param DataSet &$source dataset
     * @param DBESite &$dbeSite site database entity
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
     * @param DataSet &$dsData dataset to apply
     * @return bool : Success
     * @access public
     */
    function updateContact(&$dsData)
    {
        $this->setMethodName('updateContact');
        $ret = $this->updateDataaccessObject($dsData, $this->dbeContact);
        $this->updateModify($dsData->getValue(DBEContact::customerID));
        return $ret;

    }

    /**
     * @param DataSet $dsContact
     * @param $customerID
     * @param $siteNo
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
        $dsContact->setValue(
            DBEContact::contactID,
            0
        );
        $dsContact->setValue(
            DBEContact::customerID,
            $customerID
        );
        $dsContact->setValue(
            DBEContact::firstName,
            'First Name'
        );
        $dsContact->setValue(
            DBEContact::lastName,
            'Last Name'
        );
        $dsContact->setValue(
            DBEContact::siteNo,
            $siteNo
        );
        $dsContact->setValue(
            DBEContact::discontinuedFlag,
            'N'
        );
        $dsContact->setValue(
            DBEContact::sendMailshotFlag,
            'Y'
        );
        $dsContact->setValue(
            DBEContact::accountsFlag,
            'N'
        );
        $dsContact->setValue(
            DBEContact::mailshot2Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot2FlagDef)
        );
        $dsContact->setValue(
            DBEContact::mailshot3Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot3FlagDef)
        );
        $dsContact->setValue(
            DBEContact::mailshot4Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot4FlagDef)
        );
        $dsContact->setValue(
            DBEContact::mailshot8Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot8FlagDef)
        );
        $dsContact->setValue(
            DBEContact::mailshot9Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot9FlagDef)
        );
        $dsContact->setValue(
            DBEContact::mailshot11Flag,
            $this->dsHeader->getValue(DBEHeader::mailshot11FlagDef)
        );
        $dsContact->post();
        $this->updateModify($dsContact->getValue(DBEContact::customerID));
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
            $dsSite->setValue(DBESite::activeFlag, 'Y');
            $dsSite->setValue(DBESite::siteNo, -9);
            $dsSite->setValue(DBESite::add1, 'Address Line 1');
            $dsSite->setValue(DBESite::town, 'TOWN');
            $dsSite->setValue(DBESite::maxTravelHours, -1);    // means not set because 0 is now a valid distance
            $dsSite->setValue(DBESite::postcode, 'POSTCODE');
            $dsSite->post();
//			$this->updateModify($dsSite->getValue(DBESite::CustomerID));
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
        $dsCustomer->setValue(DBECustomer::othersEmailMainFlag, 'Y');
        $dsCustomer->setValue(DBECustomer::workStartedEmailMainFlag, 'Y');
        $dsCustomer->setValue(DBECustomer::autoCloseEmailMainFlag, 'Y');
        $dsCustomer->setValue(DBECustomer::createDate, date('Y-m-d'));
        $dsCustomer->setValue(DBECustomer::invoiceSiteNo, 0);
        $dsCustomer->setValue(DBECustomer::deliverSiteNo, 0);
        $dsCustomer->setValue(DBECustomer::customerTypeID, 0);

        $dsCustomer->setValue(DBECustomer::pcxFlag, 'N');          // 2nd site
        $dsCustomer->setValue(DBECustomer::specialAttentionFlag, 'N');
        $dsCustomer->setValue(DBECustomer::support24HourFlag, 'N');

        $dsCustomer->setValue(DBECustomer::modifyDate, date('Y-m-d H:i:s'));
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
        $this->dbeContact->getRowsByCustomerIDSiteNo($customerID, $siteNo, $supportContacts);
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
        $this->dbeContact->getSupportRowsByCustomerIDSiteNo($customerID, $siteNo);
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
        $this->dbeCustomer->setValue(DBECustomer::modifyDate, date('Y-m-d H:i:s'));
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
        $dbeOrdhead->setValue(DBEOrdhead::customerID, $customerID);
        if ($dbeOrdhead->countRowsByColumn(DBEOrdhead::customerID) > 0) {
            return FALSE;
        }
        // calls
        require_once($cfg['path_dbe'] . '/DBEProblem.inc.php');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(DBEProblem::customerID, $customerID);
        if ($dbeProblem->countRowsByColumn(DBEProblem::customerID) > 0) {
            return FALSE;
        }
        // customer items
        require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->setValue(DBECustomerItem::customerID, $customerID);
        if ($dbeCustomerItem->countRowsByColumn(DBECustomerItem::customerID) > 0) {
            return FALSE;
        }
        // invoices
        require_once($cfg['path_dbe'] . '/DBEInvhead.inc.php');
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setValue(DBEInvhead::customerID, $customerID);
        if ($dbeInvhead->countRowsByColumn(DBEInvhead::customerID) > 0) {
            return FALSE;
        }
        // customer notes
        require_once($cfg['path_dbe'] . '/DBECustomerNote.inc.php');
        $dbeCustomerNote = new DBECustomerNote($this);
        $dbeCustomerNote->setValue(DBECustomerNote::customerID, $customerID);
        if ($dbeCustomerNote->countRowsByColumn(DBECustomerNote::customerID) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete customers, sites and contacts
     */
    function deleteCustomer($customerID)
    {
        $this->dbeContact->setValue(DBEContact::customerID, $customerID);
        $this->dbeContact->deleteRowsByCustomerID();
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
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
     * @param $customerID
     * @param $siteNo
     * @return bool
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
    /**
     *    Delete sites and contacts
     * @param $customerID
     * @param $siteNo
     */
    function deleteSite($customerID, $siteNo)
    {
        $this->dbeContact->setValue(DBEContact::customerID, $customerID);
        $this->dbeContact->setValue(DBEContact::siteNo, $siteNo);
        $this->dbeContact->deleteRowsByCustomerIDSiteNo();
        $this->dbeSite->setValue(DBESite::customerID, $customerID);
        $this->dbeSite->setValue(DBESite::siteNo, $siteNo);
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
     * @param $contactID
     * @return bool
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
        $dbeInvhead->setValue(DBEInvhead::contactID, $contactID);
        if ($dbeInvhead->countRowsByColumn(DBEInvhead::contactID) > 0) {
            return FALSE;
        }
        // calls
        require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(DBECallActivity::contactID, $contactID);
        if ($dbeCallActivity->countRowsByColumn(DBECallActivity::contactID) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete contact
     */
    function deleteContact($contactID)
    {
        $this->dbeContact->setValue(DBEContact::contactID, $contactID);
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
        return $customerDir . '/' . $this->dbeCustomer->getValue(DBECustomer::name);

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

    /**
     * Get next prospect to be reviewed
     * @param DataSet $dsResults
     * @return bool
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

    function uploadPortalDocument($customerID,
                                  $description,
                                  $userfile,
                                  $startersFormFlag,
                                  $leaversFormFlag,
                                  $mainContactOnlyFlag)
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
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::file,
                                             fread(fopen($filePath, 'rb'), $fileSizeBytes));
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::description, ( string )$description);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::filename, ( string )$fileName);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::createdUserID,
                                             ( string )$GLOBALS ['auth']->is_authenticated());
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::createdDate, date(CONFIG_MYSQL_DATETIME));
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::fileMimeType, ( string )$mimeType);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::startersFormFlag, $startersFormFlag);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::leaversFormFlag, $leaversFormFlag);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::mainContactOnlyFlag, $mainContactOnlyFlag);

        return ($dbePortalCustomerDocument->insertRow());
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function hasPrepayContract($customerId)
    {
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getPrePayContracts($customerId);

        $array = $dbeJContract->getRowAsArray();

        return !!count($array);
    }

}// End of class
?>