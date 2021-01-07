<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 09/01/2018
 * Time: 18:05
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DBEJOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_dbe'] . '/DBEJSite.php');
require_once($cfg['path_ct'] . '/CTCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');


class CTCustomerCRM extends CTCustomer
{

    const CTCUSTOMER_ACT_SEARCH_LEAD = 'searchLead';
    const searchFormCustomerID = 'customerID';
    private $reviewMeetingFrequencyMonths;
    private $lastReviewMeetingDate;
    private $reviewMeetingEmailSentFlag;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = ACCOUNT_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(403);
    }

    function initialProcesses()
    {
        $this->retrieveHTMLVars();
        parent::initialProcesses();
    } // end search

    function setSite(&$siteArray)
    {
        if (!is_array($siteArray)) {
            return;
        }
        foreach ($siteArray as $value) {
            $dbeJSite = new DBEJSite($this);
            $dbeJSite->setValue(
                DBESite::customerID,
                $value['customerID']
            );
            $dbeJSite->setValue(
                DBESite::siteNo,
                $value['siteNo']
            );
            $dbeJSite->getRow();
            $this->getData(
                $dbeJSite,
                $this->dsSite
            );
            $this->dsSite->setUpdateModeInsert();
            $this->dsSite->setValue(
                DBESite::add1,
                $value['add1']
            );
            $this->dsSite->setValue(
                DBESite::add2,
                $value['add2']
            );
            $this->dsSite->setValue(
                DBESite::add3,
                $value['add3']
            );
            $this->dsSite->setValue(
                DBESite::town,
                strtoupper($value['town'])
            );
            $this->dsSite->setValue(
                DBESite::county,
                $value['county']
            );
            $this->dsSite->setValue(
                DBESite::postcode,
                strtoupper($value['postcode'])
            );

            $this->dsSite->setValue(
                DBESite::phone,
                $value['sitePhone']
            );

            $this->dsSite->post();
        }
    }

    /**
     * @param DataSet|DBEntity $dbSource
     * @param DataSet $dsDestination
     * @return bool
     */
    function getData(&$dbSource,
                     &$dsDestination
    )
    {
        if (!is_object($dsDestination)) {
            $dsDestination = new Dataset($this);
        } else {
            if (
                ($dsDestination->getClassname() != DA_CLASSNAME_DATASET) &
                (!is_subclass_of(
                    $dsDestination,
                    DA_CLASSNAME_DATASET
                ))
            ) {
                $this->raiseError(
                    "dsDestination must be subclass or class of " .
                    DA_CLASSNAME_DATASET
                );
            }
        }
        if (gettype($dbSource) != "object") {
            $this->raiseError("dbSource is not initialised");
        }
        if (!is_subclass_of(
            $dbSource,
            DA_CLASSNAME_DBENTITY
        )) {
            $this->raiseError("dbSource must be subclass of " . DA_CLASSNAME_DBENTITY);
        }
        return ($dsDestination->replicate($dbSource));
    } // end search

    function setSiteNo($siteNo)
    {
        $this->setNumericVar(
            'siteNo',
            $siteNo
        );
    } // end search

    function setContactID($contactID)
    {
        $this->setNumericVar(
            'contactID',
            $contactID
        );
    }

    function getContactID()
    {
        return $this->contactID;
    }

    function setCustomer(&$customerArray)
    {
        if (!is_array($customerArray)) {
            return;
        }

        foreach ($customerArray as $value) {

            $this->buCustomer->getCustomerByID(
                @$value['customerID'],
                $this->dsCustomer
            );
            $this->dsCustomer->setUpdateModeInsert();
            $this->dsCustomer->setValue(
                DBECustomer::mailshotFlag,
                $this->getYN(@$value['mailshotFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::leadStatusId,
                @$value['leadStatusId']
            );
            $this->dsCustomer->setValue(
                DBECustomer::dateMeetingConfirmed,
                @$value['dateMeetingConfirmedDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::websiteURL,
                @$value['websiteURL']
            );

            $this->dsCustomer->setValue(
                DBECustomer::meetingDateTime,
                @$value['meetingDateTime']
            );
            $this->dsCustomer->setValue(
                DBECustomer::inviteSent,
                $this->getTrueFalse(@$value[DBECustomer::inviteSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportProcessed,
                $this->getTrueFalse(@$value[DBECustomer::reportProcessed])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportSent,
                $this->getTrueFalse(@$value[DBECustomer::reportSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::crmComments,
                @$value[DBECustomer::crmComments]
            );
            $this->dsCustomer->setValue(
                DBECustomer::companyBackground,
                @$value[DBECustomer::companyBackground]
            );
            $this->dsCustomer->setValue(
                DBECustomer::decisionMakerBackground,
                @$value[DBECustomer::decisionMakerBackground]
            );

            $this->dsCustomer->setValue(
                DBECustomer::primaryMainContactID,
                @$value[DBECustomer::primaryMainContactID]
            );

            $this->dsCustomer->setValue(
                DBECustomer::opportunityDeal,
                @$value[DBECustomer::opportunityDeal]
            );
            $this->dsCustomer->setValue(
                DBECustomer::rating,
                @$value[DBECustomer::rating]
            );
            $reviewDate = DateTime::createFromFormat(
                'Y-m-d',
                @$value[DBECustomer::reviewDate]
            );

            if ($reviewDate) {
                $reviewDateValue = null;
                if ($reviewDate) {
                    $reviewDateValue = $reviewDate->format(DATE_MYSQL_DATE);
                }

                $this->dsCustomer->setValue(
                    DBECustomer::reviewDate,
                    $reviewDateValue
                );

            }


            $this->dsCustomer->setValue(
                DBECustomer::reviewTime,
                @$value[DBECustomer::reviewTime]
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewUserID,
                @$value[DBECustomer::reviewUserID]
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewAction,
                @$value[DBECustomer::reviewAction]
            );
            $this->dsCustomer->post();
        }
    }

    private function getTrueFalse($value)
    {
        return $value == 'Y';
    }

    function getReviewMeetingFrequencyMonths()
    {
        return $this->reviewMeetingFrequencyMonths;
    }

    function setReviewMeetingFrequencyMonths($reviewMeetingFrequencyMonths)
    {
        $this->reviewMeetingFrequencyMonths = $reviewMeetingFrequencyMonths;
    }

    function getLastReviewMeetingDate()
    {
        return $this->lastReviewMeetingDate;
    }

    function setLastReviewMeetingDate($lastReviewMeetingDate)
    {
        $this->lastReviewMeetingDate = $lastReviewMeetingDate;
    }

    function getReviewMeetingEmailSentFlag()
    {
        return $this->reviewMeetingEmailSentFlag;
    }

    function setReviewMeetingEmailSentFlag($value)
    {
        $this->reviewMeetingEmailSentFlag = $value;
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
            case 'getReviewEngineers':
                return $this->getReviewEngineersController();
            case 'getCustomerReviewData':
                return $this->getCustomerReviewDataController();
            case 'updateCustomerReview':
                return $this->updateCustomerReviewController();
            case 'createCustomerFolder':
                $this->createCustomerFolder();
                break;
            case 'displayNextReviewProspect':
                $this->displayNextReviewProspect();
                break;
            case 'displayReviewList':
                $this->displayReviewList();
                break;
            case CTCUSTOMER_ACT_SEARCH:
                $this->search();
                break;
            case self::CTCUSTOMER_ACT_SEARCH_LEAD:
                echo $this->searchLead();
                break;
            case CTCNC_ACT_DISP_EDIT:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_DISP_SUCCESS:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_ADDCONTACT:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_DELETECONTACT:
                $this->deleteContact();
                break;
            case CTCUSTOMER_ACT_ADDSITE:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_DELETESITE:
                $this->deleteSite();
                break;
            case CTCUSTOMER_ACT_ADDCUSTOMER:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_DELETECUSTOMER:
                $this->deleteCustomer();
                break;
            case CTCUSTOMER_ACT_DISP_CUST_POPUP:
                $this->displayCustomerSelectPopup();
                break;
            case 'display24HourSupportCustomers':
                $this->display24HourSupportCustomers();
                break;
            case 'displaySpecialAttentionCustomers':
                $this->displaySpecialAttentionCustomers();
                break;
            case 'saveContactPassword':
                $response = [];
                try {
                    $this->saveContactPassword();
                    $response["status"] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response["status"] = "error";
                    $response["error"] = $exception->getMessage();
                }

                echo json_encode($response);
                break;
            case 'archiveContact':
                $response = [];
                try {
                    $this->clearContact();
                    $response["status"] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response["status"] = "error";
                    $response["error"] = $exception->getMessage();
                }

                echo json_encode($response);
                break;
            default:
                $this->search();
                break;
        }
    }

    function search()
    {

        $this->setMethodName('search');

        $dsSearchForm = new DSForm($this);
        $dsSearchForm->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_NOT_NULL
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $this->setCustomerID($dsSearchForm->getValue(self::searchFormCustomerID));
                $link = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'displayEditForm',
                        'customerID' => $this->getCustomerID()
                    )
                );

                header('Location: ' . $link);
            }

        } else {
            $this->setMethodName('displaySearchForm');

            $this->setTemplateFiles(
                array(
                    'CustomerCRM' => 'CRMSearch.inc'
                )
            );

            $urlSubmit = Controller::buildLink(
                $_SERVER ['PHP_SELF'],
                array('action' => CTCNC_ACT_SEARCH)
            );


            $this->setPageTitle('Customer CRM');
            $customerString = null;
            if ($dsSearchForm->getValue(self::searchFormCustomerID)) {
                $buCustomer = new BUCustomer ($this);
                $dsCustomer = new DataSet($this);
                $buCustomer->getCustomerByID(
                    $dsSearchForm->getValue(self::searchFormCustomerID),
                    $dsCustomer
                );
                $customerString = $dsCustomer->getValue(DBECustomer::name);
            }

            $urlCustomerPopup =
                Controller::buildLink(
                    CTCNC_PAGE_CUSTOMER,
                    array(
                        'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_block(
                'CustomerCRM',
                'customerLeadStatusBlock',
                'customerleadstatuses'
            );
            /**
             * @var DataSet $dsCustomerLeadStatuses
             */
            $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);

            while ($dsCustomerLeadStatuses->fetchNext()) {
                $this->template->set_var(
                    array(
                        'customerLeadStatusId'       => $dsCustomerLeadStatuses->getValue(
                            DBECustomerLeadStatus::id
                        ),
                        'customerLeadStatusName'     => $dsCustomerLeadStatuses->getValue(DBECustomerLeadStatus::name),
                        'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue(
                                DBECustomerLeadStatus::id
                            ) == $this->dsCustomer->getValue(DBECustomer::leadStatusId)) ? CT_SELECTED : null
                    )
                );
                $this->template->parse(
                    'customerleadstatuses',
                    'customerLeadStatusBlock',
                    true
                );
            }

            $linkURL =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'searchLead'
                    )
                );

            $this->template->set_var(
                array(
                    'formError'         => $this->formError,
                    'customerID'        => $dsSearchForm->getValue(self::searchFormCustomerID),
                    'customerIDMessage' => $dsSearchForm->getMessage(self::searchFormCustomerID),
                    'customerString'    => $customerString,
                    'urlCustomerPopup'  => $urlCustomerPopup,
                    'urlSubmit'         => $urlSubmit,
                    'ajaxURL'           => $linkURL,
                )
            );

            $this->template->parse(
                'CONTENTS',
                'CustomerCRM',
                true
            );

            $this->parsePage();
        }


    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar(
            'customerID',
            $customerID
        );
    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    /**
     * @return false|string
     * @throws Exception
     */
    function searchLead()
    {
        $customerLeadID = $_POST['customerLeadID'];
        // in the post we should find the id of the status we are searching for
        /** @var DBEContact $results */
        $results = $this->buCustomer->getContactsByLeadStatus($customerLeadID);

        $data = [];

        $customers = [];
        $dsCustomerLeadStatuses = new DataSet($this);
        $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);
        $leadStatuses = [];
        while ($dsCustomerLeadStatuses->fetchNext()) {
            $leadStatuses[$dsCustomerLeadStatuses->getValue(
                DBECustomerLeadStatus::id
            )] = $dsCustomerLeadStatuses->getValue(DBECustomerLeadStatus::name);
        }

        while ($results->fetchNext()) {
            $customerID = $results->getValue(DBEContact::customerID);

            if (!isset($customers[$customerID])) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($results->getValue(DBEContact::customerID));

                $link = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'displayEditForm',
                        'customerID' => $customerID
                    )
                );
                $customers[$customerID] = [
                    "customerName"        => $dbeCustomer->getValue(DBECustomer::name),
                    "customerLink"        => $link,
                    "customerReviewDate"  => $dbeCustomer->getValue(DBECustomer::reviewDate),
                    "bluestoneLeadStatus" => $leadStatuses[+$dbeCustomer->getValue(DBECustomer::leadStatusId)]
                ];

            }

            $phone = $results->getValue(DBEContact::phone);

            if (!$phone) {
                $site = new DBESite($this);
                $site->setValue(
                    DBESite::customerID,
                    $customerID
                );
                $site->setValue(
                    DBESite::siteNo,
                    $results->getValue(DBEContact::siteNo)
                );
                $site->getRow();

                $phone = $site->getValue(DBESite::phone);
            }

            $contactData = [
                "contactName"  => $results->getValue(DBEContact::firstName) . " " . $results->getValue(
                        DBEContact::lastName
                    ),
                "jobTitle"     => $results->getValue(DBEContact::position),
                'contactPhone' => $phone
            ];
            $data[] = array_merge(
                $contactData,
                $customers[$customerID]
            );


        }

        header('Content-Type: application/json;charset=utf-8');
        return json_encode($data);

    }

    /**
     * Form for editing customer details
     * @access private
     * @throws Exception
     */
    function displayEditForm()
    {
        $this->setMethodName('displayEditForm');
        $deleteCustomerURL = null;
        $deleteCustomerText = null;
        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {
            if ((!$this->formError) & ($this->getAction(
                    ) != CTCUSTOMER_ACT_DISP_SUCCESS)) {   // Not displaying form error page so get customer record
                if (!$this->buCustomer->getCustomerByID(
                    $this->getCustomerID(),
                    $this->dsCustomer
                )) {
                    $this->displayFatalError(CTCUSTOMER_MSG_CUS_NOT_FND);
                }
            }
            $this->dsCustomer->fetchNext();

            // If we can delete this customer set the link
            if ($this->buCustomer->canDeleteCustomer(
                $this->getCustomerID(),
                $this->userID
            )) {
                $deleteCustomerURL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTCUSTOMER_ACT_DELETECUSTOMER,
                        'customerID' => $this->getCustomerID()
                    )
                );
                $deleteCustomerText = 'Delete Customer';
            }
        } else {

            $this->dsCustomer->clearRows(
            );      // Creating a new customer - creates new row on dataset, NOT on the database yet
            $this->dsSite->clearRows();
            $this->dsContact->clearRows();
            $this->buCustomer->addNewCustomerRow($this->dsCustomer);
        }
        $this->setTemplateFiles(
            'CustomerEdit',
            'CustomerCRM.inc'
        );


