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
require_once($cfg['path_dbe'] . '/DBEJSite.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Parameters
define(
    'CTCUSTOMER_VAL_NONE_SELECTED',
    -1
);
// Actions
define(
    'CTCUSTOMER_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTCUSTOMER_ACT_SEARCH',
    'search'
);
define(
    'CTCUSTOMER_ACT_DISP_LIST',
    'dispList'
);
define(
    'CTCUSTOMER_ACT_UPDATE',
    'update'
);
define(
    'CTCUSTOMER_ACT_DELETECUSTOMER',
    'deleteCustomer'
);
define(
    'CTCUSTOMER_ACT_ADDCONTACT',
    'addContact'
);
define(
    'CTCUSTOMER_ACT_DELETECONTACT',
    'deleteContact'
);
define(
    'CTCUSTOMER_ACT_ADDSITE',
    'addSite'
);
define(
    'CTCUSTOMER_ACT_DELETESITE',
    'deleteSite'
);
define(
    'CTCUSTOMER_ACT_ADDCUSTOMER',
    'addCustomer'
);
define(
    'CTCUSTOMER_ACT_DISP_SUCCESS',
    'dispSuccess'
);
define(
    'CTCUSTOMER_ACT_DISP_CUST_POPUP',
    'dispCustPopup'
);
// Messages
define(
    'CTCUSTOMER_MSG_CUSTTRING_REQ',
    'Please enter search parameters'
);
define(
    'CTCUSTOMER_MSG_NONE_FND',
    'No customers found'
);
define(
    'CTCUSTOMER_MSG_CUS_NOT_FND',
    'Customer not found'
);
define(
    'CTCUSTOMER_CLS_FORM_ERROR',
    'formError'
);
define(
    'CTCUSTOMER_CLS_TABLE_EDIT_HEADER',
    'tableEditHeader'
);
define(
    'CTCUSTOMER_CLS_FORM_ERROR_UC',
    'formErrorUC'
);                // upper case
define(
    'CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC',
    'tableEditHeaderUC'
);
// Form text
define(
    'CTCUSTOMER_TXT_ADD_SITE',
    'Add site'
);
define(
    'CTCUSTOMER_TXT_ADD_CONTACT',
    'Add contact'
);


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
        "1"  => "Monthly",
        "3"  => "Quarterly",
        "6"  => "Six-monthly",
        "12" => "Annually"
    );

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
        $this->buCustomer = new BUCustomer($this);
        $this->dsContact = new DataSet($this);
        $this->dsContact->copyColumnsFrom($this->buCustomer->dbeContact);
        $this->dsContact->addColumn(
            'FirstNameClass',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsContact->addColumn(
            'LastNameClass',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSite = new DataSet($this);
        $this->dsSite->setIgnoreNULLOn();
        $this->dsSite->copyColumnsFrom($this->buCustomer->dbeSite);
        $this->dsSite->addColumn(
            'Add1Class',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSite->addColumn(
            'TownClass',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSite->addColumn(
            'PostcodeClass',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer = new DataSet($this);
        $this->dsCustomer->setIgnoreNULLOn();
        $this->dsCustomer->copyColumnsFrom($this->buCustomer->dbeCustomer);
        $this->dsCustomer->addColumn(
            'NameClass',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            'InvoiceSiteMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            'DeliverSiteMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            'SectorMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            'specialAttentionEndDateMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            'lastReviewMeetingDateMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
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
        $dsSearchForm->addColumn(
            'customerID',
            DA_STRING,
            DA_NOT_NULL
        );

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $this->setCustomerID($dsSearchForm->getValue('customerID'));
                $link = $this->buildLink(
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

            $urlSubmit = $this->buildLink(
                $_SERVER ['PHP_SELF'],
                array('action' => CTCNC_ACT_SEARCH)
            );


            $this->setPageTitle('Customer CRM');

            if ($dsSearchForm->getValue('customerID')) {
                $buCustomer = new BUCustomer ($this);
                $buCustomer->getCustomerByID(
                    $dsSearchForm->getValue('customerID'),
                    $dsCustomer
                );
                $customerString = $dsCustomer->getValue(DBECustomer::name);
            }

            $urlCustomerPopup =
                $this->buildLink(
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
                        'customerLeadStatusID'       => $dsCustomerLeadStatuses->getValue("customerLeadStatusID"),
                        'customerLeadStatusName'     => $dsCustomerLeadStatuses->getValue("name"),
                        'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue(
                                'customerLeadStatusID'
                            ) == $this->dsCustomer->getValue(DBECustomer::customerLeadStatusID)) ? CT_SELECTED : ''
                    )
                );
                $this->template->parse(
                    'customerleadstatuses',
                    'customerLeadStatusBlock',
                    true
                );
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
                    'formError'         => $this->formError,
                    'customerID'        => $dsSearchForm->getValue('customerID'),
                    'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
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
            $leadStatuses[$dsCustomerLeadStatuses->getValue(
                "customerLeadStatusID"
            )] = $dsCustomerLeadStatuses->getValue("name");
        }

        while ($results->fetchNext()) {
            $customerID = $results->getValue(DBEContact::customerID);

            if (!isset($customers[$customerID])) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($results->getValue(DBEContact::customerID));

                $link = $this->buildLink(
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
                    "bluestoneLeadStatus" => $leadStatuses[+$dbeCustomer->getValue(DBECustomer::customerLeadStatusID)]
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

    } // end search

    function initialProcesses()
    {
        $this->retrieveHTMLVars();
        parent::initialProcesses();
    }

    function setContact(&$contactArray)
    {
        if (!is_array(
            $contactArray
        )) {          // For some reason the dynamically generated call to setContact from retrieveHTMLVars does not
            return;                                // pass a valid array so I avoid a crash like this! Same for setSite() below.
        }

        while (list($key, $value) = each($contactArray)) {
            $this->dsContact->setUpdateModeInsert();
            $this->dsContact->setValue(
                DBEContact::contactID,
                $value['contactID']
            );
            $this->dsContact->setValue(
                DBEContact::customerID,
                $value['customerID']
            );
            $this->dsContact->setValue(
                DBEContact::supplierID,
                $value['supplierID']
            );
            $this->dsContact->setValue(
                DBEContact::siteNo,
                $value['siteNo']
            );
            $this->dsContact->setValue(
                DBEContact::title,
                $value['title']
            );
            $this->dsContact->setValue(
                DBEContact::lastName,
                $value['lastName']
            );
            if ($this->dsContact->getValue(DBEContact::lastName) == '') {
                $this->setFormErrorOn();
                $this->dsContact->setValue(
                    'LastNameClass',
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $this->dsContact->setValue(
                DBEContact::firstName,
                $value['firstName']
            );
            $this->dsContact->setValue(
                DBEContact::email,
                $value['email']
            );
            $this->dsContact->setValue(
                DBEContact::phone,
                $value['phone']
            );
            $this->dsContact->setValue(
                DBEContact::notes,
                $value['notes']
            );
            $this->dsContact->setValue(
                DBEContact::mobilePhone,
                $value['mobilePhone']
            );
            $this->dsContact->setValue(
                DBEContact::position,
                $value['position']
            );
            $this->dsContact->setValue(
                DBEContact::fax,
                $value['fax']
            );
            $this->dsContact->setValue(
                DBEContact::portalPassword,
                $value['portalPassword']
            );

            $this->dsContact->setValue(
                DBEContact::accountsFlag,
                $this->getYN($value['accountsFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::discontinuedFlag,
                $value['discontinuedFlag']
            );
            $this->dsContact->setValue(
                DBEContact::sendMailshotFlag,
                $this->getYN($value['sendMailshotFlag'])
            );// Use getYN() because HTML POST does not send a FALSE value
            $this->dsContact->setValue(
                DBEContact::mailshot2Flag,
                $this->getYN($value['mailshot2Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot3Flag,
                $this->getYN($value['mailshot3Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot4Flag,
                $this->getYN($value['mailshot4Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot8Flag,
                $this->getYN($value['mailshot8Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot9Flag,
                $this->getYN($value['mailshot9Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::workStartedEmailFlag,
                $this->getYN($value['workStartedEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::autoCloseEmailFlag,
                $this->getYN($value['autoCloseEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::failedLoginCount,
                $value['failedLoginCount']
            );


            if (
                $value['email'] == ''
            ) {
                $this->setFormErrorOn();
                $this->formErrorMessage = 'NOT SAVED: Email address required';
            }
            // Determine whether a new contact is to be added
            if ($this->dsContact->getValue(DBEContact::contactID) == 0) {
                if (
                    ($this->dsContact->getValue(DBEContact::title) != '') |
                    ($this->dsContact->getValue(DBEContact::firstName) != '') |
                    ($this->dsContact->getValue(DBEContact::lastName) != '')
                ) {
                    $this->dsContact->post();
                }
            } else {
                $this->dsContact->post();  // Existing contact
            }
        }
    }

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
        if (gettype($dbSource) != "object")
            $this->raiseError("dbSource is not initialised");
        if (!is_subclass_of(
            $dbSource,
            DA_CLASSNAME_DBENTITY
        ))
            $this->raiseError("dbSource must be subclass of " . DA_CLASSNAME_DBENTITY);
        return ($dsDestination->replicate($dbSource));
    }

    function setSite(&$siteArray)
    {
        if (!is_array($siteArray)) {
            return;
        }
        while (list($key, $value) = each($siteArray)) {

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
            if ($this->dsSite->getValue(DBESite::add1) == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    'Add1Class',
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
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
            if ($this->dsSite->getValue(DBESite::town) == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    'TownClass',
                    CTCUSTOMER_CLS_FORM_ERROR_UC
                );
            }
            $this->dsSite->setValue(
                DBESite::county,
                $value['county']
            );
            $this->dsSite->setValue(
                DBESite::postcode,
                strtoupper($value['postcode'])
            );
            if ($this->dsSite->getValue(DBESite::postcode) == '') {
//                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    'PostcodeClass',
                    CTCUSTOMER_CLS_FORM_ERROR_UC
                );
            }
            $this->dsSite->setValue(
                DBESite::phone,
                $value['sitePhone']
            );
            $this->dsSite->post();
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

    function setSiteNo($siteNo)
    {
        $this->setNumericVar(
            'siteNo',
            $siteNo
        );
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

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
                $value['customerID'],
                $this->dsCustomer
            );
            echo $this->dsCustomer->getValue(DBECustomer::name);
//            $this->dsCustomer->getRow($value['customerID']);
//            $this->getData($this->, $this->dsSite);
            $this->dsCustomer->setUpdateModeInsert();
            $this->dsCustomer->setValue(
                DBECustomer::mailshotFlag,
                $this->getYN($value['mailshotFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::customerLeadStatusID,
                $value['customerLeadStatusID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::dateMeetingConfirmed,
                $value['dateMeetingConfirmedDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::meetingDateTime,
                $value['meetingDateTime']
            );
            $this->dsCustomer->setValue(
                DBECustomer::inviteSent,
                $this->getTrueFalse($value[DBECustomer::inviteSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportProcessed,
                $this->getTrueFalse($value[DBECustomer::reportProcessed])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportSent,
                $this->getTrueFalse($value[DBECustomer::reportSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::crmComments,
                $value[DBECustomer::crmComments]
            );
            $this->dsCustomer->setValue(
                DBECustomer::companyBackground,
                $value[DBECustomer::companyBackground]
            );
            $this->dsCustomer->setValue(
                DBECustomer::decisionMakerBackground,
                $value[DBECustomer::decisionMakerBackground]
            );
            $this->dsCustomer->setValue(
                DBECustomer::opportunityDeal,
                $value[DBECustomer::opportunityDeal]
            );
            $this->dsCustomer->setValue(
                DBECustomer::rating,
                $value[DBECustomer::rating]
            );
            $reviewDate = DateTime::createFromFormat(
                'd/m/Y',
                $value[DBECustomer::reviewDate]
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewDate,
                $reviewDate->format(DATE_ISO8601)
            );

            $this->dsCustomer->setValue(
                DBECustomer::reviewTime,
                $value[DBECustomer::reviewTime]
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewUserID,
                $value[DBECustomer::reviewUserID]
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewAction,
                $value[DBECustomer::reviewAction]
            );

            $this->dsCustomer->setValue(
                DBECustomer::customerLeadStatusID,
                $value['customerLeadStatusID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::dateMeetingConfirmed,
                $value['dateMeetingConfirmedDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::meetingDateTime,
                $value['meetingDateTime']
            );
            $this->dsCustomer->setValue(
                DBECustomer::inviteSent,
                $this->getTrueFalse($value[DBECustomer::inviteSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportProcessed,
                $this->getTrueFalse($value[DBECustomer::reportProcessed])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reportSent,
                $this->getTrueFalse($value[DBECustomer::reportSent])
            );
            $this->dsCustomer->setValue(
                DBECustomer::crmComments,
                $value[DBECustomer::crmComments]
            );
            $this->dsCustomer->setValue(
                DBECustomer::companyBackground,
                $value[DBECustomer::companyBackground]
            );
            $this->dsCustomer->setValue(
                DBECustomer::decisionMakerBackground,
                $value[DBECustomer::decisionMakerBackground]
            );
            $this->dsCustomer->setValue(
                DBECustomer::opportunityDeal,
                $value[DBECustomer::opportunityDeal]
            );
            $this->dsCustomer->setValue(
                DBECustomer::rating,
                $value[DBECustomer::rating]
            );

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
            $dateArray = explode(
                '/',
                $dateDMY
            );
            return ($dateArray[2] . '-' . str_pad(
                    $dateArray[1],
                    2,
                    '0',
                    STR_PAD_LEFT
                ) . '-' . str_pad(
                    $dateArray[0],
                    2,
                    '0',
                    STR_PAD_LEFT
                ));
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
                    'action'     => CTCNC_ACT_DISP_EDIT,
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
                        'action'     => CTCNC_ACT_DISP_EDIT,
                        'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
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

        $this->setTemplateFiles(
            'CustomerReviewList',
            'CustomerReviewList.inc'
        );

        $this->setPageTitle("My Daily Call List");

        $this->template->set_block(
            'CustomerReviewList',
            'reviewBlock',
            'reviews'
        );


        if ($this->buCustomer->getDailyCallList($dsCustomer)) {

            $buUser = new BUUser($this);

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );

                if ($dsCustomer->getValue(DBECustomer::reviewUserID)) {
                    $buUser->getUserByID(
                        $dsCustomer->getValue(DBECustomer::reviewUserID),
                        $dsUser
                    );
                    $user = $dsUser->getValue('name');
                } else {
                    $user = false;
                }

                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'reviewDate'   => $dsCustomer->getValue(DBECustomer::reviewDate),
                        'reviewTime'   => $dsCustomer->getValue(DBECustomer::reviewTime),
                        'reviewAction' => $dsCustomer->getValue(DBECustomer::reviewAction),
                        'reviewUser'   => $user,
                        'linkURL'      => $linkURL
                    )
                );

                $this->template->parse(
                    'reviews',
                    'reviewBlock',
                    true
                );

            }

            $this->template->parse(
                'CONTENTS',
                'CustomerReviewList',
                true
            );

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

            $this->setTemplateFiles(
                'Customer24HourSupport',
                'Customer24HourSupport.inc'
            );

            $this->template->set_block(
                'Customer24HourSupport',
                'customerBlock',
                'customers'
            );

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'linkURL'      => $linkURL
                    )
                );

                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $this->template->parse(
                'CONTENTS',
                'Customer24HourSupport',
                true
            );

        } else {

            $this->setTemplateFiles(
                'SimpleMessage',
                'SimpleMessage.inc'
            );
            $this->template->set_var(
                'message',
                'There are no 24 Hour Support customers'
            );
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


            $this->setTemplateFiles(
                'CustomerSpecialAttention',
                'CustomerSpecialAttention.inc'
            );

            $this->template->set_block(
                'CustomerSpecialAttention',
                'customerBlock',
                'customers'
            );

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName'            => $dsCustomer->getValue(DBECustomer::name),
                        'specialAttentionEndDate' => $dsCustomer->getValue(DBECustomer::specialAttentionEndDate),
                        'linkURL'                 => $linkURL
                    )
                );

                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $this->template->parse(
                'CONTENTS',
                'CustomerSpecialAttention',
                true
            );

        } else {

            $this->setTemplateFiles(
                'SimpleMessage',
                'SimpleMessage.inc'
            );

            $this->template->set_var(array('message' => 'There are no special attention customers'));

            $this->template->parse(
                'CONTENTS',
                'SimpleMessage',
                true
            );
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
        $this->setTemplateFiles(
            'CustomerSearch',
            'CustomerSearch.inc'
        );
// Parameters
        $this->setPageTitle("Customer");
        $submitURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_SEARCH)
        );
        $createURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_ADDCUSTOMER)
        );
        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->template->set_var(
            array(
                'contactString'           => $this->getContactString(),
                'phoneString'             => $this->getPhoneString(),
                'customerString'          => $this->getCustomerString(),
                'address'                 => $this->getAddress(),
                'customerStringMessage'   => $this->getCustomerStringMessage(),
                'newCustomerFromDate'     => $this->getNewCustomerFromDate(),
                'newCustomerToDate'       => $this->getNewCustomerToDate(),
                'droppedCustomerFromDate' => $this->getDroppedCustomerFromDate(),
                'droppedCustomerToDate'   => $this->getDroppedCustomerToDate(),
                'submitURL'               => $submitURL,
                'createURL'               => $createURL,
                'customerPopupURL'        => $customerPopupURL,
            )
        );
        if (is_object($this->dsCustomer)) {
            $this->template->set_block(
                'CustomerSearch',
                'customerBlock',
                'customers'
            );
            while ($this->dsCustomer->fetchNext()) {
                $customerURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTCNC_ACT_DISP_EDIT,
                            'customerID' => $this->dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );

                $this->template->set_var(
                    array(
                        'customerName' => $this->dsCustomer->getValue(DBECustomer::name),
                        'customerURL'  => $customerURL
                    )
                );
                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'CustomerSearch',
            true
        );
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
//                        'customerID' => $this->dsCustomer->getValue(DBECustomer::CustomerID)
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
                $deleteCustomerURL = $this->buildLink(
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
            $cancelURL = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => CTCUSTOMER_ACT_DISP_SEARCH)
            );
        }
        $addSiteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_ADDSITE,
                    'customerID' => $this->getCustomerID(),
                )
            );
        if (!$this->formError) {              // Not displaying form error page so get customer record
//            $this->dsCustomer->setValue(DBECustomer::NameClass, CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
//            $this->dsCustomer->setValue(DBECustomer::InvoiceSiteMessage, CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
//            $this->dsCustomer->setValue(DBECustomer::DeliverSiteMessage, CTCUSTOMER_CLS_TABLE_EDIT_HEADER);
        }

        $this->setPageTitle("Customer - " . $this->dsCustomer->getValue(DBECustomer::name));
        /*
        Get the list of custom letter template file names from the custom letter directory
        */
        $dir = LETTER_TEMPLATE_DIR . "/custom/";

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
        } else {
            $customLetterTemplates = false;
        }

        if ($customerFolderPath = $this->buCustomer->customerFolderExists(
            $this->dsCustomer->getValue(DBECustomer::customerID)
        )) {
            $customerFolderLink =
                '<a href="file:' . $customerFolderPath . '" target="_blank" title="Open Folder">Open Folder</a>';
        } else {
            $urlCreateCustomerFolder =
                $this->buildLink(
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
            $this->buildLink(
                'RenewalReport.php',
                array(
                    'action'     => 'produceReport',
                    'customerID' => $this->getCustomerID()
                )
            );


        $renewalLink = '<a href="' . $renewalLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';

        $passwordLinkURL =
            $this->buildLink(
                'Password.php',
                array(
                    'action'     => 'list',
                    'customerID' => $this->getCustomerID()
                )
            );


        $passwordLink = '<a href="' . $passwordLinkURL . '" target="_blank" title="Passwords">Service Passwords</a>';

        $showInactiveContactsURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'               => 'dispEdit',
                    'customerID'           => $this->getCustomerID(),
                    'showInactiveContacts' => '1'
                )
            );
        $showInactiveSitesURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'            => 'dispEdit',
                    'customerID'        => $this->getCustomerID(),
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
                'reviewMeetingEmailSentFlag' => $this->dsCustomer->getValue(DBECustomer::reviewMeetingEmailSentFlag),
                'customerNotePopupLink' => $this->getCustomerNotePopupLink($this->getCustomerID()),
                'showInactiveContactsURL' => $showInactiveContactsURL,
                'showInactiveSitesURL' => $showInactiveSitesURL,
                'customerID' => $this->dsCustomer->getValue(DBECustomer::customerID),
                'customerName' => $this->dsCustomer->getValue(DBECustomer::name),
                'reviewCount' => $this->buCustomer->getReviewCount(),
                'customerFolderLink' => $customerFolderLink,
                //'customerNameClass' => $this->dsCustomer->getValue(DBECustomer::NameClass),
                //'SectorMessage' => $this->dsCustomer->getValue(DBECustomer::SectorMessage),
                'regNo' => $this->dsCustomer->getValue(DBECustomer::regNo),
                'mailshotFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::mailshotFlag)),
                'referredFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::referredFlag)),
                'specialAttentionFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::specialAttentionFlag)),
                'specialAttentionEndDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::specialAttentionEndDate)),
                //'specialAttentionEndDateMessage' => $this->dsCustomer->getValue(DBECustomer::SpecialAttentionEndDateMessage),
                'lastReviewMeetingDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::lastReviewMeetingDate)),
                'dateMeetingConfirmedDate' => $this->dsCustomer->getValue(DBECustomer::dateMeetingConfirmed),
                'meetingDateTime' => Controller::dateToISO($this->dsCustomer->getValue(DBECustomer::meetingDateTime)),
                DBECustomer::inviteSent => $this->getChecked($this->dsCustomer->getValue(DBECustomer::inviteSent)),
                DBECustomer::reportProcessed => $this->getChecked($this->dsCustomer->getValue(DBECustomer::reportProcessed)),
                DBECustomer::reportSent => $this->getChecked($this->dsCustomer->getValue(DBECustomer::reportSent)),
                DBECustomer::crmComments => $this->dsCustomer->getValue(DBECustomer::crmComments),
                DBECustomer::companyBackground => $this->dsCustomer->getValue(DBECustomer::companyBackground),
                DBECustomer::decisionMakerBackground => $this->dsCustomer->getValue(DBECustomer::decisionMakerBackground),
                DBECustomer::opportunityDeal => $this->dsCustomer->getValue(DBECustomer::opportunityDeal),
                DBECustomer::rating => $this->dsCustomer->getValue(DBECustomer::rating),

                'prospectFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::prospectFlag)
                ),
//                'othersEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::othersEmailMainFlag)),
//                'workStartedEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::workStartedEmailMainFlag)),
//                'autoCloseEmailMainFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::autoCloseEmailMainFlag)),
                'pcxFlagChecked' => $this->getChecked($this->dsCustomer->getValue(DBECustomer::pcxFlag)),
                'createDate' => $this->dsCustomer->getValue(DBECustomer::createDate),
                'mailshot2FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot2FlagDesc"),
                'mailshot3FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot3FlagDesc"),
                'mailshot4FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot4FlagDesc"),
                'mailshot8FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot8FlagDesc"),
                'mailshot9FlagDesc' => $this->buCustomer->dsHeader->getValue("mailshot9FlagDesc"),
                'submitURL' => $submitURL,
                'renewalLink' => $renewalLink,
                'passwordLink' => $passwordLink,
                'deleteCustomerURL' => $deleteCustomerURL,
                'deleteCustomerText' => $deleteCustomerText,
                'cancelURL' => $cancelURL,
                'disabled' => $this->hasPermissions(PHPLIB_PERM_SALES) ? '' : CTCNC_HTML_DISABLED,
                'gscTopUpAmount' => $this->dsCustomer->getValue(DBECustomer::gscTopUpAmount),
                'noOfServers' => $this->dsCustomer->getValue(DBECustomer::noOfServers),
                'noOfSites' => $this->dsCustomer->getValue(DBECustomer::noOfSites),
                'modifyDate' => $this->dsCustomer->getValue(DBECustomer::modifyDate),
                'reviewDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::reviewDate)),
                'reviewTime' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::reviewTime)),
                'becameCustomerDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::becameCustomerDate)),
                'droppedCustomerDate' => Controller::dateYMDtoDMY($this->dsCustomer->getValue(DBECustomer::droppedCustomerDate)
                ),'reviewAction' => $this->dsCustomer->getValue(DBECustomer::reviewAction),
                'comments' => $this->dsCustomer->getValue(DBECustomer::comments),
                'techNotes' => $this->dsCustomer->getValue(DBECustomer::techNotes),
                'slaP1' => $this->dsCustomer->getValue(DBECustomer::slaP1),
                'slaP2' => $this->dsCustomer->getValue(DBECustomer::slaP2),
                'slaP3' => $this->dsCustomer->getValue(DBECustomer::slaP3),
                'slaP4' => $this->dsCustomer->getValue(DBECustomer::slaP4),
                'slaP5' => $this->dsCustomer->getValue(DBECustomer::slaP5),

                'add1' => $site->getValue(DBESite::add1),
                'add2' => $site->getValue(DBESite::add2),
                'add3' => $site->getValue(DBESite::add3),

                'town'              => $site->getValue(DBESite::town),
                'county'            => $site->getValue(DBESite::county),
                'postcode'          => $site->getValue(DBESite::postcode),
                'sitePhone'         => $site->getValue(DBESite::phone),
                'siteNo'            => $site->getValue(DBESite::siteNo),
                'sageRef'           => $site->getValue(DBESite::sageRef),
                'debtorCode'        => $site->getValue(DBESite::debtorCode),
                'maxTravelHours'    => $site->getValue(DBESite::maxTravelHours),
                'deliverContactID'  => $site->getValue(DBESite::deliverContactID),
                'activeFlagChecked' => ($site->getValue(DBESite::activeFlag) == 'Y') ? CT_CHECKED : '',
                'activeFlag'        => $site->getValue(DBESite::activeFlag),
                'deliveryContactID' => $site->getValue(DBESite::deliverContactID),
                'invoiceContactID'  => $site->getValue(DBESite::deliverContactID),
                'nonUKFlag'         => $site->getValue(DBESite::nonUKFlag),
                ' deleteSiteText'   => null,
                'deleteSiteURL'     => null
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
                    'noOfPCsSelected' => $value == $this->dsCustomer->getValue(DBECustomer::noOfPCs) ? CT_SELECTED : ''
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
        $this->buCustomer->getCustomerTypes($dsCustomerType);
        while ($dsCustomerType->fetchNext()) {
            $this->template->set_var(
                array(
                    'customerTypeID'          => $dsCustomerType->getValue("customerTypeID"),
                    'customerTypeDescription' => $dsCustomerType->getValue("description"),
                    'customerTypeSelected'    => ($dsCustomerType->getValue(
                            'customerTypeID'
                        ) == $this->dsCustomer->getValue(DBECustomer::customerTypeID)) ? CT_SELECTED : ''
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
                    ) ? CT_SELECTED : ''
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
                    'customerLeadStatusID'       => $dsCustomerLeadStatuses->getValue("customerLeadStatusID"),
                    'customerLeadStatusName'     => $dsCustomerLeadStatuses->getValue("name"),
                    'customerLeadStatusSelected' => ($dsCustomerLeadStatuses->getValue(
                            'customerLeadStatusID'
                        ) == $this->dsCustomer->getValue(DBECustomer::customerLeadStatusID)) ? CT_SELECTED : ''
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
        $buSector->getAll($dsSector);
        while ($dsSector->fetchNext()) {
            $this->template->set_var(
                array(
                    'sectorID'          => $dsSector->getValue("sectorID"),
                    'sectorDescription' => $dsSector->getValue("description"),
                    'sectorSelected'    => ($dsSector->getValue('sectorID') == $this->dsCustomer->getValue(
                            DBECustomer::sectorID
                        )) ? CT_SELECTED : ''
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
        $this->buCustomer->getLeadStatus($dsLeadStatus);
        while ($dsLeadStatus->fetchNext()) {

            $this->template->set_var(
                array(
                    'leadStatusID'          => $dsLeadStatus->getValue("leadStatusID"),
                    'leadStatusDescription' => $dsLeadStatus->getValue("description"),
                    'leadStatusSelected'    => ($dsLeadStatus->getValue('leadStatusID') == $this->dsCustomer->getValue(
                            DBECustomer::leadStatusID
                        )) ? CT_SELECTED : ''
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
        $buUser->getAllUsers($dsUser);

        while ($dsUser->fetchNext()) {

            $this->template->set_var(
                array(
                    'reviewUserID'       => $dsUser->getValue("userID"),
                    'reviewUserName'     => $dsUser->getValue("name"),
                    'reviewUserSelected' => ($dsUser->getValue('userID') == $this->dsCustomer->getValue(
                            DBECustomer::reviewUserID
                        )) ? CT_SELECTED : ''
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
                    'accountManagerUserID'       => $dsUser->getValue("userID"),
                    'accountManagerUserName'     => $dsUser->getValue("name"),
                    'accountManagerUserSelected' => ($dsUser->getValue('userID') == $this->dsCustomer->getValue(
                            DBECustomer::accountManagerUserID
                        )) ? CT_SELECTED : ''
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
            $this->buildLink(
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
            $buProject->getProjectsByCustomerID(
                $this->getCustomerID(),
                $dsProject
            );

            while ($dsProject->fetchNext()) {
                if ($buProject->canDelete($dsProject->getValue('projectID'))) {
                    $deleteProjectLink =
                        $this->buildLink(
                            'Project.php',
                            array(
                                'action'    => 'delete',
                                'projectID' => $dsProject->getValue('projectID')
                            )
                        );
                    $deleteProjectText = 'delete';
                } else {
                    $deleteProjectLink = '';
                    $deleteProjectText = '';
                }

                $editProjectLink =
                    $this->buildLink(
                        'Project.php',
                        array(
                            'action'    => 'edit',
                            'projectID' => $dsProject->getValue('projectID')
                        )
                    );

                $this->template->set_var(
                    array(
                        'projectID'         => $dsProject->getValue('projectID'),
                        'projectName'       => $dsProject->getValue('description'),
                        'notes'             => substr(
                            $dsProject->getValue('notes'),
                            0,
                            50
                        ),
                        'startDate'         => strftime(
                            "%d/%m/%Y",
                            strtotime($dsProject->getValue('startDate'))
                        ),
                        'expiryDate'        => strftime(
                            "%d/%m/%Y",
                            strtotime($dsProject->getValue('expiryDate'))
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
            'contactBlock',
            'contacts'
        );      // have to declare innermost block first

        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) & ($this->getAction(
                ) != CTCUSTOMER_ACT_DISP_SUCCESS)) {                                                      // Only get from DB if not displaying form error(s)
            $this->buCustomer->getSitesByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsSite,
                $_REQUEST['showInactiveSites']
            );

            $this->buCustomer->getContactsByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsContact,
                $_REQUEST['showInactiveContacts']
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


        if (!$this->formError) {// Only get from DB if not displaying form error(s)
            $thing = CTCUSTOMER_CLS_TABLE_EDIT_HEADER;
//            $site->setValue(DBESite::Add1Class,);
//            $site->setValue(DBESite::TownClass, CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
//            $site->setValue(DBESite::PostcodeClass, CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC);
        }

        //      $this->template->set_block('CustomerEdit','contacts', '');
        $addContactURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_ADDCONTACT,
                    'customerID' => $site->getValue(DBESite::customerID),
                    'siteNo'     => $site->getValue(DBESite::siteNo)
                )
            );
        // If we can delete this site set the link
        if ($this->buCustomer->canDeleteSite(
            $site->getValue(DBESite::customerID),
            $site->getValue(DBESite::siteNo)
        )) {
            $deleteSiteURL = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_DELETESITE,
                    'customerID' => $site->getValue(DBESite::customerID),
                    'siteNo'     => $site->getValue(DBESite::siteNo)
                )
            );
            $deleteSiteText = 'Delete Site';
        } else {
            $deleteSiteText = '';
        }
        //Horrible hack cause I don't understand why these are empty strings when they should be zero values!
        if ($this->dsCustomer->getValue(DBECustomer::invoiceSiteNo) == '') $this->dsCustomer->setValue(
            DBECustomer::invoiceSiteNo,
            '0'
        );
        if ($this->dsCustomer->getValue(DBECustomer::deliverSiteNo) == '') $this->dsCustomer->setValue(
            DBECustomer::deliverSiteNo,
            '0'
        );
        $this->template->set_var(
            array(
                'add1Class'      => $thing,
                'add1'           => $site->getValue(DBESite::add1),
                'add2'           => $site->getValue(DBESite::add2),
                'add3'           => $site->getValue(DBESite::add3),
                //                'townClass' => $site->getValue(DBESite::TownClass),
                'town'           => $site->getValue(DBESite::town),
                'county'         => $site->getValue(DBESite::county),
                //                'postcodeClass' => $site->getValue(DBESite::PostcodeClass),
                'postcode'       => $site->getValue(DBESite::postcode),
                'sitePhone'      => $site->getValue(DBESite::phone),
                'siteNo'         => $site->getValue(DBESite::siteNo),
                'customerID'     => $site->getValue(DBESite::customerID),
                'sageRef'        => $site->getValue(DBESite::sageRef),
                'debtorCode'     => $site->getValue(DBESite::debtorCode),
                'maxTravelHours' => $site->getValue(DBESite::maxTravelHours),

                'invoiceSiteFlagChecked' => ($this->dsCustomer->getValue(DBECustomer::invoiceSiteNo) == $site->getValue(
                        DBESite::siteNo
                    )) ? CT_CHECKED : '',
                'deliverSiteFlagChecked' => ($this->dsCustomer->getValue(DBECustomer::deliverSiteNo) == $site->getValue(
                        DBESite::siteNo
                    )) ? CT_CHECKED : '',
                'activeFlagChecked'      => ($site->getValue(DBESite::activeFlag) == 'Y') ? CT_CHECKED : '',
                'deleteSiteText'         => $deleteSiteText,
                'deleteSiteURL'          => $deleteSiteURL
            )
        );

        $this->template->set_block(
            'CustomerEdit',
            'invoiceContacts',
            ''
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
            ''
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
                ''
            );
            $this->template->set_block(
                'CustomerEdit',
                'customLetters',
                ''
            );

            if ($this->dsContact->getValue(DBEContact::contactID) == 0) { // New contact so no delete link
                $deleteContactURL = '';
                $deleteContactLink = '';
                $clientFormURL = '';
            } else {
                $deleteContactURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTCUSTOMER_ACT_DELETECONTACT,
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                        )
                    );
                $deleteContactLink =
                    '<a href="' . $deleteContactURL . '"><img align=middle border=0 hspace=2 src="images/icondelete.gif" alt="Delete contact" onClick="if(!confirm(\'Are you sure you want to delete this contact?\')) return(false)"></a>';
                $clientFormURL =
                    $this->buildLink(
                        'ClientInformationForm.php',
                        array(
                            'contactID'   => $this->dsContact->getValue(DBEContact::contactID),
                            'contactName' => $this->dsContact->getValue(
                                    DBEContact::firstName
                                ) . ' ' . $this->dsContact->getValue(DBEContact::lastName)
                        )
                    );
                $dearJohnURL =
                    $this->buildLink(
                        'DearJohnForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                        )
                    );
                $dmLetterURL =
                    $this->buildLink(
                        'DMLetterForm.php',
                        array(
                            'contactID' => $this->dsContact->getValue(DBEContact::contactID)//,
                            //                  'letterTemplate' => 'dm_letter'
                        )
                    );
            }

            $this->template->set_var(
                array(
                    'contactID'                   => $this->dsContact->getValue(DBEContact::contactID),
                    'siteNo'                      => $this->dsContact->getValue(DBEContact::siteNo),
                    'customerID'                  => $this->dsContact->getValue(DBEContact::customerID),
                    'supplierID'                  => $this->dsContact->getValue(DBEContact::supplierID),
                    'title'                       => $this->dsContact->getValue(DBEContact::title),
                    'firstName'                   => $this->dsContact->getValue(DBEContact::firstName),
                    'lastName'                    => $this->dsContact->getValue(DBEContact::lastName),
                    'firstNameClass'              => $this->dsContact->getValue('FirstNameClass'),
                    'lastNameClass'               => $this->dsContact->getValue('LastNameClass'),
                    'phone'                       => $this->dsContact->getValue(DBEContact::phone),
                    'mobilePhone'                 => $this->dsContact->getValue(DBEContact::mobilePhone),
                    'position'                    => $this->dsContact->getValue(DBEContact::position),
                    'fax'                         => $this->dsContact->getValue(DBEContact::fax),
                    'portalPassword'              => $this->dsContact->getValue(DBEContact::portalPassword),
                    'failedLoginCount'            => $this->dsContact->getValue(DBEContact::failedLoginCount),
                    'email'                       => $this->dsContact->getValue(DBEContact::email),
                    'notes'                       => $this->dsContact->getValue(DBEContact::notes),
                    'discontinuedFlag'            => $this->dsContact->getValue(DBEContact::discontinuedFlag),
                    'invoiceContactFlagChecked'   => ($this->dsContact->getValue(
                            DBEContact::contactID
                        ) == $this->dsSite->getValue(DBESite::invoiceContactID)) ? CT_CHECKED : '',
                    'deliverContactFlagChecked'   => ($this->dsContact->getValue(
                            DBEContact::contactID
                        ) == $this->dsSite->getValue(DBESite::deliverContactID)) ? CT_CHECKED : '',
                    'sendMailshotFlagChecked'     => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::sendMailshotFlag)
                    ),
                    'accountsFlagChecked'         => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::accountsFlag)
                    ),
                    'mailshot2FlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot2Flag)
                    ),
                    'mailshot3FlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot3Flag)
                    ),
                    'mailshot4FlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot4Flag)
                    ),
                    'mailshot8FlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot8Flag)
                    ),
                    'mailshot9FlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::mailshot9Flag)
                    ),
                    'workStartedEmailFlagChecked' => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::workStartedEmailFlag)
                    ),
                    'autoCloseEmailFlagChecked'   => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::autoCloseEmailFlag)
                    ),
                    'clientFormURL'               => $clientFormURL,
                    'dearJohnURL'                 => $dearJohnURL,
                    'dmLetterURL'                 => $dmLetterURL,
                    'customLetter1URL'            => $customLetter1URL,
                    'deleteContactLink'           => $deleteContactLink
                )
            );

            $this->siteDropdown(
                $this->dsContact->getValue(DBEContact::customerID),
                $this->dsContact->getValue(DBEContact::siteNo)
            );

            /*
            Display all the custom letters
            */
            foreach ($customLetterTemplates as $index => $filename) {

                $customLetterURL =
                    $this->buildLink(
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
                    $this->buildLink(
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
        if ($this->dsCustomer->getValue(DBECustomer::customerID)) {
            $this->documents(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                'CustomerEdit'
            );
        }

        $this->template->parse(
            'CONTENTS',
            'CustomerEdit',
            true
        );
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
        if ($this->buCustomer->canDeleteCustomer(
            $this->getCustomerID(),
            $this->userID
        )) {
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
            $this->buCustomer->getCustomerByID(
                $this->getCustomerID(),
                $this->dsCustomer
            );
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
        if ($this->buCustomer->canDeleteSite(
            $this->getCustomerID(),
            $this->getSiteNo()
        )) {
            $this->buCustomer->deleteSite(
                $this->getCustomerID(),
                $this->getSiteNo()
            );
        } else {
            $this->setFormError('Cannot delete this site - dependencies exist');
        }
        $nextURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCNC_ACT_DISP_EDIT,
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
        $this->buCustomer->getContactByID(
            $this->getContactID(),
            $dsContact
        );
        $this->setCustomerID($dsContact->getValue(DBEContact::customerID));

        $nextURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCNC_ACT_DISP_EDIT,
                    'customerID' => $this->getCustomerID()
                )
            );

        if ($this->buCustomer->canDeleteContact($this->getContactID())) {
            $this->buCustomer->deleteContact($this->getContactID());
        } else {
            // Display a message page.
            $this->setTemplateFiles(
                'Message',
                'Message.inc'
            );

            $message =
                'Cannot delete contact ' .
                $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(DBEContact::lastName) .
                ' because dependencies exist in the database';

            $this->template->set_var(
                array(
                    'message' => $message,
                    'nextURL' => $nextURL
                )
            );
            $this->template->parse(
                'CONTENTS',
                'Message',
                true
            );
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
        $this->setCustomerID($this->dsCustomer->getValue(DBECustomer::customerID));
        if (!$this->formError) {

            $this->buCustomer->updateCustomer($this->dsCustomer);
            $this->buCustomer->updateSite($this->dsSite);
            if (isset($this->postVars["form"]["contact"])) {
                $this->buCustomer->updateContact($this->dsContact);

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

    /**
     * Display the popup selector form
     * @access private
     */
    function displayCustomerSelectPopup()
    {
        $this->setMethodName('displayCustomerSelectPopup');
        $this->buCustomer->getCustomersByNameMatch(
            '',
            '',
            $this->getCustomerString(),
            '',
            '',
            '',
            '',
            '',
            $this->dsCustomer
        );
        if ($this->dsCustomer->rowCount() == 1) {
            $this->setTemplateFiles(
                'CustomerSelect',
                'CustomerSelectOne.inc'
            );
        }
        if ($this->dsCustomer->rowCount() == 0) {
            $this->template->set_var(
                'customerString',
                $this->getCustomerString()
            );
            $this->setTemplateFiles(
                'CustomerSelect',
                'CustomerSelectNone.inc'
            );
        }
        if ($this->dsCustomer->rowCount() > 1) {
            $this->setTemplateFiles(
                'CustomerSelect',
                'CustomerSelectPopup.inc'
            );
        }
        // fields to populate on parent page
        $this->template->set_var(
            array(
                'parentIDField'   => $_SESSION['parentIDField'],
                'parentDescField' => $_SESSION['parentDescField']
            )
        );

// Parameters
        $this->setPageTitle('Customer Selection');
        if ($this->dsCustomer->rowCount() > 0) {
            $this->template->set_block(
                'CustomerSelect',
                'customerBlock',
                'customers'
            );
            while ($this->dsCustomer->fetchNext()) {
                $this->template->set_var(
                    array(
                        'customerName' => addslashes($this->dsCustomer->getValue(DBECustomer::name)),
                        'customerID'   => $this->dsCustomer->getValue(DBECustomer::customerID)
                    )
                );
                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'CustomerSelect',
            true
        );
        $this->parsePage();
    }

    function getCustomerNotePopupLink($customerID)
    {
        if ($customerID) {
            $url =
                $this->buildLink(
                    'CustomerNote.php',
                    array(
                        'action'     => 'customerNoteHistoryPopup',
                        'customerID' => $customerID,
                        'htmlFmt'    => CT_HTML_FMT_POPUP
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
        $dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $dbeSite->getRowsByCustomerID();


        while ($dbeSite->fetchNext()) {
            $siteSelected = ($siteNo == $dbeSite->getValue(DBESite::siteNo)) ? CT_SELECTED : '';
            $siteDesc = $dbeSite->getValue(DBESite::siteNo);

            $this->template->set_var(
                array(
                    'siteSelected'   => $siteSelected,
                    'selectSiteNo'   => $dbeSite->getValue(DBESite::siteNo),
                    'selectSiteDesc' => $siteDesc
                )
            );
            $this->template->parse(
                'selectSites',
                $blockName,
                true
            );
        }

    } // end siteDropdown

    /**
     * Get and parse contact drop-down selector
     * @access private
     */
    function parseContactSelector($contactID,
                                  &$dsContact,
                                  $blockVar,
                                  $blockName
    )
    {
        $dsContact->initialise();
        while ($dsContact->fetchNext()) {
            $contactSelected = ($dsContact->getValue(DBEContact::contactID) == $contactID) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    $blockName . 'Selected'  => $contactSelected,
                    $blockName . 'ContactID' => $dsContact->getValue(DBEContact::contactID),
                    $blockName . 'FirstName' => $dsContact->getValue(DBEContact::firstName),
                    $blockName . 'LastName'  => $dsContact->getValue(DBEContact::lastName)
                )
            );
            $this->template->parse(
                $blockVar,
                $blockName,
                true
            );
        }
    }

    function documents($customerID,
                       $templateName
    )
    {
        $this->template->set_block(
            $templateName,
            'portalDocumentBlock',
            'portalDocuments'
        );

        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $buPortalCustomerDocument = new BUPortalCustomerDocument($this);
            $buPortalCustomerDocument->getDocumentsByCustomerID(
                $customerID,
                $dsPortalCustomerDocument
            );

            $urlAddDocument =
                $this->buildLink(
                    'PortalCustomerDocument.php',
                    array(
                        'action'     => 'add',
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
                            'action'                   => 'edit',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                                'portalCustomerDocumentID'
                            )
                        )
                    );

                $urlViewFile =
                    $this->buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action'                   => 'viewFile',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                                'portalCustomerDocumentID'
                            )
                        )
                    );

                $urlDeleteDocument =
                    $this->buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action'                   => 'delete',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                                'portalCustomerDocumentID'
                            )
                        )
                    );

                $this->template->set_var(
                    array(
                        'description'         => $dsPortalCustomerDocument->getValue("description"),
                        'filename'            => $dsPortalCustomerDocument->getValue("filename"),
                        'startersFormFlag'    => $dsPortalCustomerDocument->getValue("startersFormFlag"),
                        'leaversFormFlag'     => $dsPortalCustomerDocument->getValue("leaversFormFlag"),
                        'mainContactOnlyFlag' => $dsPortalCustomerDocument->getValue("mainContactOnlyFlag"),
                        'createDate'          => $dsPortalCustomerDocument->getValue("createdDate"),
                        'urlViewFile'         => $urlViewFile,
                        'urlEditDocument'     => $urlEditDocument,
                        'urlDeleteDocument'   => $urlDeleteDocument
                    )
                );
                $this->template->parse(
                    'portalDocuments',
                    'portalDocumentBlock',
                    true
                );
            } // end while

        } // end if


    }

    private function getTrueFalse($value)
    {
        return $value == 'Y';

    } // end function documents

}// end of class
?>
