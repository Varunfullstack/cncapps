<?php /**
 * Customer business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBESite.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerType.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerLeadStatus.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContract.inc.php');
require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
require_once($cfg['path_dbe'] . '/DBEProblem.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEInvhead.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerNote.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
define(
    'BUCUSTOMER_NAME_STR_NT_PASD',
    'No name string passed'
);

class BUCustomer extends Business
{
    /** @var DBECustomer */
    public $dbeCustomer;
    /** @var DBESite */
    public $dbeSite;
    /** @var DBEContact */
    public $dbeContact;
    /** @var DBECustomerType */
    public $dbeCustomerType;
    /** @var BUHeader */
    public $buHeader;
    /** @var DBEHeader */
    public $dsHeader;
    /** @var DBECustomerLeadStatus */
    protected $dbeCustomerLeadStatuses;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeCustomer             = new DBECustomer($this);
        $this->dbeSite                 = new DBESite($this);
        $this->dbeContact              = new DBEContact($this);
        $this->dbeCustomerType         = new DBECustomerType($this);
        $this->dbeCustomerLeadStatuses = new DBECustomerLeadStatus($this);
        $this->buHeader                = new BUHeader($this);
        $this->buHeader->getHeader($this->dsHeader);
        $this->dsHeader->fetchNext();
    }

    /**
     * Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @param DataSet &$dsResults results
     * @param string $contactString
     * @param string $phoneString
     * @param string $nameMatchString
     * @param string $town
     * @param string $newCustomerFromDate
     * @param string $newCustomerToDate
     * @param string $droppedCustomerFromDate
     * @param string $droppedCustomerToDate
     * @return bool : One or more rows
     * @access public
     */
    function getCustomersByNameMatch(&$dsResults,
                                     $contactString = null,
                                     $phoneString = null,
                                     $nameMatchString = null,
                                     $town = null,
                                     $newCustomerFromDate = null,
                                     $newCustomerToDate = null,
                                     $droppedCustomerFromDate = null,
                                     $droppedCustomerToDate = null
    )
    {
        $this->setMethodName('getCustomersByNameMatch');
        $nameMatchString = trim($nameMatchString);
        if (is_numeric($nameMatchString)) {
            $ret = ($this->getCustomerByID(
                $nameMatchString,
                $dsResults
            ));
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
            $ret = ($this->getData(
                $this->dbeCustomer,
                $dsResults
            ));
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
    function getCustomerByID($customerID,
                             &$dsResults
    )
    {
        $this->setMethodName('getCustomerByID');
        return ($this->getDatasetByPK(
            $customerID,
            $this->dbeCustomer,
            $dsResults
        ));
    }

    /**
     * Get site rows by customerID
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param $showInactiveSites
     * @return bool : Success
     * @access public
     */
    function getSitesByCustomerID($customerID,
                                  &$dsResults,
                                  $showInactiveSites
    )
    {
        $this->setMethodName('getSitesByCustomerID');
        if (!$customerID) {
            $this->raiseError('CustomerID not passed');
        }
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $this->dbeSite->getRowsByCustomerID(!$showInactiveSites);
        return ($this->getData(
            $this->dbeSite,
            $dsResults
        ));
    }

    /**
     * Get invoice site by customerID
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param $dsContact
     * @return bool : Success
     * @access public
     */
    function getInvoiceSiteByCustomerID($customerID,
                                        &$dsResults,
                                        &$dsContact
    )
    {
        $this->setMethodName('getInvoiceSiteByCustomerID');
        if (!$customerID) {
            $this->raiseError('CustomerID not passed');
        }
        /** @var DataSet $dsCustomer */
        $this->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $this->dbeSite->setValue(
            DBESite::siteNo,
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo)
        );
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData(
            $this->dbeSite,
            $dsResults
        );
        $this->getContactByID(
            $dsResults->getValue(DBESite::invoiceContactID),
            $dsContact
        );
        return TRUE;
    }

    /**
     * Get contact by id
     * @param $contactID
     * @param DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getContactByID($contactID,
                            &$dsResults
    )
    {
        $this->setMethodName('getContactByID');
        if (!$contactID) {
            $this->raiseError('contactID not passed');
        }
        return ($this->getDatasetByPK(
            $contactID,
            $this->dbeContact,
            $dsResults
        ));
    }

    /**
     * Get invoice site by customerID, siteNo
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param $dsContact
     * @return void : Success
     * @access public
     */
    function getDeliverSiteByCustomerID($customerID,
                                        &$dsResults,
                                        &$dsContact
    )
    {
        $this->setMethodName('getDeliverySiteByCustomerID');
        if (!$customerID) {
            $this->raiseError('CustomerID not passed');
        }
        /** @var DataSet $dsCustomer */
        $this->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $this->dbeSite->setValue(
            DBESite::siteNo,
            $dsCustomer->getValue(DBECustomer::deliverSiteNo)
        );
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData(
            $this->dbeSite,
            $dsResults
        );
        $this->getContactByID(
            $dsResults->getValue(DBESite::deliverContactID),
            $dsContact
        );
    }

    function duplicatedEmail($email,
                             $contactID = null,
                             $customerID = null
    )
    {
        if ($email === '') {
            return true;
        }
        $query      = "select count(con_contno) as count from contact where con_email = ? and active = 1 ";
        $paramTypes = 's';
        $params     = [
            $email,
        ];
        if ($contactID) {
            $query      .= " and con_contno <> ? ";
            $paramTypes .= "i";
            $params[]   = +$contactID;
        }
        if ($customerID) {
            $query      .= " and con_custno <> ?";
            $paramTypes .= "i";
            $params[]   = +$customerID;
        }
        $params   = array_merge(
            [$paramTypes],
            $params
        );
        $refArray = [];
        foreach ($params as $key => $value) $refArray[$key] = &$params[$key];
        $statement = $this->db->prepare($query);
        call_user_func_array(
            [$statement, 'bind_param'],
            $refArray
        );
        $result = $statement->execute() ? $statement->get_result() : false;
        $statement->close();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Get all customer types
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getCustomerTypes(&$dsResults)
    {
        $this->setMethodName('getCustomerTypes');
        $this->dbeCustomerType->getRows(DBECustomerType::description);
        return ($this->getData(
            $this->dbeCustomerType,
            $dsResults
        ));
    }

    /**
     * @param DataSet $dsResults
     * @return bool
     */
    function getCustomerLeadStatuses(&$dsResults)
    {
        $this->dbeCustomerLeadStatuses->getRows('name');
        return ($this->getData(
            $this->dbeCustomerLeadStatuses,
            $dsResults
        ));
    }

    /**
     * @param null $leadStatusID
     * @return DBEContact
     */
    function getContactsByLeadStatus($leadStatusID = null)
    {
        return $this->dbeContact->getContactsByLeadStatus($leadStatusID);
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
        $dbeLeadStatus = new DBECustomerLeadStatus($this);
        $dbeLeadStatus->getRows();
        return ($this->getData(
            $dbeLeadStatus,
            $dsResults
        ));
    }

    /**
     * @param DataSet $newRow
     */
    function beforeUpdateCustomer($newRow)
    {
        $customerID      = $newRow->getPkValue();
        $currentCustomer = new DBECustomer($this);
        $currentCustomer->getRow($customerID);
        $newRow->setValue(
            DBECustomer::modifyDate,
            date('d/m/Y H:i:s')
        );
        $newRow->setValue(
            DBECustomer::modifyUserID,
            $GLOBALS ['auth']->is_authenticated()
        );
        if ($currentCustomer->getValue(DBECustomer::lastReviewMeetingDate) != $newRow->getValue(
                DBECustomer::lastReviewMeetingDate
            )) {
            $newRow->setValue(
                DBECustomer::reviewMeetingEmailSentFlag,
                'N'
            );
        }
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
    function insertCustomer(&$dsData,
                            &$dsSite,
                            &$dsContact
    )
    {
        $this->setMethodName('insertCustomer');
        $ret = ($this->updateCustomer($dsData));
        $this->addNewSiteRow(
            $dsSite,
            $dsData->getValue(DBECustomer::customerID)
        );                        // New customerID
        $dsSite->initialise();
        $this->dbeSite->setCallbackMethod(
            DA_BEFORE_POST,
            $this,
            'setSageRef'
        );
        $ret = $ret && ($this->updateSite($dsSite));
        $this->dbeSite->resetCallbackMethod(DA_BEFORE_POST);
        $this->addNewContactRow(
            $dsContact,
            $dsData->getValue(DBECustomer::customerID),
            '0'
        ); // First siteno always zero
        $ret = $ret & ($this->updateContact($dsContact));
        $dsSite->setUpdateModeUpdate();
        $dsSite->setValue(
            DBESite::deliverContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dsSite->setValue(
            DBESite::invoiceContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dsSite->post();
        $ret = $ret & ($this->updateSite($dsSite));        // Then update site delivery and invoice contacts
        return $ret;
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
        if (!$dsData->getValue(DBECustomer::name)) {
            $this->raiseError('Customer Name is empty!');
            exit;
        }
        $this->dbeCustomer->setCallbackMethod(
            DA_BEFORE_POST,
            $this,
            'beforeUpdateCustomer'
        );
        return ($this->updateDataAccessObject(
            $dsData,
            $this->dbeCustomer
        ));
    }

    /**
     * @param DataSet $dsSite
     * @param $customerID
     * @return bool
     */
    function addNewSiteRow(&$dsSite,
                           $customerID
    )
    {
        if (!$customerID) {
            $this->raiseError('customerID not passed');
            return FALSE;
        } else {
            $dsSite->clearCurrentRow();
            $dsSite->setUpdateModeInsert();
            $dsSite->setValue(
                DBESite::customerID,
                $customerID
            );
            $dsSite->setValue(
                DBESite::activeFlag,
                'Y'
            );
            $dsSite->setValue(
                DBESite::siteNo,
                -9
            );
            $dsSite->setValue(
                DBESite::add1,
                'Address Line 1'
            );
            $dsSite->setValue(
                DBESite::town,
                'TOWN'
            );
            $dsSite->setValue(
                DBESite::maxTravelHours,
                -1
            );    // means not set because 0 is now a valid distance
            $dsSite->setValue(
                DBESite::postcode,
                'POSTCODE'
            );
            $dsSite->post();
//			$this->updateModify($dsSite->getValue(DBESite::CustomerID));
            return TRUE;
        }
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
        $this->dbeSite->setCallbackMethod(
            DA_AFTER_COLUMNS_CREATED,
            $this,
            'setCustomerID'
        );
        $ret = ($this->updateDataAccessObject(
            $dsData,
            $this->dbeSite
        ));
        $this->dbeSite->resetCallbackMethod(DA_AFTER_COLUMNS_CREATED);
        return $ret;
    }

    /**
     * @param DataSet $dsContact
     * @param $customerID
     * @param $siteNo
     * @return bool
     */
    function addNewContactRow(&$dsContact,
                              $customerID,
                              $siteNo
    )
    {
        $this->setMethodName('addNewContactRow');
        if (!$customerID) {
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
        $dsContact->setValue(
            DBEContact::initialLoggingEmailFlag,
            'Y'
        );
        $dsContact->setValue(
            DBEContact::fixedEmailFlag,
            'Y'
        );
        $dsContact->setValue(
            DBEContact::othersInitialLoggingEmailFlag,
            'Y'
        );
        $dsContact->setValue(
            DBEContact::othersWorkUpdatesEmailFlag,
            'Y'
        );
        $dsContact->setValue(
            DBEContact::othersFixedEmailFlag,
            'Y'
        );
        $dsContact->post();
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
        $ret = $this->updateDataAccessObject(
            $dsData,
            $this->dbeContact
        );
        return $ret;

    }

    /**
     * by default, replicate() function only sets the siteNo (PK column) before setUpdateModeUpdate
     * so we jump in to set the customerID as well because DBESite has a composite PK
     * @param DataSet &$source dataset Not used
     * @param DBESite &$dbeSite site database entity
     * @return bool : Success
     * @access public
     */
    function setCustomerID(&$source,
                           &$dbeSite
    )
    {
        $dbeSite->setValue(
            DBESite::customerID,
            $source->getValue(DBECustomer::customerID)
        );
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
    function setSageRef(&$source,
                        &$dbeSite
    )
    {
        $customerName = $this->dbeCustomer->getValue(DBECustomer::name);
        $shortCode    = "";
        for ($ixChar = 0; $ixChar <= strlen($customerName); $ixChar++) {
            if (substr(
                    $customerName,
                    $ixChar,
                    1
                ) != " ") {
                $shortCode = $shortCode . strtoupper(
                        substr(
                            $customerName,
                            $ixChar,
                            1
                        )
                    );
                if (strlen($shortCode) == 2) {
                    break;
                }
            }
        }
        $number       = 1;
        $numberUnique = FALSE;
        $dbeSite      = new DBESite($this);
        $sageRef      = null;
        while (!$numberUnique) {
            $sageRef      = $shortCode . str_pad(
                    $number,
                    3,
                    "0",
                    STR_PAD_LEFT
                );
            $numberUnique = $dbeSite->uniqueSageRef($sageRef);
            $number++;
        }
        $source->setValue(
            DBESite::sageRef,
            $sageRef
        );
        return TRUE;
    }

    /**
     * @param DataSet $dsCustomer
     */
    function addNewCustomerRow(&$dsCustomer)
    {
        $dsCustomer->clearCurrentRow();
        $dsCustomer->setUpdateModeInsert();
        $dsCustomer->setValue(
            DBECustomer::customerID,
            null
        );
        $dsCustomer->setValue(
            DBECustomer::name,
            'New Customer'
        );
        $dsCustomer->setValue(
            DBECustomer::mailshotFlag,
            'Y'
        );
        $dsCustomer->setValue(
            DBECustomer::referredFlag,
            'Y'
        );
        $dsCustomer->setValue(
            DBECustomer::createDate,
            date('Y-m-d')
        );
        $dsCustomer->setValue(
            DBECustomer::invoiceSiteNo,
            0
        );
        $dsCustomer->setValue(
            DBECustomer::deliverSiteNo,
            0
        );
        $dsCustomer->setValue(
            DBECustomer::customerTypeID,
            0
        );
        $dsCustomer->setValue(
            DBECustomer::pcxFlag,
            'N'
        );
        $dsCustomer->setValue(
            DBECustomer::specialAttentionFlag,
            'N'
        );
        $dsCustomer->setValue(
            DBECustomer::support24HourFlag,
            'N'
        );
        $dsCustomer->setValue(
            DBECustomer::modifyDate,
            date('Y-m-d H:i:s')
        );
        $dsCustomer->post();
    }

    /**
     * Get contact rows by customerID
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @param $customerID
     * @param $siteNo
     * @param $dsResults
     * @param bool $supportContacts
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerIDSiteNo($customerID,
                                           $siteNo,
                                           &$dsResults,
                                           $supportContacts = false
    )
    {
        $this->setMethodName('getContactsByCustomerIDSiteNo');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        if ($siteNo == '') {
            $this->raiseError('siteNo not passed');
        }
        $this->dbeContact->getRowsByCustomerIDSiteNo(
            $customerID,
            $siteNo,
            $supportContacts
        );
        return ($this->getData(
            $this->dbeContact,
            $dsResults
        ));
    }

    /**
     * Is contact a nominated support contact
     * @parameter integer $contactID
     * @param $contactID
     * @return bool : True = support contact
     * @access public
     */
    function isASupportContact($contactID)
    {
        $this->setMethodName('isASupportContact');
        if (!$contactID) {
            $this->raiseError('contactID not passed');
        }
        $this->dbeContact->getRow($contactID);
        return !empty($this->dbeContact->getValue(DBEContact::supportLevel));
    }

    function ensureBecameCustomer($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        if (!$this->dbeCustomer->getValue(DBECustomer::becameCustomerDate)) {
            $this->dbeCustomer->setValue(DBECustomer::becameCustomerDate, (new DateTime())->format(DATE_MYSQL_DATE));
        }
        $this->dbeCustomer->setValue(
            DBECustomer::modifyDate,
            date('Y-m-d H:i:s')
        );
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
        $this->getContactByID(
            $contactID,
            $dsContact
        );
//		$dsContact->fetchNext();
        /** @var DataSet $dsSite */
        $this->getSiteByCustomerIDSiteNo(
            $dsContact->getValue(DBEContact::customerID),
            $dsContact->getValue(DBEContact::siteNo),
            $dsSite
        );
        $dsContact->fetchNext();
        $contactPhone = null;
        if ($dsSite->getValue(DBESite::phone)) {
            $contactPhone = $dsSite->getValue(DBESite::phone);
        }
        if ($dsContact->getValue(DBEContact::phone)) {
            $contactPhone .= ' DDI: ' . $dsContact->getValue(DBEContact::phone);
        }
        if ($dsContact->getValue(DBEContact::mobilePhone)) {
            $contactPhone .= ' Mobile: ' . $dsContact->getValue(DBEContact::mobilePhone);
        }
        return $contactPhone;
    }

    /**
     * Get site by customerID and SiteNo
     * @param integer $customerID
     * @param integer $siteNo
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function getSiteByCustomerIDSiteNo($customerID,
                                       $siteNo,
                                       &$dsResults
    )
    {
        $this->setMethodName('getSiteByCustomerIDSiteNo');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $this->dbeSite->setValue(
            DBESite::siteNo,
            $siteNo
        );
        $this->dbeSite->getRowByCustomerIDSiteNo();
        $this->getData(
            $this->dbeSite,
            $dsResults
        );
        return TRUE;
    }

    /**
     * This version includes tel: tags for soft phone dialing from browser
     * @param $contactID
     * @param null $emailSubject
     * @return string
     */
    function getContactPhoneForHtml($contactID, $emailSubject = null)
    {
        /** @var DataSet $dsContact */
        $this->getContactByID(
            $contactID,
            $dsContact
        );
        /** @var DataSet $dsSite */
        $this->getSiteByCustomerIDSiteNo(
            $dsContact->getValue(DBEContact::customerID),
            $dsContact->getValue(DBEContact::siteNo),
            $dsSite
        );
        $dsContact->fetchNext();
        $contactPhone = null;
        if ($dsSite->getValue(DBESite::phone)) {
            $contactPhone .= '<a href="tel:' . str_replace(
                    ' ',
                    '',
                    $dsSite->getValue(DBESite::phone)
                ) . '">' . $dsSite->getValue(DBESite::phone) . '</a>';
        }
        if ($dsContact->getValue(DBEContact::phone)) {
            $contactPhone .= ' DDI: <a href="tel:' . str_replace(
                    ' ',
                    '',
                    $dsContact->getValue(DBEContact::phone)
                ) . '">' . $dsContact->getValue(DBEContact::phone) . '</a>';
        }
        if ($dsContact->getValue(DBEContact::mobilePhone)) {
            $contactPhone .= ' Mobile: <a href="tel:' . str_replace(
                    ' ',
                    '',
                    $dsContact->getValue(DBEContact::mobilePhone)
                ) . '">' . $dsContact->getValue(DBEContact::mobilePhone) . '</a>';
        }
        if ($dsContact->getValue(DBEContact::email)) {
            $subject = null;
            if ($emailSubject) {
                $subject = "?subject={$emailSubject}";
            }
            $contactPhone .= "&nbsp;<a href='mailto:{$dsContact->getValue(DBEContact::email)}{$subject}'><img src='images/email.gif' style='border: 0' alt='email'></a>";
        }
        return $contactPhone;
    }

    /**
     * Get all the invoice contacts
     * @parameter CustomerID CustomerID
     * @param $customerID
     * @param $dsData
     * @return bool : Success
     * @access public
     */
    function getInvoiceContactsByCustomerID($customerID,
                                            &$dsData
    )
    {
        $this->setMethodName('getInvoiceContactsByCustomerID');
        $this->dbeContact->getInvoiceContactsByCustomerID($customerID);
        $ret = $this->getData(
            $this->dbeContact,
            $dsData
        );
        return $ret;

    }

    /**
     * Get main support contact rows by customerID
     * i.e. those contacts with mailFlag10 = Y
     * @parameter integer $customerID
     * @parameter DataSet &$dsResults results
     * @param $customerID
     * @param $excludeEmail
     * @return array : Success
     * @access public
     */
    function getMainSupportEmailAddresses($customerID,
                                          $excludeEmail
    )
    {
        $this->setMethodName('getMainSupportEmailAddresses');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->getMainSupportRowsByCustomerID($customerID);
        $emailList = [];
        while ($this->dbeContact->fetchNext()) {
            $currentContactEmail = strtolower($this->dbeContact->getValue(DBEContact::email));
            if ($currentContactEmail === strtolower($excludeEmail)) {
                continue;
            }
            if (array_key_exists($currentContactEmail, $emailList)) {
                continue;
            }
            $emailList[$currentContactEmail] = $currentContactEmail;
        }
        return $emailList;
    }

    /**
     *    Check dependent tables:
     *    Calls
     *    Customer Items
     * Sales Orders
     * Invoices
     * @param $customerID
     * @param $userID
     * @return bool
     */
    function canDeleteCustomer($customerID,
                               $userID
    )
    {
        if ($userID != USER_GJ) {
            return false;
        }
        // sales orders
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setValue(
            DBEOrdhead::customerID,
            $customerID
        );
        if ($dbeOrdhead->countRowsByColumn(DBEOrdhead::customerID) > 0) {
            return FALSE;
        }
        // calls
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        if ($dbeProblem->countRowsByColumn(DBEProblem::customerID) > 0) {
            return FALSE;
        }
        // customer items
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->setValue(
            DBECustomerItem::customerID,
            $customerID
        );
        if ($dbeCustomerItem->countRowsByColumn(DBECustomerItem::customerID) > 0) {
            return FALSE;
        }
        // invoices
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setValue(
            DBEInvhead::customerID,
            $customerID
        );
        if ($dbeInvhead->countRowsByColumn(DBEInvhead::customerID) > 0) {
            return FALSE;
        }
        // customer notes
        $dbeCustomerNote = new DBECustomerNote($this);
        $dbeCustomerNote->setValue(
            DBECustomerNote::customerID,
            $customerID
        );
        if ($dbeCustomerNote->countRowsByColumn(DBECustomerNote::customerID) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete customers, sites and contacts
     * @param $customerID
     */
    function deleteCustomer($customerID)
    {
        $this->dbeContact->setValue(
            DBEContact::customerID,
            $customerID
        );
        $this->dbeContact->deleteRowsByCustomerID();
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
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
    function canDeleteSite($customerID,
                           $siteNo
    )
    {
        $dbeOrdhead = new DBEOrdhead($this);
        if ($dbeOrdhead->countRowsByCustomerSiteNo(
                $customerID,
                $siteNo
            ) > 0) {
            return FALSE;
        }
        // sales invoices
        $dbeInvhead = new DBEInvhead($this);
        if ($dbeInvhead->countRowsByCustomerSiteNo(
                $customerID,
                $siteNo
            ) > 0) {
            return FALSE;
        }
        // calls
        $dbeCallActivity = new DBECallActivity($this);
        if ($dbeCallActivity->countRowsByCustomerSiteNo(
                $customerID,
                $siteNo
            ) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     * Gets a list of main support contacts
     * @param $customerID
     * @param bool $includeSupervisors
     * @return array
     */
    function getMainSupportContacts($customerID,
                                    $includeSupervisors = false
    )
    {
        $this->setMethodName('getMainSupportContacts');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->getMainSupportRowsByCustomerID(
            $customerID,
            $includeSupervisors
        );
        $contacts = [];
        while ($this->dbeContact->fetchNext()) {
            $contacts[] = [
                DBEContact::contactID                     => $this->dbeContact->getValue(DBEContact::contactID),
                DBEContact::firstName                     => $this->dbeContact->getValue(DBEContact::firstName),
                DBEContact::lastName                      => $this->dbeContact->getValue(DBEContact::lastName),
                DBEContact::email                         => $this->dbeContact->getValue(DBEContact::email),
                DBEContact::supportLevel                  => $this->dbeContact->getValue(DBEContact::supportLevel),
                DBEContact::initialLoggingEmailFlag       => $this->dbeContact->getValue(
                    DBEContact::initialLoggingEmailFlag
                ),
                DBEContact::fixedEmailFlag                => $this->dbeContact->getValue(
                    DBEContact::fixedEmailFlag
                ),
                DBEContact::othersInitialLoggingEmailFlag => $this->dbeContact->getValue(
                    DBEContact::othersInitialLoggingEmailFlag
                ),
                DBEContact::othersWorkUpdatesEmailFlag    => $this->dbeContact->getValue(
                    DBEContact::othersWorkUpdatesEmailFlag
                ),
                DBEContact::othersFixedEmailFlag          => $this->dbeContact->getValue(
                    DBEContact::othersFixedEmailFlag
                ),
            ];
        }
        return $contacts;
    }

    /**
     *    Delete sites and contacts
     * @param $customerID
     * @return array
     */
    function getReviewContacts($customerID)
    {
        $this->setMethodName('getMainSupportContacts');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->getReviewContactsByCustomerID(
            $customerID
        );
        $contacts = [];
        while ($this->dbeContact->fetchNext()) {
            $contacts[] = [
                DBEContact::contactID                     => $this->dbeContact->getValue(DBEContact::contactID),
                DBEContact::firstName                     => $this->dbeContact->getValue(DBEContact::firstName),
                DBEContact::lastName                      => $this->dbeContact->getValue(DBEContact::lastName),
                DBEContact::email                         => $this->dbeContact->getValue(DBEContact::email),
                DBEContact::supportLevel                  => $this->dbeContact->getValue(DBEContact::supportLevel),
                DBEContact::initialLoggingEmailFlag       => $this->dbeContact->getValue(
                    DBEContact::initialLoggingEmailFlag
                ),
                DBEContact::fixedEmailFlag                => $this->dbeContact->getValue(
                    DBEContact::fixedEmailFlag
                ),
                DBEContact::othersInitialLoggingEmailFlag => $this->dbeContact->getValue(
                    DBEContact::initialLoggingEmailFlag
                ),
                DBEContact::othersFixedEmailFlag          => $this->dbeContact->getValue(
                    DBEContact::fixedEmailFlag
                ),
            ];
        }
        return $contacts;
    }

    /**
     *    Delete sites and contacts
     * @param $customerID
     * @param $siteNo
     */
    function deleteSite($customerID,
                        $siteNo
    )
    {
        $this->dbeContact->setValue(
            DBEContact::customerID,
            $customerID
        );
        $this->dbeContact->setValue(
            DBEContact::siteNo,
            $siteNo
        );
        $this->dbeContact->deleteRowsByCustomerIDSiteNo();
        $this->dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $this->dbeSite->setValue(
            DBESite::siteNo,
            $siteNo
        );
        $this->dbeSite->deleteRow();
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
        // sales orders
        $dbeOrdhead = new DBEOrdhead($this);
        if ($dbeOrdhead->countRowsByContactID($contactID) > 0) {
            return FALSE;
        }
        // sales invoices
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setValue(
            DBEInvhead::contactID,
            $contactID
        );
        if ($dbeInvhead->countRowsByColumn(DBEInvhead::contactID) > 0) {
            return FALSE;
        }
        // calls
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(
            DBECallActivity::contactID,
            $contactID
        );
        if ($dbeCallActivity->countRowsByColumn(DBECallActivity::contactID) > 0) {
            return FALSE;
        }
        return TRUE;    // no rows on dependent tables
    }

    /**
     *    Delete contact
     * @param $contactID
     */
    function deleteContact($contactID)
    {
        $this->dbeContact->setValue(
            DBEContact::contactID,
            $contactID
        );
        $this->dbeContact->deleteRow();
    }

    function createCustomerFolder($customerID)
    {
        $dir = $this->getCustomerFolderPath($customerID);
        /* check to see if the folder exists */
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        /*
        Then sub/folders
        */
        $subfolders = array(
            'Client Information Forms',
            'CNC Internet',
            'Current Documentation',
            'E-Support Packs',
            'PC Build Sheets',
            'Projects',
            'Review Meetings',
            'Software Licencing',
            'Vulnerability Scans',
            'Disaster Recovery Process'
        );
        foreach ($subfolders as $folder) {
            $this->createFolderIfNotExist($dir . '/' . $folder);
        }
        /*
        Then these under Current Documentation
        */
        $subfolders = array(
            'Documents and Forms',
            'Old Documentation',
            'Photos',
            'Bitlocker Recovery Keys'
        );
        foreach ($subfolders as $folder) {
            $this->createFolderIfNotExist($dir . '/Current Documentation/' . $folder);
        }
        $this->createFolderIfNotExist($dir . '/Current Documentation/Documents and Forms/Starters & Leavers');
        $this->createFolderIfNotExist($dir . '/Review Meetings/Analysis & Reports');
        $itemsForNextMeetingFilePath = $dir . '/Review Meetings/ITEMS FOR NEXT REVIEW MEETING.txt';
        if (!file_exists($itemsForNextMeetingFilePath)) {
            file_put_contents($itemsForNextMeetingFilePath, null);
        }
    }


    function getCustomerFolderPath($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        $customerDir = CUSTOMER_DIR;
        return $customerDir . '/' . $this->dbeCustomer->getValue(DBECustomer::name);
    }

    /**
     * @param $folderName
     * @return bool|void
     */
    private function createFolderIfNotExist($folderName)
    {
        if (file_exists($folderName)) {
            return;
        }
        return mkdir($folderName, 0777, true);
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

    function getCustomerFolderPathFromBrowser($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        return CUSTOMER_DIR_FROM_BROWSER . '/' . $this->dbeCustomer->getValue(DBECustomer::name);
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

    function getCurrentDocumentsFolderPath($customerID)
    {

        return $this->getCustomerFolderPath($customerID) . '/Current Documentation';

    }

    function getCurrentDocumentsFolderPathFromBrowser($customerID)
    {

        return $this->getCustomerFolderPathFromBrowser($customerID) . '/Current Documentation';

    }

    function getDailyCallList(CTCNC $controller,
                              &$dsResults,
                              $sortColumn = false
    )
    {
        if ($controller->hasPermissions(TECHNICAL_PERMISSION)) {
            $reviewUserID = false;
        } else {
            $reviewUserID = $GLOBALS['auth']->is_authenticated();
        }
        $this->dbeCustomer->getReviewList(
            $reviewUserID,
            $sortColumn
        );
        $ret = $this->getData(
            $this->dbeCustomer,
            $dsResults
        );
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
        $this->getData(
            $this->dbeCustomer,
            $dsResults
        );
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

    /**
     * @param $dsResults
     * @param bool $onlyCurrentCustomers
     * @return bool
     */
    function get24HourSupportCustomers(&$dsResults, $onlyCurrentCustomers = false)
    {
        $this->dbeCustomer->get24HourSupportCustomers($onlyCurrentCustomers);
        return $this->getData(
            $this->dbeCustomer,
            $dsResults
        );
    }

    function hasDefaultInvoiceContactsAtAllSites($customerID)
    {
        $db          = new dbSweetcode (); // database connection for query
        $dbeCustomer = new DBECustomer($this);
        $dbeSite     = new DBESite($this);
        $sql         = "SELECT COUNT(*) AS recCount
			FROM {$dbeCustomer->getTableName()}
				JOIN {$dbeSite->getTableName()} ON {$dbeCustomer->getDBColumnName(DBECustomer::customerID)} = {$dbeSite->getDBColumnName(DBESite::customerID)} AND {$dbeCustomer->getDBColumnName(DBECustomer::invoiceSiteNo)} = {$dbeSite->getDBColumnName(DBESite::siteNo)}
			WHERE
				{$dbeSite->getDBColumnName(DBESite::invoiceContactID)} = 0
				AND {$dbeCustomer->getDBColumnName(DBECustomer::becameCustomerDate)} is not null and {$dbeCustomer->getDBColumnName(DBECustomer::droppedCustomerDate)} is null
				AND {$dbeCustomer->getDBColumnName(DBECustomer::mailshotFlag)} = 'Y'
				AND {$dbeCustomer->getDBColumnName(DBECustomer::customerID)} = " . $customerID;
        $db->query($sql);
        $db->next_record();
        return $db->Record ['recCount'];

    }

    function getSpecialAttentionCustomers(&$dsResults)
    {
        $this->dbeCustomer->getSpecialAttentionCustomers();
        return $this->getData(
            $this->dbeCustomer,
            $dsResults
        );
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

    /**
     * @param $customerID
     * @return DBEContact|null
     */
    public function getPrimaryContact($customerID)
    {
        $this->dbeCustomer->getRow($customerID);
        $primaryMainContactID = $this->dbeCustomer->getValue(DBECustomer::primaryMainContactID);
        if (!$primaryMainContactID) {
            return null;
        }
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($primaryMainContactID);
        return $dbeContact;
    }

    public function getActiveCustomers(DataSet $dsCustomers, $ignoreProspects = false)
    {
        $this->dbeCustomer->getActiveCustomers($ignoreProspects);
        return $this->getData(
            $this->dbeCustomer,
            $dsCustomers
        );
    }

    /**
     * @param int $customerID
     * @return DBEPassword
     * @throws Exception
     */
    public function getOffice365PasswordItem(int $customerID)
    {
        $dbePassword = new DBEPassword($this);
        $dbePassword->getOffice365PasswordByCustomerID($customerID);
        return $dbePassword;
    }

    public function getPasswordItemByPasswordServiceId(int $customerId, $passwordServiceId)
    {
        $dbePassword = new DBEPassword($this);
        $dbePassword->getPasswordItemByCustomerIdAndServiceId($customerId, $passwordServiceId);
        return $dbePassword;
    }

    public function removeSupportForAllUsersAndReferCustomer($customerID)
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $dbeCustomer->setValue(DBECustomer::referredFlag, 'Y');
        $dbeCustomer->updateRow();
        $dsContacts = new DataSet($this);
        $this->getContactsByCustomerID($customerID, $dsContacts);
        while ($dsContacts->fetchNext()) {
            if (!$dsContacts->getValue(DBEContact::supportLevel)) {
                continue;
            }
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dsContacts->getValue(DBEContact::contactID));
            $dbeContact->setValue(DBEContact::supportLevel, null);
            $dbeContact->updateRow();
        }
    }

    /**
     * Get contact rows by customerID
     * @param integer $customerID
     * @param DataSet &$dsResults results
     * @param bool $includeInactive
     * @return bool : Success
     * @access public
     */
    function getContactsByCustomerID($customerID,
                                     &$dsResults,
                                     $includeInactive = false
    )
    {
        $this->setMethodName('getContactsByCustomerID');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $this->dbeContact->getRowsByCustomerID(
            $customerID,
            $includeInactive
        );
        return ($this->getData(
            $this->dbeContact,
            $dsResults
        ));
    }


}
