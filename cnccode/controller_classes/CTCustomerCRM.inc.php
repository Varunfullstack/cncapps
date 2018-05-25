<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 09/01/2018
 * Time: 18:05
 */
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DBEJOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Parameters
define('CTCUSTOMER_VAL_NONE_SELECTED', -1);
// Actions
define('CTCUSTOMER_ACT_DISP_SEARCH', 'dispSearch');
define('CTCUSTOMER_ACT_SEARCH', 'search');
define('CTCUSTOMER_ACT_DISP_LIST', 'dispList');
define('CTCUSTOMER_ACT_UPDATE', 'update');
define('CTCUSTOMER_ACT_DELETECUSTOMER', 'deleteCustomer');
define('CTCUSTOMER_ACT_ADDCONTACT', 'addContact');
define('CTCUSTOMER_ACT_DELETECONTACT', 'deleteContact');
define('CTCUSTOMER_ACT_ADDSITE', 'addSite');
define('CTCUSTOMER_ACT_DELETESITE', 'deleteSite');
define('CTCUSTOMER_ACT_ADDCUSTOMER', 'addCustomer');
define('CTCUSTOMER_ACT_DISP_SUCCESS', 'dispSuccess');
define('CTCUSTOMER_ACT_DISP_CUST_POPUP', 'dispCustPopup');
// Messages
define('CTCUSTOMER_MSG_CUSTTRING_REQ', 'Please enter search parameters');
define('CTCUSTOMER_MSG_NONE_FND', 'No customers found');
define('CTCUSTOMER_MSG_CUS_NOT_FND', 'Customer not found');
define('CTCUSTOMER_CLS_FORM_ERROR', 'formError');
define('CTCUSTOMER_CLS_TABLE_EDIT_HEADER', 'tableEditHeader');
define('CTCUSTOMER_CLS_FORM_ERROR_UC', 'formErrorUC');                // upper case
define('CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC', 'tableEditHeaderUC');
// Form text
define('CTCUSTOMER_TXT_ADD_SITE', 'Add site');
define('CTCUSTOMER_TXT_ADD_CONTACT', 'Add contact');


class CTCustomerCRM extends CTCNC
{

    const CTCUSTOMER_ACT_SEARCH_LEAD = 'searchLead';
    var $customerID = '';
    var $customerString = '';                      // Used when searching for an entity by string
    var $contactString = '';                      // Used when searching for an entity by string
    var $phoneString = '';                      // Used when searching for an entity by string
    var $newCustomerFromDate = '';                      // Used when searching for an entity by string
    var $newCustomerToDate = '';                      // Used when searching for an entity by string
    var $droppedCustomerFromDate = '';                      // Used when searching for an entity by string
    var $droppedCustomerToDate = '';                      // Used when searching for an entity by string
    var $address = '';                                // Used when searching for customer

    /**
     * @var BUCustomer $buCustomer
     *
     */
    var $buCustomer;
    var $customerStringMessage = '';
    /**
     * @var DBECustomer
     */
    var $dsCustomer;
    var $dsContact = '';
    var $dsSite = '';
    var $siteNo = '';
    var $contactID = '';
    var $dsHeader = '';

    var $orderTypeArray = array(
        "I" => "Initial",
        "Q" => "Quotation",
        "P" => "Part Despatched",
        "C" => "Completed",
        "B" => "Both Initial & Part Despatched",
        "R" => "Renewal Quote: Quick Quote Not Sent",
        "S" => "Renewal Quote: Awaiting Client Reply"
    );

    var $meetingFrequency = array(
        "3" => "Quarterly",
        "6" => "Six-monthly",
        "12" => "Annually"
    );

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buCustomer = new BUCustomer($this);
        $this->dsContact = new DataSet($this);
        $this->dsContact->copyColumnsFrom($this->buCustomer->dbeContact);
        $this->dsContact->addColumn('FirstNameClass', DA_STRING, DA_ALLOW_NULL);
        $this->dsContact->addColumn('LastNameClass', DA_STRING, DA_ALLOW_NULL);
        $this->dsSite = new DataSet($this);
        $this->dsSite->setIgnoreNULLOn();
        $this->dsSite->copyColumnsFrom($this->buCustomer->dbeSite);
        $this->dsSite->addColumn('Add1Class', DA_STRING, DA_ALLOW_NULL);
        $this->dsSite->addColumn('TownClass', DA_STRING, DA_ALLOW_NULL);
        $this->dsSite->addColumn('PostcodeClass', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer = new DataSet($this);
        $this->dsCustomer->setIgnoreNULLOn();
        $this->dsCustomer->copyColumnsFrom($this->buCustomer->dbeCustomer);
        $this->dsCustomer->addColumn('NameClass', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer->addColumn('InvoiceSiteMessage', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer->addColumn('DeliverSiteMessage', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer->addColumn('SectorMessage', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer->addColumn('specialAttentionEndDateMessage', DA_STRING, DA_ALLOW_NULL);
        $this->dsCustomer->addColumn('lastReviewMeetingDateMessage', DA_STRING, DA_ALLOW_NULL);
    }


//    /**
//     * Route to function based upon action passed
//     */
//    function defaultAction()
//    {
//        switch ($_REQUEST['action']) {
//            case 'edit':
//                $this->edit();
//                break;
//            case 'delete':
//                $this->delete();
//                break;
//            case 'generate':
//                $this->generate();
//                break;
//            case 'loadFromCsv':
//                $this->loadFromCsv();
//                break;
//
//            case 'list':
//                $this->displayList();
//                break;
//
//            case 'search':
//            default:
//                $this->search();
//                break;
//        }
//    }
//
    function search()
    {

        $this->setMethodName('search');

        $dsSearchForm = new DSForm($this);
        $dsSearchForm->addColumn('customerID', DA_STRING, DA_NOT_NULL);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $this->setCustomerID($dsSearchForm->getValue('customerID'));
                $link = $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'displayEditForm',
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

            $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));


            $this->setPageTitle('Customer CRM');

            if ($dsSearchForm->getValue('customerID')) {
                $buCustomer = new BUCustomer ($this);
                $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
                $customerString = $dsCustomer->getValue(DBECustomer::Name);
            }

            $urlCustomerPopup =
                $this->buildLink(
                    CTCNC_PAGE_CUSTOMER,
                    array(
                        'action' => CTCNC_ACT_DISP_CUST_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP)
                );

            $this->template->set_block('CustomerCRM', 'customerLeadStatusBlock', 'customerleadstatuses');
            /**
             * @var DataSet $dsCustomerLeadStatuses
             */
            $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);

            while ($dsCustomerLeadStatuses->fetchNext()) {
                $this->template->set_var(
                    array(
                        'customerLeadStatusID' => $dsCustomerLeadStatuses->getValue("customerLeadStatusID"),
                        'customerLeadStatusName' => $dsCustomerLeadStatuses->getValue("name"),
                        'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue('customerLeadStatusID') == $this->dsCustomer->getValue('CustomerLeadStatusID')) ? CT_SELECTED : ''
                    )
                );
                $this->template->parse('customerleadstatuses', 'customerLeadStatusBlock', true);
            }

            $linkURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'searchLead'
                    )
                );

            $this->template->set_var(
                array(
                    'formError' => $this->formError,
                    'customerID' => $dsSearchForm->getValue('customerID'),
                    'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
                    'customerString' => $customerString,
                    'urlCustomerPopup' => $urlCustomerPopup,
                    'urlSubmit' => $urlSubmit,
                    'ajaxURL' => $linkURL,
                )
            );

            $this->template->parse('CONTENTS', 'CustomerCRM', true);