// Parameters
        if ($this->getParam('save_page')) {
            $this->setSessionParam('save_page', $this->getParam('save_page'));
        } else {
            $this->setSessionParam('save_page', false);
        }
        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMER_ACT_UPDATE
                )
            );

        if ($this->getSessionParam('save_page')) {
            $cancelURL = $this->getSessionParam('save_page');
        } else {
            $cancelURL = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => CTCUSTOMER_ACT_DISP_SEARCH)
            );
        }
        $addSiteURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_ADDSITE,
                    'customerID' => $this->getCustomerID(),
                )
            );
        if (!$this->formError) {              // Not displaying form error page so get customer record
            $this->dsCustomer->setValue(
                self::customerFormNameClass,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER
            );
            $this->dsCustomer->setValue(
                self::customerFormInvoiceSiteMessage,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER
            );
            $this->dsCustomer->setValue(
                self::customerFormDeliverSiteMessage,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER
            );
        }
        $title = "Customer - " . $this->dsCustomer->getValue(DBECustomer::name);
        $color = "red";
        if ($this->dsCustomer->getValue(DBECustomer::websiteURL)) {
            $color = "green";
        }

        $this->setPageTitle(
            $title,
            $title . ' <i class="fas fa-globe" onclick="checkWebsite()" style="color:' . $color . '"></i>'
        );
        /*
        Get the list of custom letter template file names from the custom letter directory
        */
        $dir = LETTER_TEMPLATE_DIR . "/custom/";
        $customLetterTemplates = [];
        if (is_dir($dir)) {

            $dh = opendir($dir);

            while (false !== ($filename = readdir($dh))) {

                $ext = explode(
                    '.',
                    $filename
                );
                $ext = $ext[count($ext) - 1];

                if ($ext == 'htm') {
                    $customLetterTemplates[] = $filename;
                }
            }
        }

        if ($customerFolderPath = $this->buCustomer->customerFolderExists(
            $this->dsCustomer->getValue(DBECustomer::customerID)
        )) {
            $customerFolderLink =
                '<a href="file:' . $customerFolderPath . '" target="_blank" title="Open Folder">Open Folder</a>';
        } else {
            $urlCreateCustomerFolder =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'createCustomerFolder',
                        'customerID' => $this->getCustomerID(),
                    )
                );
            $customerFolderLink =
                '<a href="http:' . $urlCreateCustomerFolder . '" title="Create Folder">Create Customer Folder</a>';
        }
        $renewalLinkURL =
            Controller::buildLink(
                'RenewalReport.php',
                array(
                    'action'     => 'produceReport',
                    'customerID' => $this->getCustomerID()
                )
            );


        $renewalLink = '<a href="' . $renewalLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';

        $passwordLinkURL =
            Controller::buildLink(
                'Password.php',
                array(
                    'action'     => 'list',
                    'customerID' => $this->getCustomerID()
                )
            );


        $passwordLink = '<a href="' . $passwordLinkURL . '" target="_blank" title="Passwords">Service Passwords</a>';

        $bodyTagExtras = 'onLoad="loadNote(\'last\')"';

        $urlContactPopup =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