            $this->parsePage();
        }


    } // end search

    function searchLead()
    {
        $customerLeadID = $_POST['customerLeadID'];
        // in the post we should find the id of the status we are searching for
        /** @var DBEContact $results */
        $results = $this->buCustomer->getMainContactsByLeadStatus($customerLeadID);
        $data = [];

        $customers = [];

        $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);
        $leadStatuses = [];
        while ($dsCustomerLeadStatuses->fetchNext()) {
            $leadStatuses[$dsCustomerLeadStatuses->getValue("customerLeadStatusID")] = $dsCustomerLeadStatuses->getValue("name");
        }

        while ($results->fetchNext()) {
            $customerID = $results->getValue(DBEContact::CustomerID);

            if (!isset($customers[$customerID])) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($results->getValue(DBEContact::CustomerID));

                $link = $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'displayEditForm',
                        'customerID' => $customerID
                    )
                );
                $customers[$customerID] = [
                    "customerName" => $dbeCustomer->getValue(DBECustomer::Name),
                    "customerLink" => $link,
                    "customerReviewDate" => $dbeCustomer->getValue(DBECustomer::reviewDate),
                    "bluestoneLeadStatus" => $leadStatuses[+$dbeCustomer->getValue(DBECustomer::CustomerLeadStatusID)]
                ];

            }

            $phone = $results->getValue(DBEContact::Phone);

            if (!$phone) {
                $site = new DBESite($this);
                $site->setValue(DBESite::CustomerID, $customerID);
                $site->setValue(DBESite::SiteNo, $results->getValue(DBEContact::SiteNo));
                $site->getRow();

                $phone = $site->getValue(DBESite::Phone);
            }

            $contactData = [
                "contactName" => $results->getValue(DBEContact::FirstName) . " " . $results->getValue(DBEContact::LastName),
                "jobTitle" => $results->getValue(DBEContact::Position),
                'contactPhone' => $phone
            ];
            $data[] = array_merge($contactData, $customers[$customerID]);


        }

        header('Content-Type: application/json;charset=utf-8');
        return json_encode($data);

    } // end search

    function initialProcesses()
    {
        $this->retrieveHTMLVars();
        parent::initialProcesses();
    }

    function setContact(&$contactArray)
    {
        if (!is_array($contactArray)) {          // For some reason the dynamically generated call to setContact from retrieveHTMLVars does not
            return;                                // pass a valid array so I avoid a crash like this! Same for setSite() below.
        }

        while (list($key, $value) = each($contactArray)) {
            $this->dsContact->setUpdateModeInsert();
            $this->dsContact->setValue('ContactID', $value['contactID']);
            $this->dsContact->setValue('CustomerID', $value['customerID']);
            $this->dsContact->setValue('SupplierID', $value['supplierID']);
            $this->dsContact->setValue('SiteNo', $value['siteNo']);
            $this->dsContact->setValue('Title', $value['title']);
            $this->dsContact->setValue('LastName', $value['lastName']);
            if ($this->dsContact->getValue('LastName') == '') {
                $this->setFormErrorOn();
                $this->dsContact->setValue('LastNameClass', CTCUSTOMER_CLS_FORM_ERROR);
            }
            $this->dsContact->setValue('FirstName', $value['firstName']);
            $this->dsContact->setValue('Email', $value['email']);
            $this->dsContact->setValue('Phone', $value['phone']);
            $this->dsContact->setValue('Notes', $value['notes']);
            $this->dsContact->setValue('MobilePhone', $value['mobilePhone']);
            $this->dsContact->setValue('Position', $value['position']);
            $this->dsContact->setValue('Fax', $value['fax']);
            $this->dsContact->setValue('PortalPassword', $value['portalPassword']);
            $this->dsContact->setValue('AccountsFlag', $this->getYN($value['accountsFlag']));
            $this->dsContact->setValue('DiscontinuedFlag', $value['discontinuedFlag']);
            $this->dsContact->setValue('SendMailshotFlag',
                                       $this->getYN($value['sendMailshotFlag']));// Use getYN() because HTML POST does not send a FALSE value
            $this->dsContact->setValue('Mailshot1Flag',
                                       $this->getYN($value['mailshot1Flag']));// Use getYN() because HTML POST does not send a FALSE value
            $this->dsContact->setValue('Mailshot2Flag', $this->getYN($value['mailshot2Flag']));
            $this->dsContact->setValue('Mailshot3Flag', $this->getYN($value['mailshot3Flag']));
            $this->dsContact->setValue('Mailshot4Flag', $this->getYN($value['mailshot4Flag']));
            $this->dsContact->setValue('Mailshot5Flag', $this->getYN($value['mailshot5Flag']));
            $this->dsContact->setValue('Mailshot6Flag', $this->getYN($value['mailshot6Flag']));
            $this->dsContact->setValue('Mailshot7Flag', $this->getYN($value['mailshot7Flag']));
            $this->dsContact->setValue('Mailshot8Flag', $this->getYN($value['mailshot8Flag']));
            $this->dsContact->setValue('Mailshot9Flag', $this->getYN($value['mailshot9Flag']));
            $this->dsContact->setValue('Mailshot10Flag', $this->getYN($value['mailshot10Flag']));
            $this->dsContact->setValue('WorkStartedEmailFlag', $this->getYN($value['workStartedEmailFlag']));
            $this->dsContact->setValue('AutoCloseEmailFlag', $this->getYN($value['autoCloseEmailFlag']));
            $this->dsContact->setValue('FailedLoginCount', $value['failedLoginCount']);


            if (
                $value['email'] == '' &&
                $value[CONFIG_HEADER_SUPPORT_CONTACT_FLAG] == 'Y'
            ) {
                $this->setFormErrorOn();
                $this->formErrorMessage = 'NOT SAVED: Email address required for support contacts';
            }
            // Determine whether a new contact is to be added
            if ($this->dsContact->getValue('ContactID') == 0) {
                if (
                    ($this->dsContact->getValue('Title') != '') |
                    ($this->dsContact->getValue('FirstName') != '') |
                    ($this->dsContact->getValue('LastName') != '')
                ) {
                    $this->dsContact->post();
                }
            } else {
                $this->dsContact->post();  // Existing contact
            }
        }
    }

    function setSite(&$siteArray)
    {
        if (!is_array($siteArray)) {
            return;
        }
        while (list($key, $value) = each($siteArray)) {
            $this->dsSite->setUpdateModeInsert();
            $this->dsSite->setValue('Add1Class', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsSite->setValue('TownClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
            $this->dsSite->setValue('PostcodeClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
            $this->dsSite->setValue('CustomerID', $value['customerID']);
            $this->dsSite->setValue('SiteNo', $value['siteNo']);
            $this->dsSite->setValue('Add1', $value['add1']);
            if ($this->dsSite->getValue('Add1') == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue('Add1Class', CTCUSTOMER_CLS_FORM_ERROR);
            }
            $this->dsSite->setValue('Add2', $value['add2']);
            $this->dsSite->setValue('Add3', $value['add3']);
            $this->dsSite->setValue('Town', strtoupper($value['town']));
            if ($this->dsSite->getValue('Town') == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue('TownClass', CTCUSTOMER_CLS_FORM_ERROR_UC);
            }
            $this->dsSite->setValue('County', $value['county']);
            $this->dsSite->setValue('Postcode', strtoupper($value['postcode']));
            if ($this->dsSite->getValue('Postcode') == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue('PostcodeClass', CTCUSTOMER_CLS_FORM_ERROR_UC);
            }
            $this->dsSite->setValue('Phone', $value['sitePhone']);
            $this->dsSite->setValue('MaxTravelHours', $value['maxTravelHours']);
            $this->dsSite->setValue('InvoiceContactID', $value['invoiceContactID']);
            $this->dsSite->setValue('DeliverContactID', $value['deliverContactID']);
            $this->dsSite->setValue('SageRef', $value['sageRef']);
            $this->dsSite->setValue('DebtorCode', $value['debtorCode']);
            $this->dsSite->setValue('ActiveFlag', $this->getYN($value['activeFlag']));
            $this->dsSite->post();
        }
    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar('customerID', $customerID);
    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    function setSiteNo($siteNo)
    {
        $this->setNumericVar('siteNo', $siteNo);
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

    function setContactID($contactID)
    {
        $this->setNumericVar('contactID', $contactID);
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
            $this->dsCustomer->setUpdateModeInsert();
            $this->dsCustomer->setValue('NameClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsCustomer->setValue('InvoiceSiteMessage', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsCustomer->setValue('DeliverSiteMessage', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsCustomer->setValue('CustomerID', $value['customerID']);
            $this->dsCustomer->setValue('Name', $value['name']);
            if ($this->dsCustomer->getValue('Name') == '') {
                $this->setFormErrorOn();
                $this->dsCustomer->setValue('NameClass', CTCUSTOMER_CLS_FORM_ERROR);
            }
            $this->dsCustomer->setValue('MailshotFlag', $this->getYN($value['mailshotFlag']));
            $this->dsCustomer->setValue('reviewDate', $this->convertDateYMD($value['reviewDate']));
            $this->dsCustomer->setValue('reviewMeetingEmailSentFlag',
                                        $this->getYN($value['reviewMeetingEmailSentFlag']));
            $this->dsCustomer->setValue('reviewAction', $value['reviewAction']);
            $this->dsCustomer->setValue('reviewUserID', $value['reviewUserID']);
            $this->dsCustomer->setValue('reviewTime', $value['reviewTime']);
            $this->dsCustomer->setValue('noOfServers', $value['noOfServers']);
            $this->dsCustomer->setValue('noOfPCs', $value['noOfPCs']);
            $this->dsCustomer->setValue('noOfSites', $value['noOfSites']);
            $this->dsCustomer->setValue('comments', $value['comments']);
            $this->dsCustomer->setValue('techNotes', $value['techNotes']);
            $this->dsCustomer->setValue(DBECustomer::CustomerLeadStatusID, $value['customerLeadStatusID']);
            $this->dsCustomer->setValue(DBECustomer::DateMeetingConfirmed, $value['dateMeetingConfirmedDate']);
            $this->dsCustomer->setValue(DBECustomer::MeetingDateTime, $value['meetingDateTime']);
            $this->dsCustomer->setValue(DBECustomer::InviteSent, $this->getTrueFalse($value[DBECustomer::InviteSent]));
            $this->dsCustomer->setValue(DBECustomer::ReportProcessed,
                                        $this->getTrueFalse($value[DBECustomer::ReportProcessed]));
            $this->dsCustomer->setValue(DBECustomer::ReportSent, $this->getTrueFalse($value[DBECustomer::ReportSent]));
            $this->dsCustomer->setValue(DBECustomer::CrmComments, $value[DBECustomer::CrmComments]);
            $this->dsCustomer->setValue(DBECustomer::CompanyBackground, $value[DBECustomer::CompanyBackground]);
            $this->dsCustomer->setValue(DBECustomer::DecisionMakerBackground,
                                        $value[DBECustomer::DecisionMakerBackground]);
            $this->dsCustomer->setValue(DBECustomer::OpportunityDeal, $value[DBECustomer::OpportunityDeal]);
            $this->dsCustomer->setValue(DBECustomer::Rating, $value[DBECustomer::Rating]);
            $this->dsCustomer->post();
        }
    }

    function setCustomerString($customerString)
    {
        $this->customerString = $customerString;
    }

    function getCustomerString()
    {
        return $this->customerString;
    }

    function setContactString($contactString)
    {
        $this->contactString = $contactString;
    }

    function getContactString()
    {
        return $this->contactString;
    }

    function setPhoneString($phoneString)
    {
        $this->phoneString = $phoneString;
    }

    function getPhoneString()
    {
        return $this->phoneString;
    }

    function setAddress($address)
    {
        $this->address = $address;
    }

    function getAddress()
    {
        return $this->address;
    }

    function setCustomerStringMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->customerStringMessage = $message;
    }

    function getCustomerStringMessage()
    {
        return $this->customerStringMessage;
    }

    function setNewCustomerFromDate($newCustomerFromDate)
    {
        $this->newCustomerFromDate = $newCustomerFromDate;
    }

    function getNewCustomerFromDate()
    {
        return $this->newCustomerFromDate;
    }

    function setNewCustomerToDate($newCustomerToDate)
    {
        $this->newCustomerToDate = $newCustomerToDate;
    }

    function getNewCustomerToDate()
    {
        return $this->newCustomerToDate;
    }

    function setReviewMeetingFrequencyMonths($reviewMeetingFrequencyMonths)
    {
        $this->reviewMeetingFrequencyMonths = $reviewMeetingFrequencyMonths;
    }

    function getReviewMeetingFrequencyMonths()
    {
        return $this->reviewMeetingFrequencyMonths;
    }

    function setLastReviewMeetingDate($lastReviewMeetingDate)
    {
        $this->lastReviewMeetingDate = $lastReviewMeetingDate;
    }

    function getLastReviewMeetingDate()
    {
        return $this->lastReviewMeetingDate;
    }

    function setReviewMeetingEmailSentFlag($value)
    {
        $this->reviewMeetingEmailSentFlag = $value;
    }

    function getReviewMeetingEmailSentFlag()
    {
        return $this->reviewMeetingEmailSentFlag;
    }

    function setDroppedCustomerFromDate($droppedCustomerFromDate)
    {
        $this->droppedCustomerFromDate = $droppedCustomerFromDate;
    }

    function getDroppedCustomerFromDate()
    {
        return $this->droppedCustomerFromDate;
    }

    function setDroppedCustomerToDate($droppedCustomerToDate)
    {
        $this->droppedCustomerToDate = $droppedCustomerToDate;
    }

    function getDroppedCustomerToDate()
    {
        return $this->droppedCustomerToDate;
    }

    function getYN($flag)
    {
        return ($flag == 'Y' ? $flag : 'N');
    }

    function getChecked($flag)
    {
        return ($flag == 'N' || $flag == false ? '' : CT_CHECKED);
    }

    function convertDateYMD($dateDMY)
    {
        if ($dateDMY != '') {
            $dateArray = explode('/', $dateDMY);
            return ($dateArray[2] . '-' . str_pad($dateArray[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateArray[0],
                                                                                                       2,
                                                                                                       '0',
                                                                                                       STR_PAD_LEFT));
        } else {
            return '';
        }
    }

    function getOrderTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
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
            case 'uploadPortalDocument':
                $this->uploadPortalDocument();
                break;
            case 'display24HourSupportCustomers':
                $this->display24HourSupportCustomers();
                break;
            case 'displaySpecialAttentionCustomers':
                $this->displaySpecialAttentionCustomers();
                break;
            default:
//                $this->displaySearchForm();
                $this->search();
                break;
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['parentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['parentDescField'] = $_REQUEST['parentDescField'];
        }
    }

    /**
     * when customer folder link is clicked we call this routine which first checks
     * to see whether the folder exists. If not, it is created.
     * @access private
     */
    function createCustomerFolder()
    {
        $this->setMethodName('createCustomerFolder');

        if ($this->getCustomerID() == '') {
            $this->displayFatalError('CustomerID not passed');
        }

        $this->buCustomer->createCustomerFolder($this->getCustomerID());
        $nextURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISP_EDIT,
                    'customerID' => $this->getCustomerID()
                )
            );
        header('Location: ' . $nextURL);
        exit;

    }

    /**
     * Displays next prospect to review (if any)
     *
     */
    function displayNextReviewProspect()
    {
        $this->setMethodName('displayNextReviewProspect');

        if ($this->buCustomer->getNextReviewProspect($dsCustomer)) {

            $nextURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCNC_ACT_DISP_EDIT,
                        'customerID' => $dsCustomer->getValue(DBECustomer::CustomerID)
                    )
                );
            header('Location: ' . $nextURL);

        } else {
            echo "There are no more prospects to review - well done!";
        }

        exit;


    }

    /**
     * Displays list of customers to review
     *
     */
    function displayReviewList()
    {
        $this->setMethodName('displayReviewList');

        $this->setTemplateFiles('CustomerReviewList', 'CustomerReviewList.inc');

        $this->setPageTitle("My Daily Call List");

        $this->template->set_block('CustomerReviewList', 'reviewBlock', 'reviews');


        if ($this->buCustomer->getDailyCallList($dsCustomer)) {

            $buUser = new BUUser($this);

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue(DBECustomer::CustomerID)
                        )
                    );

                if ($dsCustomer->getValue(DBECustomer::ReviewUserID)) {
                    $buUser->getUserByID($dsCustomer->getValue(DBECustomer::ReviewUserID), $dsUser);
                    $user = $dsUser->getValue('name');
                } else {
                    $user = false;
                }

                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::Name),
                        'reviewDate' => $dsCustomer->getValue(DBECustomer::ReviewDate),
                        'reviewTime' => $dsCustomer->getValue(DBECustomer::ReviewTime),
                        'reviewAction' => $dsCustomer->getValue(DBECustomer::ReviewAction),
                        'reviewUser' => $user,
                        'linkURL' => $linkURL
                    )
                );

                $this->template->parse('reviews', 'reviewBlock', true);

            }

            $this->template->parse('CONTENTS', 'CustomerReviewList', true);

        } else {

            echo "There are no customers to be reviewed";
        }
        $this->parsePage();

        exit;


    }

    /**
     * Displays list of customers with 24 Hour Support
     *
     */
    function display24HourSupportCustomers()
    {
        $this->setMethodName('display24HourSupportCustomers');

        $this->setPageTitle("24 Hour Support Customers");

        if ($this->buCustomer->get24HourSupportCustomers($dsCustomer)) {

            $this->setTemplateFiles('Customer24HourSupport', 'Customer24HourSupport.inc');

            $this->template->set_block('Customer24HourSupport', 'customerBlock', 'customers');

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::CustomerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::Name),
                        'linkURL' => $linkURL
                    )
                );

                $this->template->parse('customers', 'customerBlock', true);

            }

            $this->template->parse('CONTENTS', 'Customer24HourSupport', true);

        } else {

            $this->setTemplateFiles('SimpleMessage', 'SimpleMessage.inc');
            $this->template->set_var('message', 'There are no 24 Hour Support customers');
        }

        $this->parsePage();

        exit;
    }

    /**
     * Displays list of customers with Special Attention flag set
     *
     */
    function displaySpecialAttentionCustomers()
    {
        $this->setMethodName('displaySpecialAttentionCustomers');

        $this->setPageTitle("Special Attention Customers");


        if ($this->buCustomer->getSpecialAttentionCustomers($dsCustomer)) {


            $this->setTemplateFiles('CustomerSpecialAttention', 'CustomerSpecialAttention.inc');

            $this->template->set_block('CustomerSpecialAttention', 'customerBlock', 'customers');

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::CustomerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::Name),
                        'specialAttentionEndDate' => $dsCustomer->getValue(DBECustomer::SpecialAttentionEndDate),
                        'linkURL' => $linkURL
                    )
                );

                $this->template->parse('customers', 'customerBlock', true);

            }

            $this->template->parse('CONTENTS', 'CustomerSpecialAttention', true);

        } else {

            $this->setTemplateFiles('SimpleMessage', 'SimpleMessage.inc');

            $this->template->set_var(array('message' => 'There are no special attention customers'));

            $this->template->parse('CONTENTS', 'SimpleMessage', true);
        }

        $this->parsePage();

        exit;
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles('CustomerSearch', 'CustomerSearch.inc');
// Parameters
        $this->setPageTitle("Customer");
        $submitURL = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCUSTOMER_ACT_SEARCH));
        $createURL = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCUSTOMER_ACT_ADDCUSTOMER));
        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->template->set_var(
            array(
                'contactString' => $this->getContactString(),
                'phoneString' => $this->getPhoneString(),
                'customerString' => $this->getCustomerString(),
                'address' => $this->getAddress(),
                'customerStringMessage' => $this->getCustomerStringMessage(),
                'newCustomerFromDate' => $this->getNewCustomerFromDate(),
                'newCustomerToDate' => $this->getNewCustomerToDate(),
                'droppedCustomerFromDate' => $this->getDroppedCustomerFromDate(),
                'droppedCustomerToDate' => $this->getDroppedCustomerToDate(),
                'submitURL' => $submitURL,
                'createURL' => $createURL,
                'customerPopupURL' => $customerPopupURL,
            )
        );
        if (is_object($this->dsCustomer)) {
            $this->template->set_block('CustomerSearch', 'customerBlock', 'customers');
            while ($this->dsCustomer->fetchNext()) {
                $customerURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCNC_ACT_DISP_EDIT,
                            'customerID' => $this->dsCustomer->getValue("CustomerID")
                        )
                    );

                $this->template->set_var(
                    array(
                        'customerName' => $this->dsCustomer->getValue("Name"),
                        'customerURL' => $customerURL
                    )
                );
                $this->template->parse('customers', 'customerBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'CustomerSearch', true);
        $this->parsePage();
    }