//          'action' => CTCNC_ACT_CONTACT_EDIT,
'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


        $site = new DBESite($this);
        $site->setValue(
            DBESite::siteNo,
            $this->dsCustomer->getValue(DBECustomer::deliverSiteNo)
        );
        $site->setValue(
            DBESite::customerID,
            $this->getCustomerID()
        );
        $site->getRowByCustomerIDSiteNo();

        $this->template->set_var(
            array(
                'urlContactPopup'                    => $urlContactPopup,
                'bodyTagExtras'                      => $bodyTagExtras,
                /* hidden */
                'reviewMeetingEmailSentFlag'         => $this->dsCustomer->getValue(
                    DBECustomer::reviewMeetingEmailSentFlag
                ),
                'customerID'                         => $this->dsCustomer->getValue(DBECustomer::customerID),
                'customerName'                       => $this->dsCustomer->getValue(DBECustomer::name),
                'reviewCount'                        => $this->buCustomer->getReviewCount(),
                'customerFolderLink'                 => $customerFolderLink,
                'regNo'                              => $this->dsCustomer->getValue(DBECustomer::regNo),
                'websiteURL'                         => $this->dsCustomer->getValue(DBECustomer::websiteURL),
                'mailshotFlagChecked'                => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::mailshotFlag)
                ),
                'referredFlagChecked'                => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::referredFlag)
                ),
                'specialAttentionFlagChecked'        => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::specialAttentionFlag)
                ),
                'specialAttentionEndDate'            => $this->dsCustomer->getValue(
                    DBECustomer::specialAttentionEndDate
                ),
                'lastReviewMeetingDate'              => $this->dsCustomer->getValue(DBECustomer::lastReviewMeetingDate),
                'dateMeetingConfirmedDate'           => $this->dsCustomer->getValue(DBECustomer::dateMeetingConfirmed),
                'meetingDateTime'                    => Controller::dateToISO(
                    $this->dsCustomer->getValue(DBECustomer::meetingDateTime)
                ),
                DBECustomer::inviteSent              => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::inviteSent)
                ),
                DBECustomer::reportProcessed         => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::reportProcessed)
                ),
                DBECustomer::reportSent              => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::reportSent)
                ),
                DBECustomer::crmComments             => $this->dsCustomer->getValue(DBECustomer::crmComments),
                DBECustomer::companyBackground       => $this->dsCustomer->getValue(DBECustomer::companyBackground),
                DBECustomer::decisionMakerBackground => $this->dsCustomer->getValue(
                    DBECustomer::decisionMakerBackground
                ),
                DBECustomer::opportunityDeal         => $this->dsCustomer->getValue(DBECustomer::opportunityDeal),
                DBECustomer::rating                  => $this->dsCustomer->getValue(DBECustomer::rating),
                'pcxFlagChecked'                     => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::pcxFlag)
                ),
                'createDate'                         => $this->dsCustomer->getValue(DBECustomer::createDate),
                'mailshot2FlagDesc'                  => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot2FlagDesc
                ),
                'mailshot3FlagDesc'                  => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot3FlagDesc
                ),
                'mailshot4FlagDesc'                  => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot4FlagDesc
                ),
                'mailshot8FlagDesc'                  => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot8FlagDesc
                ),
                'mailshot9FlagDesc'                  => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot9FlagDesc
                ),
                'mailshot11FlagDesc'                 => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot11FlagDesc
                ),
                'submitURL'                          => $submitURL,
                'renewalLink'                        => $renewalLink,
                'passwordLink'                       => $passwordLink,
                'deleteCustomerURL'                  => $deleteCustomerURL,
                'deleteCustomerText'                 => $deleteCustomerText,
                'cancelURL'                          => $cancelURL,
                'disabled'                           => $this->hasPermissions(
                    SALES_PERMISSION
                ) ? null : CTCNC_HTML_DISABLED,
                'gscTopUpAmount'                     => $this->dsCustomer->getValue(DBECustomer::gscTopUpAmount),
                'noOfServers'                        => $this->dsCustomer->getValue(DBECustomer::noOfServers),
                'noOfSites'                          => $this->dsCustomer->getValue(DBECustomer::noOfSites),
                'modifyDate'                         => $this->dsCustomer->getValue(DBECustomer::modifyDate),
                'reviewDate'                         => $this->dsCustomer->getValue(DBECustomer::reviewDate),
                'reviewTime'                         => $this->dsCustomer->getValue(DBECustomer::reviewTime),
                'referred'                           => $this->dsCustomer->getValue(
                    DBECustomer::referredFlag
                ) == 'Y' ? 'true' : 'false',
                'becameCustomerDate'                 => $this->dsCustomer->getValue(DBECustomer::becameCustomerDate),
                'droppedCustomerDate'                => $this->dsCustomer->getValue(DBECustomer::droppedCustomerDate),
                'reviewAction'                       => $this->dsCustomer->getValue(DBECustomer::reviewAction),
                'comments'                           => $this->dsCustomer->getValue(DBECustomer::comments),
                'techNotes'                          => $this->dsCustomer->getValue(DBECustomer::techNotes),
                'slaP1'                              => $this->dsCustomer->getValue(DBECustomer::slaP1),
                'slaP2'                              => $this->dsCustomer->getValue(DBECustomer::slaP2),
                'slaP3'                              => $this->dsCustomer->getValue(DBECustomer::slaP3),
                'slaP4'                              => $this->dsCustomer->getValue(DBECustomer::slaP4),
                'slaP5'                              => $this->dsCustomer->getValue(DBECustomer::slaP5),
                'slaFixHoursP1'                      => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP1),
                'slaFixHoursP2'                      => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP2),
                'slaFixHoursP3'                      => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP3),
                'slaFixHoursP4'                      => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP4),

                'add1' => $site->getValue(DBESite::add1),
                'add2' => $site->getValue(DBESite::add2),
                'add3' => $site->getValue(DBESite::add3),

                'town'                 => $site->getValue(DBESite::town),
                'county'               => $site->getValue(DBESite::county),
                'postcode'             => $site->getValue(DBESite::postcode),
                'sitePhone'            => $site->getValue(DBESite::phone),
                'siteNo'               => $site->getValue(DBESite::siteNo),
                'sageRef'              => $site->getValue(DBESite::sageRef),
                'debtorCode'           => $site->getValue(DBESite::debtorCode),
                'maxTravelHours'       => $site->getValue(DBESite::maxTravelHours),
                'deliverContactID'     => $site->getValue(DBESite::deliverContactID),
                'activeFlagChecked'    => ($site->getValue(DBESite::activeFlag) == 'Y') ? CT_CHECKED : null,
                'activeFlag'           => $site->getValue(DBESite::activeFlag),
                'deliveryContactID'    => $site->getValue(DBESite::deliverContactID),
                'invoiceContactID'     => $site->getValue(DBESite::deliverContactID),
                'nonUKFlag'            => $site->getValue(DBESite::nonUKFlag),
                'deleteSiteText'       => null,
                'deleteSiteURL'        => null,
                "primaryMainContactID" => $this->dsCustomer->getValue(DBECustomer::primaryMainContactID),
            )
        );
        if ((!$this->formError) & ($this->getAction(
                ) != CTCUSTOMER_ACT_ADDCUSTOMER)) {                                                      // Only get from DB if not displaying form error(s)
            $this->template->set_var(
                array(
                    'addSiteText' => CTCUSTOMER_TXT_ADD_SITE,
                    'addSiteURL'  => $addSiteURL
                )
            );
        }
        $noOfPCs =
            array(
                '0',
                '1-5',
                '6-10',
                '11-25',
                '26-50',
                '51-99',
                '100+'
            );

        $this->template->set_block(
            'CustomerEdit',
            'noOfPCsBlock',
            'noOfPCs'
        );
        foreach ($noOfPCs as $index => $value) {
            $this->template->set_var(
                array(
                    'noOfPCsValue'    => $value,
                    'noOfPCsSelected' => $value == $this->dsCustomer->getValue(
                        DBECustomer::noOfPCs
                    ) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'noOfPCs',
                'noOfPCsBlock',
                true
            );
        }

        $this->template->set_block(
            'CustomerEdit',
            'customerTypeBlock',
            'customertypes'
        );

        $dsCustomerType = new DataSet($this);
        $this->buCustomer->getCustomerTypes($dsCustomerType);
        while ($dsCustomerType->fetchNext()) {
            $this->template->set_var(
                array(
                    'customerTypeID'          => $dsCustomerType->getValue(DBECustomerType::customerTypeID),
                    'customerTypeDescription' => $dsCustomerType->getValue(DBECustomerType::description),
                    'customerTypeSelected'    => ($dsCustomerType->getValue(
                            DBECustomerType::customerTypeID
                        ) == $this->dsCustomer->getValue(DBECustomer::customerTypeID)) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'customertypes',
                'customerTypeBlock',
                true
            );
        }

        $this->template->set_block(
            'CustomerEdit',
            'reviewFrequencyBlock',
            'reviewFrequencies'
        );
        foreach ($this->meetingFrequency as $index => $value) {
            $this->template->set_var(
                array(
                    'reviewMeetingFrequencyMonths'            => $index,
                    'reviewMeetingFrequencyMonthsDescription' => $value,
                    'reviewMeetingFrequencyMonthsSelected'    => $index == $this->dsCustomer->getValue(
                        DBECustomer::reviewMeetingFrequencyMonths
                    ) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'reviewFrequencies',
                'reviewFrequencyBlock',
                true
            );
        }

        $this->template->set_block(
            'CustomerEdit',
            'templateSupportLevelBlock',
            'templateSelectSupportLevel'
        );

        $this->template->set_block(
            'CustomerEdit',
            'templateCustomLetterBlock',
            'templateCustomLetters'
        );

        /*
       Display all the custom letters
       */
        foreach ($customLetterTemplates as $index => $filename) {

            $customLetterURL =
                Controller::buildLink(
                    'LetterForm.php',
                    array(
                        'contactID'      => $this->dsContact->getValue(DBEContact::contactID),
                        'letterTemplate' => $filename
                    )
                );


            $this->template->set_var(

                array(
                    'customLetterURL'  => $customLetterURL,
                    'customLetterName' => $filename

                )
            );

            $this->template->parse(
                'templateCustomLetters',
                'templateCustomLetterBlock',
                true
            );

        } // end foreach

        $buContact = new BUContact($this);
        $buContact->supportLevelDropDown(
            null,
            $this->template,
            'supportLevelSelected',
            'supportLevelValue',
            'supportLevelDescription',
            'templateSelectSupportLevel',
            'templateSupportLevelBlock'
        );

        $this->template->set_block(
            'CustomerEdit',
            'customerLeadStatusBlock',
            'customerleadstatuses'
        );
        /**
         * @var DataSet $dsCustomerLeadStatuses
         */
        $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);

        while ($dsCustomerLeadStatuses->fetchNext()) {

            $this->template->set_var(
                array(
                    'customerLeadStatusId'       => $dsCustomerLeadStatuses->getValue(
                        DBECustomerLeadStatus::id
                    ),
                    'customerLeadStatusName'     => $dsCustomerLeadStatuses->getValue(DBECustomerLeadStatus::name),
                    'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue(
                            DBECustomerLeadStatus::id
                        ) == $this->dsCustomer->getValue(DBECustomer::leadStatusId)) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'customerleadstatuses',
                'customerLeadStatusBlock',
                true
            );
        }

        $buSector = new BUSector($this);
        $this->template->set_block(
            'CustomerEdit',
            'sectorBlock',
            'sectors'
        );
        $dsSector = new DataSet($this);
        $buSector->getAll($dsSector);
        while ($dsSector->fetchNext()) {
            $this->template->set_var(
                array(
                    'sectorID'          => $dsSector->getValue(DBESector::sectorID),
                    'sectorDescription' => $dsSector->getValue(DBESector::description),
                    'sectorSelected'    => ($dsSector->getValue(DBESector::sectorID) == $this->dsCustomer->getValue(
                            DBECustomer::sectorID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'sectors',
                'sectorBlock',
                true
            );
        }
        $this->template->set_block(
            'CustomerEdit',
            'leadStatusBlock',
            'leadStatus'
        );
        $dsLeadStatus = new DataSet($this);
        $this->buCustomer->getLeadStatus($dsLeadStatus);
        while ($dsLeadStatus->fetchNext()) {

            $this->template->set_var(
                array(
                    'leadStatusID'          => $dsLeadStatus->getValue(DBECustomerLeadStatus::id),
                    'leadStatusDescription' => $dsLeadStatus->getValue(DBECustomerLeadStatus::name),
                    'leadStatusSelected'    => ($dsLeadStatus->getValue(
                            DBECustomerLeadStatus::id
                        ) == $this->dsCustomer->getValue(
                            DBECustomer::leadStatusId
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'leadStatus',
                'leadStatusBlock',
                true
            );
        }
        /*
        Review users
        */
        $this->template->set_block(
            'CustomerEdit',
            'reviewUserBlock',
            'reviewUsers'
        );

        $buUser = new BUUser($this);
        $dsUser = new DataSet($this);
        $buUser->getAllUsers($dsUser);

        while ($dsUser->fetchNext()) {

            $this->template->set_var(
                array(
                    'reviewUserID'       => $dsUser->getValue(DBEUser::userID),
                    'reviewUserName'     => $dsUser->getValue(DBEUser::name),
                    'reviewUserSelected' => ($dsUser->getValue(DBEUser::userID) == $this->dsCustomer->getValue(
                            DBECustomer::reviewUserID
                        )) ? CT_SELECTED : null
                )
            );

            $this->template->parse(
                'reviewUsers',
                'reviewUserBlock',
                true
            );
        }

        /*
        Account Manager users
        */
        $this->template->set_block(
            'CustomerEdit',
            'accountManagerBlock',
            'accountManagers'
        );

        $buUser = new BUUser($this);
        $buUser->getAllUsers($dsUser);

        while ($dsUser->fetchNext()) {

            $this->template->set_var(
                array(
                    'accountManagerUserID'       => $dsUser->getValue(DBEUser::userID),
                    'accountManagerUserName'     => $dsUser->getValue(DBEUser::name),
                    'accountManagerUserSelected' => ($dsUser->getValue(DBEUser::userID) == $this->dsCustomer->getValue(
                            DBECustomer::accountManagerUserID
                        )) ? CT_SELECTED : null
                )
            );

            $this->template->parse(
                'accountManagers',
                'accountManagerBlock',
                true
            );
        }

        /*
        Projects
        */
        $addProjectURL =
            Controller::buildLink(
                'Project.php',
                array(
                    'action'     => 'add',
                    'customerID' => $this->getCustomerID()
                )
            );

        $this->template->set_var(
            array(
                'addProjectText' => 'Add project',
                'addProjectURL'  => $addProjectURL
            )
        );

        $this->template->set_block(
            'CustomerEdit',
            'projectBlock',
            'projects'
        );      // have to declare innermost block first

        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $buProject = new BUProject($this);
            $dsProject = new DataSet($this);
            $buProject->getProjectsByCustomerID(
                $this->getCustomerID(),
                $dsProject
            );

            while ($dsProject->fetchNext()) {
                $deleteProjectLink = null;
                $deleteProjectText = null;
                if ($buProject->canDelete($dsProject->getValue(DBEProject::projectID))) {
                    $deleteProjectLink =
                        Controller::buildLink(
                            'Project.php',
                            array(
                                'action'    => 'delete',
                                'projectID' => $dsProject->getValue(DBEProject::projectID)
                            )
                        );
                    $deleteProjectText = 'delete';
                }

                $editProjectLink =
                    Controller::buildLink(
                        'Project.php',
                        array(
                            'action'    => 'edit',
                            'projectID' => $dsProject->getValue(DBEProject::projectID)
                        )
                    );

                $this->template->set_var(
                    array(
                        'projectID'         => $dsProject->getValue(DBEProject::projectID),
                        'projectName'       => $dsProject->getValue(DBEProject::description),
                        'notes'             => substr(
                            $dsProject->getValue(DBEProject::notes),
                            0,
                            50
                        ),
                        'startDate'         => strftime(
                            "%d/%m/%Y",
                            strtotime($dsProject->getValue(DBEProject::openedDate))
                        ),
                        'expiryDate'        => strftime(
                            "%d/%m/%Y",
                            strtotime($dsProject->getValue(DBEProject::completedDate))
                        ),
                        'editProjectLink'   => $editProjectLink,
                        'deleteProjectLink' => $deleteProjectLink,
                        'deleteProjectText' => $deleteProjectText
                    )
                );

                $this->template->parse(
                    'projects',
                    'projectBlock',
                    true
                );

            }

        }


        $this->template->set_block(
            'CustomerEdit',
            'selectInvoiceContactBlock',
            'invoiceContacts'
        );

        $this->template->set_block(
            'CustomerEdit',
            'selectDeliverContactBlock',
            'deliverContacts'
        );


        $this->template->set_block(
            'CustomerEdit',
            'siteBlock',
            'sites'
        );


        $this->template->set_block(
            'CustomerEdit',
            'customLetterBlock',
            'customLetters'
        );      //

        $this->template->set_block(
            'CustomerEdit',
            'selectSiteBlock',
            'selectSites'
        );

        $this->template->set_block(
            'CustomerEdit',
            'supportLevelBlock',
            'selectSupportLevel'
        );

        $this->template->set_block(
            'CustomerEdit',
            'contactBlock',
            'contacts'
        );      // have to declare innermost block first

        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) & ($this->getAction(
                ) != CTCUSTOMER_ACT_DISP_SUCCESS)) {                                                      // Only get from DB if not displaying form error(s)
            $this->buCustomer->getSitesByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsSite,
                true
            );

            $this->buCustomer->getContactsByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsContact,
                false
            );

            if ($this->getAction() == CTCUSTOMER_ACT_ADDCONTACT) {
                $this->buCustomer->addNewContactRow(
                    $this->dsContact,
                    $this->getCustomerID(),
                    $this->getSiteNo()
                );
            }

            if ($this->getAction() == CTCUSTOMER_ACT_ADDSITE) {
                $this->buCustomer->addNewSiteRow(
                    $this->dsSite,
                    $this->getCustomerID()
                );
            }
        }

        $this->dsSite->initialise();

        $errorClass = null;
        if (!$this->formError) {// Only get from DB if not displaying form error(s)
            $errorClass = CTCUSTOMER_CLS_TABLE_EDIT_HEADER;
        }

        $addContactURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_ADDCONTACT,
                    'customerID' => $site->getValue(DBESite::customerID),
                    'siteNo'     => $site->getValue(DBESite::siteNo)
                )
            );

        $deleteSiteURL = null;
        $deleteSiteText = null;
        if ($this->buCustomer->canDeleteSite(
            $site->getValue(DBESite::customerID),
            $site->getValue(DBESite::siteNo)
        )) {
            $deleteSiteURL = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_DELETESITE,
                    'customerID' => $site->getValue(DBESite::customerID),
                    'siteNo'     => $site->getValue(DBESite::siteNo)
                )
            );
            $deleteSiteText = 'Delete Site';
        }
        //Horrible hack cause I don't understand why these are empty strings when they should be zero values!
        if ($this->dsCustomer->getValue(DBECustomer::invoiceSiteNo) == '') {
            $this->dsCustomer->setValue(
                DBECustomer::invoiceSiteNo,
                0
            );
        }
        if ($this->dsCustomer->getValue(DBECustomer::deliverSiteNo) == '') {
            $this->dsCustomer->setValue(
                DBECustomer::deliverSiteNo,
                0
            );
        }
        $this->template->set_var(
            array(
                'add1Class'      => $errorClass,
                'add1'           => $site->getValue(DBESite::add1),
                'add2'           => $site->getValue(DBESite::add2),
                'add3'           => $site->getValue(DBESite::add3),
                'town'           => $site->getValue(DBESite::town),
                'county'         => $site->getValue(DBESite::county),
                'postcode'       => $site->getValue(DBESite::postcode),
                'sitePhone'      => $site->getValue(DBESite::phone),
                'siteNo'         => $site->getValue(DBESite::siteNo),
                'customerID'     => $site->getValue(DBESite::customerID),
                'sageRef'        => $site->getValue(DBESite::sageRef),
                'debtorCode'     => $site->getValue(DBESite::debtorCode),
                'maxTravelHours' => $site->getValue(DBESite::maxTravelHours),

                'invoiceSiteFlagChecked' => ($this->dsCustomer->getValue(DBECustomer::invoiceSiteNo) == $site->getValue(
                        DBESite::siteNo
                    )) ? CT_CHECKED : null,
                'deliverSiteFlagChecked' => ($this->dsCustomer->getValue(DBECustomer::deliverSiteNo) == $site->getValue(
                        DBESite::siteNo
                    )) ? CT_CHECKED : null,
                'activeFlagChecked'      => ($site->getValue(DBESite::activeFlag) == 'Y') ? CT_CHECKED : null,
                'deleteSiteText'         => $deleteSiteText,
                'deleteSiteURL'          => $deleteSiteURL
            )
        );

        $this->template->set_block(
            'CustomerEdit',
            'invoiceContacts',
            null
        );


        $this->parseContactSelector(
            $site->getValue(DBESite::invoiceContactID),
            $this->dsContact,
            'invoiceContacts',
            'selectInvoiceContactBlock'
        );

        $this->template->set_block(
            'CustomerEdit',
            'deliverContacts',
            null
        );

        $this->parseContactSelector(
            $site->getValue(DBESite::deliverContactID),
            $this->dsContact,
            'deliverContacts',
            'selectDeliverContactBlock'
        );


        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER)) {
            $this->template->set_var(
                array(
                    'addContactText' => CTCUSTOMER_TXT_ADD_CONTACT,
                    'addContactURL'  => $addContactURL
                )
            );
        }

        $this->template->parse(
            'sites',
            'siteBlock',
            true
        );

        $this->dsContact->initialise();
        $this->dsContact->sortAscending(DBEContact::lastName);

        while ($this->dsContact->fetchNext()) {

            $this->template->set_block(
                'CustomerEdit',
                'selectSites',
                null
            );
            $this->template->set_block(
                'CustomerEdit',
                'customLetters',
                null
            );

            $this->template->set_block(
                'CustomerEdit',
                'selectSupportLevel',
                null
            );

            $deleteContactURL = null;
            $deleteContactLink = null;
            $clientFormURL = null;
            $dearJohnURL = null;
            $dmLetterURL = null;
            $customLetter1URL = null;
            if ($this->dsContact->getValue(DBEContact::contactID) != 0) {
                $deleteContactURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTCUSTOMER_ACT_DELETECONTACT,
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                        )
                    );
                /** @noinspection HtmlDeprecatedAttribute */
                $deleteContactLink =
                    '<a href="' . $deleteContactURL . '"><img align=middle border=0 hspace=2 src="images/icondelete.gif" alt="Delete contact" onClick="if(!confirm(\'Are you sure you want to delete this contact?\')) return(false)"></a>';
                $dearJohnURL =
                    Controller::buildLink(
                        'DearJohnForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                        )
                    );
                $dmLetterURL =
                    Controller::buildLink(
                        'DMLetterForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)//,
                            //                  'letterTemplate' => 'dm_letter'
                        )
                    );
            }

            $this->template->set_var(
                array(
                    'contactID'                            => $this->dsContact->getValue(DBEContact::contactID),
                    'siteNo'                               => $this->dsContact->getValue(DBEContact::siteNo),
                    'customerID'                           => $this->dsContact->getValue(DBEContact::customerID),
                    'supplierID'                           => $this->dsContact->getValue(DBEContact::supplierID),
                    'title'                                => $this->dsContact->getValue(DBEContact::title),
                    'titleClass'                           => $this->dsContact->getValue(self::contactFormTitleClass),
                    'firstName'                            => $this->dsContact->getValue(DBEContact::firstName),
                    'lastName'                             => $this->dsContact->getValue(DBEContact::lastName),
                    'firstNameClass'                       => $this->dsContact->getValue(
                        self::contactFormFirstNameClass
                    ),
                    'lastNameClass'                        => $this->dsContact->getValue(
                        self::contactFormLastNameClass
                    ),
                    'phone'                                => $this->dsContact->getValue(DBEContact::phone),
                    'mobilePhone'                          => $this->dsContact->getValue(DBEContact::mobilePhone),
                    'position'                             => $this->dsContact->getValue(DBEContact::position),
                    'fax'                                  => $this->dsContact->getValue(DBEContact::fax),
                    'portalPasswordButtonClass'            => $this->dsContact->getValue(
                        DBEContact::portalPassword
                    ) ? 'lockedIcon' : 'unlockedIcon',
                    'pendingLeaverFlagChecked'             => ($this->dsContact->getValue(
                            DBEContact::pendingLeaverFlag
                        ) == 'Y') ? CT_CHECKED : null,
                    'pendingLeaverDate'                    => $this->dsContact->getValue(DBEContact::pendingLeaverDate),
                    'failedLoginCount'                     => $this->dsContact->getValue(DBEContact::failedLoginCount),
                    'email'                                => $this->dsContact->getValue(DBEContact::email),
                    'emailClass'                           => $this->dsContact->getValue(self::contactFormEmailClass),
                    'notes'                                => $this->dsContact->getValue(DBEContact::notes),
                    'discontinuedFlag'                     => $this->dsContact->getValue(DBEContact::discontinuedFlag),
                    'invoiceContactFlagChecked'            => ($this->dsContact->getValue(
                            DBEContact::contactID
                        ) == $this->dsSite->getValue(
                            DBESite::invoiceContactID
                        )) ? CT_CHECKED : null,
                    'deliverContactFlagChecked'            => ($this->dsContact->getValue(
                            DBEContact::contactID
                        ) == $this->dsSite->getValue(
                            DBESite::deliverContactID
                        )) ? CT_CHECKED : null,
                    'sendMailshotFlagChecked'              => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::sendMailshotFlag)
                    ),
                    'accountsFlagChecked'                  => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::accountsFlag)
                    ),
                    'mailshot2FlagChecked'                 => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot2Flag)
                    ),
                    'mailshot3FlagChecked'                 => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot3Flag)
                    ),
                    'mailshot4FlagChecked'                 => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot4Flag)
                    ),
                    'mailshot8FlagChecked'                 => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot8Flag)
                    ),
                    'mailshot9FlagChecked'                 => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot9Flag)
                    ),
                    'mailshot11FlagChecked'                => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot11Flag)
                    ),
                    'reviewUserFlagChecked'                => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::reviewUser)
                    ),
                    'initialLoggingEmailFlagChecked'       => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::initialLoggingEmailFlag)
                    ),
                    'fixedEmailFlagChecked'                => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::fixedEmailFlag)
                    ),
                    'othersInitialLoggingEmailFlagChecked' => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersInitialLoggingEmailFlag)
                    ),
                    'othersWorkUpdatesEmailFlagChecked'    => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersWorkUpdatesEmailFlag)
                    ),
                    'othersFixedEmailFlagChecked'          => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersFixedEmailFlag)
                    ),

                    'hrUserFlagChecked'                    => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::hrUser)
                    ),
                    'topUpValidation'                      => $this->buCustomer->hasPrepayContract(
                        DBEContact::customerID
                    ) ? 'data-validation="atLeastOne"' : null,
                    'specialAttentionContactFlagChecked'   => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::specialAttentionContactFlag)
                    ),
                    'clientFormURL'                        => $clientFormURL,
                    'dearJohnURL'                          => $dearJohnURL,
                    'dmLetterURL'                          => $dmLetterURL,
                    'customLetter1URL'                     => $customLetter1URL,
                    'deleteContactLink'                    => $deleteContactLink,
                    'linkedInURL'                          => $this->dsContact->getValue(DBEContact::linkedInURL),
                    'linkedInColor'                        => $this->dsContact->getValue(
                        DBEContact::linkedInURL
                    ) ? 'green' : 'red'
                )
            );

            $this->siteDropdown(
                $this->dsContact->getValue(DBEContact::customerID),
                $this->dsContact->getValue(DBEContact::siteNo)
            );

            $buContact = new BUContact($this);

            $buContact->supportLevelDropDown(
                $this->dsContact->getValue(DBEContact::supportLevel),
                $this->template
            );

            /*
            Display all the custom letters
            */
            foreach ($customLetterTemplates as $index => $filename) {

                $customLetterURL =
                    Controller::buildLink(
                        'LetterForm.php',
                        array(
                            'contactID'      => $this->dsContact->getValue(DBEContact::contactID),
                            'letterTemplate' => $filename
                        )
                    );


                $this->template->set_var(

                    array(
                        'customLetterURL'  => $customLetterURL,
                        'customLetterName' => $filename

                    )
                );

                $this->template->parse(
                    'customLetters',
                    'customLetterBlock',
                    true
                );

            } // end foreach


            $this->template->parse(
                'contacts',
                'contactBlock',
                true
            );

        }
        /*
        List of sales orders with links. Very similar to code in CTSalesOrder
        */
        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $dbeJOrdhead = new DBEJOrdhead($this);
            $dbeJOrdhead->getRowsBySearchCriteria(
                $this->getCustomerID(),
                false,
                false,
                false,
                false,
                false,
                false
            );

            $this->template->set_block(
                'CustomerEdit',
                'orderBlock',
                'orders'
            );

            while ($dbeJOrdhead->fetchNext()) {

                $ordheadID = $dbeJOrdhead->getPKValue();

                $orderURL =
                    Controller::buildLink(
                        'SalesOrder.php',
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $ordheadID
                        )
                    );

                $this->template->set_var(
                    array(
                        'orderURL'  => $orderURL,
                        'ordheadID' => $ordheadID,
                        'orderType' => $this->getOrderTypeDescription($dbeJOrdhead->getValue(DBEJOrdhead::type)),
                        'orderDate' => strftime(
                            "%d/%m/%Y",
                            strtotime($dbeJOrdhead->getValue(DBEJOrdhead::date))
                        ),
                        'custPORef' => $dbeJOrdhead->getValue(DBEJOrdhead::custPORef)
                    )
                );

                $this->template->parse(
                    'orders',
                    'orderBlock',
                    true
                );

            }

        }

        $this->template->setVar(
            'javaScript',
            "<script src='components/customerEditMain/dist/CustomerReviewComponent.js?version=1.0.0'></script><script src=components/customerEditMain/dist/CustomerNotesComponent.js?version=1.0.0'></script>",
        );

        $this->template->parse(
            'CONTENTS',
            'CustomerEdit',
            true
        );
        $this->parsePage();
    }

    function getChecked($flag)
    {
        return ($flag == 'N' || $flag == false ? null : CT_CHECKED);
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

    /**
     * Update details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->setCustomerID($this->dsCustomer->getValue(DBECustomer::customerID));
        if (!$this->formError) {

            $this->buCustomer->updateCustomer($this->dsCustomer);
            $this->buCustomer->updateSite($this->dsSite);
            if (isset($this->postVars["form"]["contact"])) {
                $this->buCustomer->updateContact($this->dsContact);
            }

            $this->setAction(CTCUSTOMER_ACT_DISP_SUCCESS);
            if ($this->getSessionParam('save_page')) {
                header('Location: ' . $_SESSION['save_page']);
                exit;
            } else {
                $nextURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTCNC_ACT_DISP_EDIT,
                            'customerID' => $this->getCustomerID()
                        )
                    );

                header('Location: ' . $nextURL);
                exit;
            }
        } else {
            $this->displayEditForm();
        }
    }
}