//    /**
//     * Search for customers usng customerString
//     * @access private
//     */
//    function search()
//    {
//        $this->setMethodName('search');
//        // Parameter validation
//        if (!$this->buCustomer->getCustomersByNameMatch(
//            $this->getContactString(),
//            $this->getPhoneString(),
//            $this->getCustomerString(),
//            $this->getAddress(),
//            $this->convertDateYMD($this->getNewCustomerFromDate()),
//            $this->convertDateYMD($this->getNewCustomerToDate()),
//            $this->convertDateYMD($this->getDroppedCustomerFromDate()),
//            $this->convertDateYMD($this->getDroppedCustomerToDate()),
//            $this->dsCustomer)
//        ) {
//            $this->setCustomerStringMessage(CTCUSTOMER_MSG_NONE_FND);
//        }
//        if (($this->formError) || ($this->dsCustomer->rowCount() > 1)) {
//            $this->displaySearchForm();
//        } else {
//            // reload with this customer
//            $nextURL =
//                $this->buildLink(
//                    $_SERVER['PHP_SELF'],
//                    array(
//                        'action' => CTCNC_ACT_DISP_EDIT,
//                        'customerID' => $this->dsCustomer->getValue('CustomerID')
//                    )
//                );
//            header('Location: ' . $nextURL);
//            exit;
//
//        }
//    }

    /**
     * Form for editing customer details
     * @access private
     */
    function displayEditForm()
    {
        $this->setMethodName('displayEditForm');
        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {
            if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_DISP_SUCCESS)) {   // Not displaying form error page so get customer record
                if (!$this->buCustomer->getCustomerByID($this->getCustomerID(), $this->dsCustomer)) {
                    $this->displayFatalError(CTCUSTOMER_MSG_CUS_NOT_FND);
                }
            }
            $this->dsCustomer->fetchNext();

            // If we can delete this customer set the link
            if ($this->buCustomer->canDeleteCustomer($this->getCustomerID(), $this->userID)) {
                $deleteCustomerURL = $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCUSTOMER_ACT_DELETECUSTOMER,
                        'customerID' => $this->getCustomerID()
                    )
                );
                $deleteCustomerText = 'Delete Customer';
            }
        } else {

            $this->dsCustomer->clearRows();      // Creating a new customer - creates new row on dataset, NOT on the database yet
            $this->dsSite->clearRows();
            $this->dsContact->clearRows();
            $this->buCustomer->addNewCustomerRow($this->dsCustomer);
        }
        $this->setTemplateFiles('CustomerEdit', 'CustomerCRM.inc');

// Parameters
        if ($_REQUEST['save_page']) {
            $_SESSION['save_page'] = $_REQUEST['save_page'];
        } else {
            $_SESSION['save_page'] = false;
        }
        $submitURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMER_ACT_UPDATE
                )
            );

        if ($_SESSION['save_page']) {
            $cancelURL = $_SESSION['save_page'];
        } else {
            $cancelURL = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCUSTOMER_ACT_DISP_SEARCH));
        }
        $addSiteURL =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'action' => CTCUSTOMER_ACT_ADDSITE,
                                 'customerID' => $this->getCustomerID(),
                             )
            );
        if (!$this->formError) {              // Not displaying form error page so get customer record
            $this->dsCustomer->setValue('NameClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsCustomer->setValue('InvoiceSiteMessage', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
            $this->dsCustomer->setValue('DeliverSiteMessage', CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
        }

        $this->setPageTitle("Customer - " . $this->dsCustomer->getValue('Name'));
        /*
        Get the list of custom letter template file names from the custom letter directory
        */
        $dir = LETTER_TEMPLATE_DIR . "/custom/";

        if (is_dir($dir)) {

            $dh = opendir($dir);

            while (false !== ($filename = readdir($dh))) {

                $ext = explode('.', $filename);
                $ext = $ext[count($ext) - 1];

                if ($ext == 'htm') {
                    $customLetterTemplates[] = $filename;
                }
            }
        } else {
            $customLetterTemplates = false;
        }

        if ($customerFolderPath = $this->buCustomer->customerFolderExists($this->dsCustomer->getValue('CustomerID'))) {
            $customerFolderLink =
                '<a href="file:' . $customerFolderPath . '" target="_blank" title="Open Folder">Open Folder</a>';
        } else {
            $urlCreateCustomerFolder =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => 'createCustomerFolder',
                                     'customerID' => $this->getCustomerID(),
                                 )
                );
            $customerFolderLink =
                '<a href="http:' . $urlCreateCustomerFolder . '" title="Create Folder">Create Customer Folder</a>';
        }
        $renewalLinkURL =
            $this->buildLink(
                'RenewalReport.php',
                array(
                    'action' => 'produceReport',
                    'customerID' => $this->getCustomerID()
                )
            );


        $renewalLink = '<a href="' . $renewalLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';

        $passwordLinkURL =
            $this->buildLink(
                'Password.php',
                array(
                    'action' => 'list',
                    'customerID' => $this->getCustomerID()
                )
            );


        $passwordLink = '<a href="' . $passwordLinkURL . '" target="_blank" title="Passwords">Service Passwords</a>';

        $showInactiveContactsURL =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'action' => 'dispEdit',
                                 'customerID' => $this->getCustomerID(),
                                 'showInactiveContacts' => '1'
                             )
            );
        $showInactiveSitesURL =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'action' => 'dispEdit',
                                 'customerID' => $this->getCustomerID(),
                                 'showInactiveSites' => '1'
                             )
            );
        $bodyTagExtras = 'onLoad="loadNote(\'last\')"';

        $urlContactPopup =
            $this->buildLink(
                CTCNC_PAGE_CONTACT,
                array(
//          'action' => CTCNC_ACT_CONTACT_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $site = new DBESite($this);
        $site->setValue('SiteNo', $this->dsCustomer->getValue('DeliverSiteNo'));
        $site->setValue('CustomerID', $this->getCustomerID());
        $site->getRowByCustomerIDSiteNo();

        $this->template->set_var(
            array(
                'urlContactPopup' => $urlContactPopup,
                'bodyTagExtras' => $bodyTagExtras,
                /* hidden */
                'reviewMeetingEmailSentFlag' => $this->dsCustomer->getValue('reviewMeetingEmailSentFlag'),
                'customerNotePopupLink' => $this->getCustomerNotePopupLink($this->getCustomerID()),
                'showInactiveContactsURL' => $showInactiveContactsURL,
                'showInactiveSitesURL' => $showInactiveSitesURL,
                'customerID' => $this->dsCustomer->getValue('CustomerID'),
                'customerName' => $this->dsCustomer->getValue('Name'),
                'reviewCount' => $this->buCustomer->getReviewCount(),
                'customerFolderLink' => $customerFolderLink,
                'customerNameClass' => $this->dsCustomer->getValue('NameClass'),
                'SectorMessage' => $this->dsCustomer->getValue('SectorMessage'),
                'regNo' => $this->dsCustomer->getValue('RegNo'),
                'mailshotFlagChecked' => $this->getChecked($this->dsCustomer->getValue('MailshotFlag')),
                'referredFlagChecked' => $this->getChecked($this->dsCustomer->getValue('ReferredFlag')),
                'specialAttentionFlagChecked' => $this->getChecked($this->dsCustomer->getValue('specialAttentionFlag')),
                'specialAttentionEndDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('specialAttentionEndDate')),
                'specialAttentionEndDateMessage' => $this->dsCustomer->getValue('specialAttentionEndDateMessage'),
                'lastReviewMeetingDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('lastReviewMeetingDate')),
                'dateMeetingConfirmedDate' => $this->dsCustomer->getValue(DBECustomer::DateMeetingConfirmed),
                'meetingDateTime' => Controller::dateToISO($this->dsCustomer->getValue(DBECustomer::MeetingDateTime)),
                DBECustomer::InviteSent => $this->getChecked($this->dsCustomer->getValue(DBECustomer::InviteSent)),
                DBECustomer::ReportProcessed => $this->getChecked($this->dsCustomer->getValue(DBECustomer::ReportProcessed)),
                DBECustomer::ReportSent => $this->getChecked($this->dsCustomer->getValue(DBECustomer::ReportSent)),
                DBECustomer::CrmComments => $this->dsCustomer->getValue(DBECustomer::CrmComments),
                DBECustomer::CompanyBackground => $this->dsCustomer->getValue(DBECustomer::CompanyBackground),
                DBECustomer::DecisionMakerBackground => $this->dsCustomer->getValue(DBECustomer::DecisionMakerBackground),
                DBECustomer::OpportunityDeal => $this->dsCustomer->getValue(DBECustomer::OpportunityDeal),
                DBECustomer::Rating => $this->dsCustomer->getValue(DBECustomer::Rating),
                'lastReviewMeetingDateMessage' => $this->dsCustomer->getValue('lastReviewMeetingDateMessage'),
                'support24HourFlagChecked' => $this->getChecked($this->dsCustomer->getValue('support24HourFlag')),
                'prospectFlagChecked' => $this->getChecked($this->dsCustomer->getValue('ProspectFlag')),
                'othersEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue('OthersEmailMainFlag')),
                'workStartedEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue('WorkStartedEmailMainFlag')),
                'autoCloseEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue('AutoCloseEmailMainFlag')),
                'pcxFlagChecked' => $this->getChecked($this->dsCustomer->getValue('PCXFlag')),
                'createDate' => $this->dsCustomer->getValue("CreateDate"),
                'mailshot1FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot1FlagDesc"),
                'mailshot2FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot2FlagDesc"),
                'mailshot3FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot3FlagDesc"),
                'mailshot4FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot4FlagDesc"),
                'mailshot5FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot5FlagDesc"),
                'mailshot6FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot6FlagDesc"),
                'mailshot7FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot7FlagDesc"),
                'mailshot8FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot8FlagDesc"),
                'mailshot9FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot9FlagDesc"),
                'mailshot10FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot10FlagDesc"),
                'submitURL' => $submitURL,
                'renewalLink' => $renewalLink,
                'passwordLink' => $passwordLink,
                'deleteCustomerURL' => $deleteCustomerURL,
                'deleteCustomerText' => $deleteCustomerText,
                'cancelURL' => $cancelURL,
                'disabled' => $this->hasPermissions(PHPLIB_PERM_SALES) ? '' : CTCNC_HTML_DISABLED,
                'gscTopUpAmount' => $this->dsCustomer->getValue('GSCTopUpAmount'),
                'noOfServers' => $this->dsCustomer->getValue('noOfServers'),
                'noOfSites' => $this->dsCustomer->getValue('noOfSites'),
                'modifyDate' => $this->dsCustomer->getValue('modifyDate'),
                'reviewDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('reviewDate')),
                'reviewTime' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('reviewTime')),
                'becameCustomerDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('becameCustomerDate')),
                'droppedCustomerDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('droppedCustomerDate')),
                'reviewAction' => Controller::dateYMDtoDMY($this->dsCustomer->getValue('reviewAction')),
                'comments' => $this->dsCustomer->getValue('comments'),
                'techNotes' => $this->dsCustomer->getValue('techNotes'),
                'slaP1' => $this->dsCustomer->getValue('slaP1'),
                'slaP2' => $this->dsCustomer->getValue('slaP2'),
                'slaP3' => $this->dsCustomer->getValue('slaP3'),
                'slaP4' => $this->dsCustomer->getValue('slaP4'),
                'slaP5' => $this->dsCustomer->getValue('slaP5'),

                'add1' => $site->getValue("Add1"),
                'add2' => $site->getValue("Add2"),
                'add3' => $site->getValue("Add3"),
//                'townClass' => $site->getValue('TownClass'),
                'town' => $site->getValue("Town"),
                'county' => $site->getValue("County"),
//                'postcodeClass' => $site->getValue('PostcodeClass'),
                'postcode' => $site->getValue("Postcode"),
                'sitePhone' => $site->getValue(DBESite::Phone),
                'siteNo' => $site->getValue("SiteNo"),
                'sageRef' => $site->getValue("SageRef"),
                'debtorCode' => $site->getValue("DebtorCode"),
                'maxTravelHours' => $site->getValue("MaxTravelHours"),

                'invoiceSiteFlagChecked' => ($this->dsCustomer->getValue('InvoiceSiteNo') == $site->getValue('SiteNo')) ? CT_CHECKED : '',
                'deliverSiteFlagChecked' => ($this->dsCustomer->getValue('DeliverSiteNo') == $site->getValue('SiteNo')) ? CT_CHECKED : '',
                'activeFlagChecked' => ($site->getValue('ActiveFlag') == 'Y') ? CT_CHECKED : '',
                'deleteSiteText' => $deleteSiteText,
                'deleteSiteURL' => $deleteSiteURL
            )
        );
        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER)) {                                                      // Only get from DB if not displaying form error(s)
            $this->template->set_var(
                array(
                    'addSiteText' => CTCUSTOMER_TXT_ADD_SITE,
                    'addSiteURL' => $addSiteURL
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

        $this->template->set_block('CustomerEdit', 'noOfPCsBlock', 'noOfPCs');
        foreach ($noOfPCs as $index => $value) {
            $this->template->set_var(
                array(
                    'noOfPCsValue' => $value,
                    'noOfPCsSelected' => $value == $this->dsCustomer->getValue('noOfPCs') ? CT_SELECTED : ''
                )
            );
            $this->template->parse('noOfPCs', 'noOfPCsBlock', true);
        }

        $this->template->set_block('CustomerEdit', 'customerTypeBlock', 'customertypes');
        $this->buCustomer->getCustomerTypes($dsCustomerType);
        while ($dsCustomerType->fetchNext()) {
            $this->template->set_var(
                array(
                    'customerTypeID' => $dsCustomerType->getValue("customerTypeID"),
                    'customerTypeDescription' => $dsCustomerType->getValue("description"),
                    'customerTypeSelected' => ($dsCustomerType->getValue('customerTypeID') == $this->dsCustomer->getValue('CustomerTypeID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('customertypes', 'customerTypeBlock', true);
        }

        $this->template->set_block('CustomerEdit', 'reviewFrequencyBlock', 'reviewFrequencies');
        foreach ($this->meetingFrequency as $index => $value) {
            $this->template->set_var(
                array(
                    'reviewMeetingFrequencyMonths' => $index,
                    'reviewMeetingFrequencyMonthsDescription' => $value,
                    'reviewMeetingFrequencyMonthsSelected' => $index == $this->dsCustomer->getValue('reviewMeetingFrequencyMonths') ? CT_SELECTED : ''
                )
            );
            $this->template->parse('reviewFrequencies', 'reviewFrequencyBlock', true);
        }

        $this->template->set_block('CustomerEdit', 'customerLeadStatusBlock', 'customerleadstatuses');
        /**
         * @var DataSet $dsCustomerLeadStatuses
         */
        $this->buCustomer->getCustomerLeadStatuses($dsCustomerLeadStatuses);

        while ($dsCustomerLeadStatuses->fetchNext()) {

            $this->template->set_var(
                array(
                    'customerLeadStatusID' => $dsCustomerLeadStatuses->getValue("customerLeadStatusID"),
                    'customerLeadStatusName' => $dsCustomerLeadStatuses->getValue("name"),
                    'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue('customerLeadStatusID') == $this->dsCustomer->getValue('CustomerLeadStatusID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('customerleadstatuses', 'customerLeadStatusBlock', true);
        }

        $buSector = new BUSector($this);
        $this->template->set_block('CustomerEdit', 'sectorBlock', 'sectors');
        $buSector->getAll($dsSector);
        while ($dsSector->fetchNext()) {
            $this->template->set_var(
                array(
                    'sectorID' => $dsSector->getValue("sectorID"),
                    'sectorDescription' => $dsSector->getValue("description"),
                    'sectorSelected' => ($dsSector->getValue('sectorID') == $this->dsCustomer->getValue('sectorID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('sectors', 'sectorBlock', true);
        }
        $this->template->set_block('CustomerEdit', 'leadStatusBlock', 'leadStatus');
        $this->buCustomer->getLeadStatus($dsLeadStatus);
        while ($dsLeadStatus->fetchNext()) {

            $this->template->set_var(
                array(
                    'leadStatusID' => $dsLeadStatus->getValue("leadStatusID"),
                    'leadStatusDescription' => $dsLeadStatus->getValue("description"),
                    'leadStatusSelected' => ($dsLeadStatus->getValue('leadStatusID') == $this->dsCustomer->getValue('leadStatusID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('leadStatus', 'leadStatusBlock', true);
        }
        /*
        Review users
        */
        $this->template->set_block('CustomerEdit', 'reviewUserBlock', 'reviewUsers');

        $buUser = new BUUser($this);
        $buUser->getAllUsers($dsUser);

        while ($dsUser->fetchNext()) {

            $this->template->set_var(
                array(
                    'reviewUserID' => $dsUser->getValue("userID"),
                    'reviewUserName' => $dsUser->getValue("name"),
                    'reviewUserSelected' => ($dsUser->getValue('userID') == $this->dsCustomer->getValue('reviewUserID')) ? CT_SELECTED : ''
                )
            );

            $this->template->parse('reviewUsers', 'reviewUserBlock', true);
        }

        /*
        Account Manager users
        */
        $this->template->set_block('CustomerEdit', 'accountManagerBlock', 'accountManagers');

        $buUser = new BUUser($this);
        $buUser->getAllUsers($dsUser);

        while ($dsUser->fetchNext()) {

            $this->template->set_var(
                array(
                    'accountManagerUserID' => $dsUser->getValue("userID"),
                    'accountManagerUserName' => $dsUser->getValue("name"),
                    'accountManagerUserSelected' => ($dsUser->getValue('userID') == $this->dsCustomer->getValue('accountManagerUserID')) ? CT_SELECTED : ''
                )
            );

            $this->template->parse('accountManagers', 'accountManagerBlock', true);
        }

        /*
        Projects
        */
        $addProjectURL =
            $this->buildLink(
                'Project.php',
                array(
                    'action' => 'add',
                    'customerID' => $this->getCustomerID()
                )
            );

        $this->template->set_var(
            array(
                'addProjectText' => 'Add project',
                'addProjectURL' => $addProjectURL
            )
        );

        $this->template->set_block('CustomerEdit',
                                   'projectBlock',
                                   'projects');      // have to declare innermost block first

        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $buProject = new BUProject($this);
            $buProject->getProjectsByCustomerID($this->getCustomerID(), $dsProject);

            while ($dsProject->fetchNext()) {
                if ($buProject->canDelete($dsProject->getValue('projectID'))) {
                    $deleteProjectLink =
                        $this->buildLink('Project.php',
                                         array(
                                             'action' => 'delete',
                                             'projectID' => $dsProject->getValue('projectID')
                                         )
                        );
                    $deleteProjectText = 'delete';
                } else {
                    $deleteProjectLink = '';
                    $deleteProjectText = '';
                }

                $editProjectLink =
                    $this->buildLink('Project.php',
                                     array(
                                         'action' => 'edit',
                                         'projectID' => $dsProject->getValue('projectID')
                                     )
                    );

                $this->template->set_var(
                    array(
                        'projectID' => $dsProject->getValue('projectID'),
                        'projectName' => $dsProject->getValue('description'),
                        'notes' => substr($dsProject->getValue('notes'), 0, 50),
                        'startDate' => strftime("%d/%m/%Y", strtotime($dsProject->getValue('startDate'))),
                        'expiryDate' => strftime("%d/%m/%Y", strtotime($dsProject->getValue('expiryDate'))),
                        'editProjectLink' => $editProjectLink,
                        'deleteProjectLink' => $deleteProjectLink,
                        'deleteProjectText' => $deleteProjectText
                    )
                );

                $this->template->parse('projects', 'projectBlock', true);

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


        $this->template->set_block('CustomerEdit', 'siteBlock', 'sites');


        $this->template->set_block('CustomerEdit', 'customLetterBlock', 'customLetters');      //

        $this->template->set_block('CustomerEdit', 'selectSiteBlock', 'selectSites');

        $this->template->set_block('CustomerEdit',
                                   'contactBlock',
                                   'contacts');      // have to declare innermost block first

        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) & ($this->getAction() != CTCUSTOMER_ACT_DISP_SUCCESS)) {                                                      // Only get from DB if not displaying form error(s)
            $this->buCustomer->getSitesByCustomerID($this->dsCustomer->getValue('CustomerID'),
                                                    $this->dsSite,
                                                    $_REQUEST['showInactiveSites']);

            $this->buCustomer->getContactsByCustomerID($this->dsCustomer->getValue('CustomerID'),
                                                       $this->dsContact,
                                                       $_REQUEST['showInactiveContacts']);

            if ($this->getAction() == CTCUSTOMER_ACT_ADDCONTACT) {
                $this->buCustomer->addNewContactRow($this->dsContact, $this->getCustomerID(), $this->getSiteNo());
            }

            if ($this->getAction() == CTCUSTOMER_ACT_ADDSITE) {
                $this->buCustomer->addNewSiteRow($this->dsSite, $this->getCustomerID());
            }
        }

        $this->dsSite->initialise();


        if (!$this->formError) {// Only get from DB if not displaying form error(s)
            $thing = CTCUSTOMER_CLS_TABLE_EDIT_HEADER;
//            $site->setValue('Add1Class',);
//            $site->setValue('TownClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
//            $site->setValue('PostcodeClass', CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
        }

        //      $this->template->set_block('CustomerEdit','contacts', '');
        $addContactURL =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'action' => CTCUSTOMER_ACT_ADDCONTACT,
                                 'customerID' => $site->getValue("CustomerID"),
                                 'siteNo' => $site->getValue("SiteNo")
                             )
            );
        // If we can delete this site set the link
        if ($this->buCustomer->canDeleteSite($site->getValue("CustomerID"), $site->getValue("SiteNo"))) {
            $deleteSiteURL = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMER_ACT_DELETESITE,
                    'customerID' => $site->getValue("CustomerID"),
                    'siteNo' => $site->getValue("SiteNo")
                )
            );
            $deleteSiteText = 'Delete Site';
        } else {
            $deleteSiteText = '';
        }
        //Horrible hack cause I don't understand why these are empty strings when they should be zero values!
        if ($this->dsCustomer->getValue('InvoiceSiteNo') == '') $this->dsCustomer->setValue('InvoiceSiteNo', '0');
        if ($this->dsCustomer->getValue('DeliverSiteNo') == '') $this->dsCustomer->setValue('DeliverSiteNo', '0');
        $this->template->set_var(
            array(
                'add1Class' => $thing,
                'add1' => $site->getValue("Add1"),
                'add2' => $site->getValue("Add2"),
                'add3' => $site->getValue("Add3"),
//                'townClass' => $site->getValue('TownClass'),
                'town' => $site->getValue("Town"),
                'county' => $site->getValue("County"),
//                'postcodeClass' => $site->getValue('PostcodeClass'),
                'postcode' => $site->getValue("Postcode"),
                'sitePhone' => $site->getValue("Phone"),
                'siteNo' => $site->getValue("SiteNo"),
                'customerID' => $site->getValue("CustomerID"),
                'sageRef' => $site->getValue("SageRef"),
                'debtorCode' => $site->getValue("DebtorCode"),
                'maxTravelHours' => $site->getValue("MaxTravelHours"),

                'invoiceSiteFlagChecked' => ($this->dsCustomer->getValue('InvoiceSiteNo') == $site->getValue('SiteNo')) ? CT_CHECKED : '',
                'deliverSiteFlagChecked' => ($this->dsCustomer->getValue('DeliverSiteNo') == $site->getValue('SiteNo')) ? CT_CHECKED : '',
                'activeFlagChecked' => ($site->getValue('ActiveFlag') == 'Y') ? CT_CHECKED : '',
                'deleteSiteText' => $deleteSiteText,
                'deleteSiteURL' => $deleteSiteURL
            )
        );

        $this->template->set_block('CustomerEdit', 'invoiceContacts', '');


        $this->parseContactSelector(
            $site->getValue('InvoiceContactID'),
            $this->dsContact,
            'invoiceContacts',
            'selectInvoiceContactBlock'
        );

        $this->template->set_block('CustomerEdit', 'deliverContacts', '');

        $this->parseContactSelector(
            $site->getValue('DeliverContactID'),
            $this->dsContact,
            'deliverContacts',
            'selectDeliverContactBlock'
        );


        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER)) {
            $this->template->set_var(
                array(
                    'addContactText' => CTCUSTOMER_TXT_ADD_CONTACT,
                    'addContactURL' => $addContactURL
                )
            );
        }

        $this->template->parse('sites', 'siteBlock', true);

        $this->dsContact->initialise();
        $this->dsContact->sortAscending('LastName');

        while ($this->dsContact->fetchNext()) {

            $this->template->set_block('CustomerEdit', 'selectSites', '');
            $this->template->set_block('CustomerEdit', 'customLetters', '');

            if ($this->dsContact->getValue('ContactID') == 0) { // New contact so no delete link
                $deleteContactURL = '';
                $deleteContactLink = '';
                $clientFormURL = '';
            } else {
                $deleteContactURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCUSTOMER_ACT_DELETECONTACT,
                            'contactID' => $this->dsContact->getValue("ContactID")
                        )
                    );
                $deleteContactLink =
                    '<a href="' . $deleteContactURL . '"><img align=middle border=0 hspace=2 src="images/icondelete.gif" alt="Delete contact" onClick="if(!confirm(\'Are you sure you want to delete this contact?\')) return(false)"></a>';
                $clientFormURL =
                    $this->buildLink(
                        'ClientInformationForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue("ContactID"),
                            'contactName' => $this->dsContact->getValue("FirstName") . ' ' . $this->dsContact->getValue("LastName")
                        )
                    );
                $dearJohnURL =
                    $this->buildLink(
                        'DearJohnForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue("ContactID")
                        )
                    );
                $dmLetterURL =
                    $this->buildLink(
                        'DMLetterForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue("ContactID")//,
//                  'letterTemplate' => 'dm_letter'
                        )
                    );
            }

            $this->template->set_var(
                array(
                    'contactID' => $this->dsContact->getValue("ContactID"),
                    'siteNo' => $this->dsContact->getValue("SiteNo"),
                    'customerID' => $this->dsContact->getValue("CustomerID"),
                    'supplierID' => $this->dsContact->getValue('SupplierID'),
                    'title' => $this->dsContact->getValue("Title"),
                    'firstName' => $this->dsContact->getValue("FirstName"),
                    'lastName' => $this->dsContact->getValue("LastName"),
                    'firstNameClass' => $this->dsContact->getValue('FirstNameClass'),
                    'lastNameClass' => $this->dsContact->getValue('LastNameClass'),
                    'phone' => $this->dsContact->getValue("Phone"),
                    'mobilePhone' => $this->dsContact->getValue("MobilePhone"),
                    'position' => $this->dsContact->getValue("Position"),
                    'fax' => $this->dsContact->getValue("Fax"),
                    'portalPassword' => $this->dsContact->getValue("PortalPassword"),
                    'failedLoginCount' => $this->dsContact->getValue("FailedLoginCount"),
                    'email' => $this->dsContact->getValue("Email"),
                    'notes' => $this->dsContact->getValue("Notes"),
                    'discontinuedFlag' => $this->dsContact->getValue("DiscontinuedFlag"),
                    'invoiceContactFlagChecked' => ($this->dsContact->getValue("ContactID") == $this->dsSite->getValue('InvoiceContactID')) ? CT_CHECKED : '',
                    'deliverContactFlagChecked' => ($this->dsContact->getValue("ContactID") == $this->dsSite->getValue('DeliverContactID')) ? CT_CHECKED : '',
                    'sendMailshotFlagChecked' => $this->getChecked($this->dsContact->getValue("SendMailshotFlag")),
                    'accountsFlagChecked' => $this->getChecked($this->dsContact->getValue("AccountsFlag")),
                    'mailshot1FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot1Flag")),
                    'mailshot2FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot2Flag")),
                    'mailshot3FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot3Flag")),
                    'mailshot4FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot4Flag")),
                    'mailshot5FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot5Flag")),
                    'mailshot6FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot6Flag")),
                    'mailshot7FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot7Flag")),
                    'mailshot8FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot8Flag")),
                    'mailshot9FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot9Flag")),
                    'mailshot10FlagChecked' => $this->getChecked($this->dsContact->getValue("Mailshot10Flag")),
                    'workStartedEmailFlagChecked' => $this->getChecked($this->dsContact->getValue('WorkStartedEmailFlag')),
                    'autoCloseEmailFlagChecked' => $this->getChecked($this->dsContact->getValue('AutoCloseEmailFlag')),
                    'clientFormURL' => $clientFormURL,
                    'dearJohnURL' => $dearJohnURL,
                    'dmLetterURL' => $dmLetterURL,
                    'customLetter1URL' => $customLetter1URL,
                    'deleteContactLink' => $deleteContactLink
                )
            );

            $this->siteDropdown(
                $this->dsContact->getValue('CustomerID'),
                $this->dsContact->getValue('SiteNo')
            );

            /*
            Display all the custom letters
            */
            foreach ($customLetterTemplates as $index => $filename) {

                $customLetterURL =
                    $this->buildLink(
                        'LetterForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue("ContactID"),
                            'letterTemplate' => $filename
                        )
                    );


                $this->template->set_var(

                    array(
                        'customLetterURL' => $customLetterURL,
                        'customLetterName' => $filename

                    )
                );

                $this->template->parse('customLetters', 'customLetterBlock', true);

            } // end foreach


            $this->template->parse('contacts', 'contactBlock', true);

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

            $this->template->set_block('CustomerEdit', 'orderBlock', 'orders');

            while ($dbeJOrdhead->fetchNext()) {

                $ordheadID = $dbeJOrdhead->getPKValue();

                $orderURL =
                    $this->buildLink(
                        'SalesOrder.php',
                        array(
                            'action' => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $ordheadID
                        )
                    );

                $this->template->set_var(
                    array(
                        'orderURL' => $orderURL,
                        'ordheadID' => $ordheadID,
                        'orderType' => $this->getOrderTypeDescription($dbeJOrdhead->getValue('type')),
                        'orderDate' => strftime("%d/%m/%Y", strtotime($dbeJOrdhead->getValue('date'))),
                        'custPORef' => $dbeJOrdhead->getValue('custPORef')
                    )
                );

                $this->template->parse('orders', 'orderBlock', true);

            }

        }
        if ($this->dsCustomer->getValue('CustomerID')) {
            $this->documents($this->dsCustomer->getValue('CustomerID'), 'CustomerEdit');
        }

        $this->template->parse('CONTENTS', 'CustomerEdit', true);
        $this->parsePage();
    }

    /**
     * Delete customer and associated sites/contacts
     * @access private
     */
    function deleteCustomer()
    {
        $this->setMethodName('deleteCustomer');
        if ($this->getCustomerID() == '') {
            $this->displayFatalError('CustomerID not passed');
        }
        if ($this->buCustomer->canDeleteCustomer($this->getCustomerID(), $this->userID)) {
            $this->buCustomer->deleteCustomer($this->getCustomerID());

            $nextURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCUSTOMER_ACT_DISP_SEARCH
                    )
                );
        } else {
            $this->setFormErrorMessage('Cannot delete this customer - dependencies exist');
            $this->setAction(CTCNC_ACT_DISP_EDIT);
            $this->buCustomer->getCustomerByID($this->getCustomerID(), $this->dsCustomer);
            $this->displayEditForm();
            exit;
        }

        header('Location: ' . $nextURL);
        exit;
    }

    /**
     * Delete sites and associated contacts
     * @access private
     */
    function deleteSite()
    {
        $this->setMethodName('deleteSite');
        if ($this->getCustomerID() == '') {
            $this->displayFatalError('CustomerID not passed');
        }
        if ($this->getSiteNo() == '') {
            $this->displayFatalError('SiteNo not passed');
        }
        if ($this->buCustomer->canDeleteSite($this->getCustomerID(), $this->getSiteNo())) {
            $this->buCustomer->deleteSite($this->getCustomerID(), $this->getSiteNo());
        } else {
            $this->setFormError('Cannot delete this site - dependencies exist');
        }
        $nextURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISP_EDIT,
                    'customerID' => $this->getCustomerID()
                )
            );
        header('Location: ' . $nextURL);
        exit;
    }

    /**
     * Delete contact
     * @access private
     */
    function deleteContact()
    {
        $this->setMethodName('deleteContact');
        if ($this->getContactID() == '') {
            $this->displayFatalError('ContactID not passed');
        }
        $this->buCustomer->getContactByID($this->getContactID(), $dsContact);
        $this->setCustomerID($dsContact->getValue('CustomerID'));

        $nextURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISP_EDIT,
                    'customerID' => $this->getCustomerID()
                )
            );

        if ($this->buCustomer->canDeleteContact($this->getContactID())) {
            $this->buCustomer->deleteContact($this->getContactID());
        } else {
            // Display a message page.
            $this->setTemplateFiles('Message', 'Message.inc');

            $message =
                'Cannot delete contact ' .
                $dsContact->getValue('FirstName') . ' ' . $dsContact->getValue('LastName') .
                ' because dependencies exist in the database';

            $this->template->set_var(
                array(
                    'message' => $message,
                    'nextURL' => $nextURL
                )
            );
            $this->template->parse('CONTENTS', 'Message', true);
            $this->parsePage();
            exit;
        }
        header('Location: ' . $nextURL);
        exit;
    }

    /**
     * Update details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $this->setCustomerID($this->dsCustomer->getValue('CustomerID'));
        if (!$this->formError) {
            // Update the database
            if ($this->getCustomerID() == 0) {      // New customer
                $this->buCustomer->insertCustomer($this->dsCustomer, $this->dsSite, $this->dsContact);
                $this->dsCustomer->initialise();
                $this->dsCustomer->fetchNext();
                $this->setCustomerID($this->dsCustomer->getValue('CustomerID'));
            } else {                // Updates to customer and updates/inserts to sites and contacts
                $this->buCustomer->updateCustomer($this->dsCustomer);
                $this->buCustomer->updateSite($this->dsSite);
                if (isset($this->postVars["form"]["contact"])) {
                    $this->buCustomer->updateContact($this->dsContact);
                }
            }
            $this->setAction(CTCUSTOMER_ACT_DISP_SUCCESS);
            if ($_SESSION['save_page']) {
                header('Location: ' . $_SESSION['save_page']);
                exit;
            } else {
                $nextURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCNC_ACT_DISP_EDIT,
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

    /**
     * Display the popup selector form
     * @access private
     */
    function displayCustomerSelectPopup()
    {
        $this->setMethodName('displayCustomerSelectPopup');
        $this->buCustomer->getCustomersByNameMatch('',
                                                   '',
                                                   $this->getCustomerString(),
                                                   '',
                                                   '',
                                                   '',
                                                   '',
                                                   '',
                                                   $this->dsCustomer);
        if ($this->dsCustomer->rowCount() == 1) {
            $this->setTemplateFiles('CustomerSelect', 'CustomerSelectOne.inc');
        }
        if ($this->dsCustomer->rowCount() == 0) {
            $this->template->set_var('customerString', $this->getCustomerString());
            $this->setTemplateFiles('CustomerSelect', 'CustomerSelectNone.inc');
        }
        if ($this->dsCustomer->rowCount() > 1) {
            $this->setTemplateFiles('CustomerSelect', 'CustomerSelectPopup.inc');
        }
        // fields to populate on parent page
        $this->template->set_var(
            array(
                'parentIDField' => $_SESSION['parentIDField'],
                'parentDescField' => $_SESSION['parentDescField']
            )
        );

// Parameters
        $this->setPageTitle('Customer Selection');
        if ($this->dsCustomer->rowCount() > 0) {
            $this->template->set_block('CustomerSelect', 'customerBlock', 'customers');
            while ($this->dsCustomer->fetchNext()) {
                $this->template->set_var(
                    array(
                        'customerName' => addslashes($this->dsCustomer->getValue("Name")),
                        'customerID' => $this->dsCustomer->getValue("CustomerID")
                    )
                );
                $this->template->parse('customers', 'customerBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'CustomerSelect', true);
        $this->parsePage();
    }

    function getCustomerNotePopupLink($customerID)
    {
        if ($customerID) {
            $url =
                $this->buildLink(
                    'CustomerNote.php',
                    array(
                        'action' => 'customerNoteHistoryPopup',
                        'customerID' => $customerID,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $link = '<A HREF="' . $url . ' " target="_blank" >Notes History</A>';
        } else {
            $link = '';
        }

        return $link;

    }

    function siteDropdown(
        $customerID,
        $siteNo,
        $templateName = 'CustomerEdit',
        $blockName = 'selectSiteBlock'
    )
    {
        // Site selection
        $dbeSite = new DBESite($this);
        $dbeSite->setValue('CustomerID', $customerID);
        $dbeSite->getRowsByCustomerID();


        while ($dbeSite->fetchNext()) {
            $siteSelected = ($siteNo == $dbeSite->getValue("SiteNo")) ? CT_SELECTED : '';
            $siteDesc = $dbeSite->getValue("SiteNo");

            $this->template->set_var(
                array(
                    'siteSelected' => $siteSelected,
                    'selectSiteNo' => $dbeSite->getValue("SiteNo"),
                    'selectSiteDesc' => $siteDesc
                )
            );
            $this->template->parse('selectSites', $blockName, true);
        }

    } // end siteDropdown

    /**
     * Get and parse contact drop-down selector
     * @access private
     */
    function parseContactSelector($contactID, &$dsContact, $blockVar, $blockName)
    {
        $dsContact->initialise();
        while ($dsContact->fetchNext()) {
            $contactSelected = ($dsContact->getValue('ContactID') == $contactID) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    $blockName . 'Selected' => $contactSelected,
                    $blockName . 'ContactID' => $dsContact->getValue('ContactID'),
                    $blockName . 'FirstName' => $dsContact->getValue('FirstName'),
                    $blockName . 'LastName' => $dsContact->getValue('LastName')
                )
            );
            $this->template->parse($blockVar, $blockName, true);
        }
    }

    function documents($customerID, $templateName)
    {
        $this->template->set_block($templateName, 'portalDocumentBlock', 'portalDocuments');

        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $buPortalCustomerDocument = new BUPortalCustomerDocument($this);
            $buPortalCustomerDocument->getDocumentsByCustomerID($customerID, $dsPortalCustomerDocument);

            $urlAddDocument =
                $this->buildLink(
                    'PortalCustomerDocument.php',
                    array(
                        'action' => 'add',
                        'customerID' => $customerID
                    )
                );


            $this->template->set_var(
                array(
                    'txtAddDocument' => 'Add document',
                    'urlAddDocument' => $urlAddDocument
                )
            );

            while ($dsPortalCustomerDocument->fetchNext()) {

                $urlEditDocument =
                    $this->buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action' => 'edit',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue('portalCustomerDocumentID')
                        )
                    );

                $urlViewFile =
                    $this->buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action' => 'viewFile',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue('portalCustomerDocumentID')
                        )
                    );

                $urlDeleteDocument =
                    $this->buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action' => 'delete',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue('portalCustomerDocumentID')
                        )
                    );

                $this->template->set_var(
                    array(
                        'description' => $dsPortalCustomerDocument->getValue("description"),
                        'filename' => $dsPortalCustomerDocument->getValue("filename"),
                        'startersFormFlag' => $dsPortalCustomerDocument->getValue("startersFormFlag"),
                        'leaversFormFlag' => $dsPortalCustomerDocument->getValue("leaversFormFlag"),
                        'mainContactOnlyFlag' => $dsPortalCustomerDocument->getValue("mainContactOnlyFlag"),
                        'createDate' => $dsPortalCustomerDocument->getValue("createdDate"),
                        'urlViewFile' => $urlViewFile,
                        'urlEditDocument' => $urlEditDocument,
                        'urlDeleteDocument' => $urlDeleteDocument
                    )
                );
                $this->template->parse('portalDocuments', 'portalDocumentBlock', true);
            } // end while

        } // end if


    }

    private function getTrueFalse($value)
    {
        return $value == 'Y';

    } // end function documents

}// end of class
?>
