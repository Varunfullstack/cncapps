<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;
use CNCLTD\SupportedCustomerAssets\UnsupportedCustomerAssetService;
use CNCLTD\Utils;

global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DBEJOrdhead.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg["path_bu"] . "/BURenBroadband.inc.php");
require_once($cfg["path_bu"] . "/BURenContract.inc.php");
require_once($cfg["path_bu"] . "/BURenQuotation.inc.php");
require_once($cfg["path_bu"] . "/BURenDomain.inc.php");
require_once($cfg["path_bu"] . "/BURenHosting.inc.php");
require_once($cfg["path_bu"] . "/BUExternalItem.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");
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
    'contactError'
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

class CTCustomer extends CTCNC
{
    const GET_CUSTOMER_REVIEW_CONTACTS               = "getCustomerReviewContacts";
    const DECRYPT                                    = "decrypt";
    const contactFormTitleClass                      = 'TitleClass';
    const contactFormFirstNameClass                  = 'FirstNameClass';                      // Used when searching for an entity by string
    const contactFormLastNameClass                   = 'LastNameClass';                      // Used when searching for an entity by string
    const contactFormEmailClass                      = 'EmailClass';                      // Used when searching for an entity by string
    const contactFormHasPassword                     = 'hasPassword';                      // Used when searching for an entity by string
    const siteFormAdd1Class                          = 'Add1Class';                      // Used when searching for an entity by string
    const siteFormTownClass                          = 'TownClass';                      // Used when searching for an entity by string
    const siteFormPostcodeClass                      = 'PostcodeClass';                      // Used when searching for an entity by string
    const customerFormNameClass                      = 'NameClass';                                // Used when searching for customer
    const customerFormInvoiceSiteMessage             = 'InvoiceSiteMessage';
    const customerFormDeliverSiteMessage             = 'DeliverSiteMessage';
    const customerFormSectorMessage                  = 'SectorMessage';
    const customerFormSpecialAttentionEndDateMessage = 'specialAttentionEndDateMessage';
    const customerFormLastReviewMeetingDateMessage   = 'lastReviewMeetingDateMessage';
    const CONTACTS_ACTION                            = "contacts";
    public $customerID;
    public $customerString;
    public $contactString;
    public $phoneString;
    public $newCustomerFromDate;
    public $newCustomerToDate;
    public $droppedCustomerFromDate;
    public $droppedCustomerToDate;
    public $address;
    public $buCustomer;
    public $customerStringMessage;
    public $dsCustomer;
    public $dsContact;
    public $dsSite;
    public $siteNo;
    public $contactID;
    public $dsHeader;
    var    $orderTypeArray = array(
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
        "2"  => "Two Monthly",
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
        $roles = [
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(303);
        $this->buCustomer = new BUCustomer($this);
        $this->dsContact  = new DataSet($this);
        $this->dsContact->copyColumnsFrom($this->buCustomer->dbeContact);
        $this->dsContact->addColumn(
            self::contactFormTitleClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsContact->addColumn(
            self::contactFormFirstNameClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsContact->addColumn(
            self::contactFormLastNameClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsContact->addColumn(
            self::contactFormEmailClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsContact->addColumn(
            self::contactFormHasPassword,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
        $this->dsSite = new DataSet($this);
        $this->dsSite->setIgnoreNULLOn();
        $this->dsSite->copyColumnsFrom($this->buCustomer->dbeSite);
        $this->dsSite->addColumn(
            self::siteFormAdd1Class,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSite->addColumn(
            self::siteFormTownClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSite->addColumn(
            self::siteFormPostcodeClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer = new DataSet($this);
        $this->dsCustomer->setIgnoreNULLOn();
        $this->dsCustomer->copyColumnsFrom($this->buCustomer->dbeCustomer);
        $this->dsCustomer->addColumn(
            self::customerFormNameClass,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            self::customerFormInvoiceSiteMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            self::customerFormDeliverSiteMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            self::customerFormSectorMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            self::customerFormSpecialAttentionEndDateMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsCustomer->addColumn(
            self::customerFormLastReviewMeetingDateMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
    }

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
        foreach ($contactArray as $value) {
            if (@$value['contactID']) {

                $dbeContact = new DBEContact($this);
                $dbeContact->getRow($value['contactID']);
                $this->dsContact->setValue(
                    DBEContact::portalPassword,
                    $dbeContact->getValue(DBEContact::portalPassword)
                );
                $this->dsContact->setValue(
                    DBEContact::active,
                    $dbeContact->getValue(DBEContact::active)
                );
            }
            $this->dsContact->setUpdateModeInsert();
            $this->dsContact->setValue(
                DBEContact::contactID,
                @$value['contactID']
            );
            $this->dsContact->setValue(
                DBEContact::customerID,
                @$value['customerID']
            );
            $this->dsContact->setValue(
                DBEContact::siteNo,
                @$value['siteNo']
            );
            $this->dsContact->setValue(
                DBEContact::linkedInURL,
                @$value['linkedInURL']
            );
            $this->dsContact->setValue(
                DBEContact::title,
                @$value['title']
            );
            $this->dsContact->setValue(
                self::contactFormTitleClass,
                null
            );
            if (!$this->dsContact->getValue(DBEContact::title)) {
                $this->setFormErrorOn();
                $this->dsContact->setValue(
                    self::contactFormTitleClass,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $this->dsContact->setValue(
                DBEContact::lastName,
                @$value['lastName']
            );
            $this->dsContact->setValue(
                self::contactFormLastNameClass,
                null
            );
            if (!$this->dsContact->getValue(DBEContact::lastName)) {
                $this->setFormErrorOn();
                $this->dsContact->setValue(
                    self::contactFormLastNameClass,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $this->dsContact->setValue(
                DBEContact::firstName,
                @$value['firstName']
            );
            $this->dsContact->setValue(
                self::contactFormFirstNameClass,
                null
            );
            if (!$this->dsContact->getValue(DBEContact::firstName)) {
                $this->setFormErrorOn();
                $this->dsContact->setValue(
                    self::contactFormFirstNameClass,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $validEmail = true;
            $email      = !@$value['email'] ? null : $value['email'];
            $this->dsContact->setValue(
                self::contactFormEmailClass,
                null
            );
            if ($email) {

                if ($this->buCustomer->duplicatedEmail(
                    $email,
                    @$value['contactID'],
                    @$value['customerID']
                )) {
                    $this->setFormErrorOn();
                    $this->dsContact->setValue(
                        self::contactFormEmailClass,
                        CTCUSTOMER_CLS_FORM_ERROR
                    );
                    $validEmail = false;
                }


            }
            if ($validEmail) {
                $this->dsContact->setValue(
                    DBEContact::email,
                    $email
                );
            }
            $this->dsContact->setValue(
                DBEContact::phone,
                @$value['phone']
            );
            $this->dsContact->setValue(
                DBEContact::notes,
                @$value['notes']
            );
            $this->dsContact->setValue(
                DBEContact::mobilePhone,
                @$value['mobilePhone']
            );
            $this->dsContact->setValue(
                DBEContact::position,
                @$value['position']
            );
            $this->dsContact->setValue(
                DBEContact::fax,
                @$value['fax']
            );
            $this->dsContact->setValue(
                DBEContact::accountsFlag,
                $this->getYN(@$value['accountsFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::supportLevel,
                @$value['supportLevel']
            );
            $this->dsContact->setValue(
                DBEContact::reviewUser,
                $this->getYN(@$value['reviewUser'])
            );
            $this->dsContact->setValue(
                DBEContact::specialAttentionContactFlag,
                $this->getYN(@$value['specialAttentionContactFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::discontinuedFlag,
                $this->getYN(@$value['discontinuedFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot2Flag,
                $this->getYN(@$value['mailshot2Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot3Flag,
                $this->getYN(@$value['mailshot3Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot4Flag,
                $this->getYN(@$value['mailshot4Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot8Flag,
                $this->getYN(@$value['mailshot8Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot9Flag,
                $this->getYN(@$value['mailshot9Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot11Flag,
                $this->getYN(@$value['mailshot11Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::initialLoggingEmailFlag,
                $this->getYN(@$value['initialLoggingEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::workStartedEmailFlag,
                $this->getYN(@$value['workStartedEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::workUpdatesEmailFlag,
                $this->getYN(@$value['workUpdatesEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::fixedEmailFlag,
                $this->getYN(@$value['fixedEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::pendingClosureEmailFlag,
                $this->getYN(@$value['pendingClosureEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::closureEmailFlag,
                $this->getYN(@$value['closureEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersInitialLoggingEmailFlag,
                $this->getYN(@$value['othersInitialLoggingEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersWorkStartedEmailFlag,
                $this->getYN(@$value['othersWorkStartedEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersWorkUpdatesEmailFlag,
                $this->getYN(@$value['othersWorkUpdatesEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersFixedEmailFlag,
                $this->getYN(@$value['othersFixedEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersPendingClosureEmailFlag,
                $this->getYN(@$value['othersPendingClosureEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::othersClosureEmailFlag,
                $this->getYN(@$value['othersClosureEmailFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::reviewUser,
                $this->getYN(@$value['reviewUser'])
            );
            $this->dsContact->setValue(
                DBEContact::hrUser,
                $this->getYN(@$value['hrUser'])
            );
            $this->dsContact->setValue(
                DBEContact::sendMailshotFlag,
                $this->getYN(@$value['sendMailshotFlag'])
            );
            $this->dsContact->setValue(
                DBEContact::failedLoginCount,
                @$value['failedLoginCount']
            );
            $this->dsContact->setValue(
                DBEContact::pendingLeaverFlag,
                $this->getYN(@$value[DBEContact::pendingLeaverFlag])
            );
            $this->dsContact->setValue(
                DBEContact::pendingLeaverDate,
                @$value[DBEContact::pendingLeaverDate]
            );
            // Determine whether a new contact is to be added
            if (!$this->dsContact->getValue(DBEContact::contactID)) {
                if (($this->dsContact->getValue(DBEContact::title)) | ($this->dsContact->getValue(
                        DBEContact::firstName
                    )) | ($this->dsContact->getValue(DBEContact::lastName))) {
                    $this->dsContact->setValue(DBEContact::active, 1);
                    $this->dsContact->post();
                }
            } else {
                $this->dsContact->post();  // Existing contact
            }
        }
    }

    function getYN($flag)
    {
        return ($flag == 'Y' ? $flag : 'N');
    }

    function setSite(&$siteArray)
    {
        if (!is_array($siteArray)) {
            return;
        }
        foreach ($siteArray as $key => $value) {
            $this->dsSite->setUpdateModeInsert();
            $this->dsSite->setValue(
                self::siteFormAdd1Class,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER
            );
            $this->dsSite->setValue(
                self::siteFormTownClass,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC
            );
            $this->dsSite->setValue(
                self::siteFormPostcodeClass,
                CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC
            );
            $this->dsSite->setValue(
                DBESite::customerID,
                @$value['customerID']
            );
            $this->dsSite->setValue(
                DBESite::siteNo,
                @$value['siteNo']
            );
            $this->dsSite->setValue(
                DBESite::add1,
                @$value['add1']
            );
            $this->dsSite->setValue(
                DBESite::what3Words,
                @$value[DBESite::what3Words]
            );
            if (!$this->dsSite->getValue(DBESite::add1)) {
                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    self::siteFormAdd1Class,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $this->dsSite->setValue(
                DBESite::add2,
                @$value['add2']
            );
            $this->dsSite->setValue(
                DBESite::add3,
                @$value['add3']
            );
            $this->dsSite->setValue(
                DBESite::town,
                strtoupper(@$value['town'])
            );
            if (!$this->dsSite->getValue(DBESite::town)) {
                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    self::siteFormTownClass,
                    CTCUSTOMER_CLS_FORM_ERROR_UC
                );
            }
            $this->dsSite->setValue(
                DBESite::county,
                @$value['county']
            );
            $this->dsSite->setValue(
                DBESite::postcode,
                strtoupper(@$value['postcode'])
            );
            if (!$this->dsSite->getValue(DBESite::postcode)) {
                $this->setFormErrorOn();
                $this->dsSite->setValue(
                    self::siteFormPostcodeClass,
                    CTCUSTOMER_CLS_FORM_ERROR_UC
                );
            }
            $this->dsSite->setValue(
                DBESite::phone,
                @$value['phone']
            );
            $this->dsSite->setValue(
                DBESite::maxTravelHours,
                @$value['maxTravelHours']
            );
            $this->dsSite->setValue(
                DBESite::invoiceContactID,
                @$value['invoiceContactID']
            );
            $this->dsSite->setValue(
                DBESite::deliverContactID,
                @$value['deliverContactID']
            );
            $this->dsSite->setValue(
                DBESite::sageRef,
                @$value['sageRef']
            );
            $this->dsSite->setValue(
                DBESite::debtorCode,
                @$value['debtorCode']
            );
            $this->dsSite->setValue(
                DBESite::nonUKFlag,
                $this->getYN(@$value['nonUKFlag'])
            );
            $this->dsSite->setValue(
                DBESite::activeFlag,
                $this->getYN(@$value['activeFlag'])
            );
            $this->dsSite->post();
        }
    }

    function setCustomer(&$customerArray)
    {
        if (!is_array($customerArray)) {
            return;
        }
        foreach ($customerArray as $key => $value) {
            $this->dsCustomer->setUpdateModeInsert();
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
            $this->dsCustomer->setValue(
                DBECustomer::customerID,
                @$value['customerID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::name,
                @$value['name']
            );
            if (!$this->dsCustomer->getValue(DBECustomer::name)) {
                $this->setFormErrorOn();
                $this->dsCustomer->setValue(
                    self::customerFormNameClass,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }
            $this->dsCustomer->setValue(
                DBECustomer::reviewMeetingBooked,
                !!@$value['reviewMeetingBooked']
            );
            $this->dsCustomer->setValue(
                DBECustomer::inclusiveOOHCallOuts,
                @$value['inclusiveOOHCallOuts']
            );
            $this->dsCustomer->setValue(
                DBECustomer::regNo,
                @$value['regNo']
            );
            $this->dsCustomer->setValue(
                DBECustomer::invoiceSiteNo,
                @$value['invoiceSiteNo']
            );
            $this->dsCustomer->setValue(
                DBECustomer::websiteURL,
                @$value['websiteURL']
            );
            $this->dsCustomer->setValue(
                DBECustomer::deliverSiteNo,
                @$value['deliverSiteNo']
            );
            $this->dsCustomer->setValue(
                DBECustomer::mailshotFlag,
                $this->getYN(@$value['mailshotFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::referredFlag,
                $this->getYN(@$value['referredFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::specialAttentionFlag,
                $this->getYN(@$value['specialAttentionFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::specialAttentionEndDate,
                @$value['specialAttentionEndDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP1PenaltiesAgreed,
                @$value['slaP1PenaltiesAgreed']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP2PenaltiesAgreed,
                @$value['slaP2PenaltiesAgreed']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP3PenaltiesAgreed,
                @$value['slaP3PenaltiesAgreed']
            );
            if ($this->dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' && !$this->dsCustomer->getValue(
                    DBECustomer::specialAttentionEndDate
                )) {
                $this->dsCustomer->setValue(
                    self::customerFormSpecialAttentionEndDateMessage,
                    'You must enter a date'
                );
                $this->setFormErrorOn();
            }
            $this->dsCustomer->setValue(
                DBECustomer::primaryMainContactID,
                @$value[DBECustomer::primaryMainContactID]
            );
            $this->dsCustomer->setValue(
                DBECustomer::customerTypeID,
                @$value['customerTypeID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::support24HourFlag,
                $this->getYN(@$value['support24HourFlag'])
            );
            $this->dsCustomer->setValue(
                self::customerFormSectorMessage,
                null
            );
            if (!@$value['sectorID']) {
                $this->setFormErrorOn();
                $this->dsCustomer->setValue(
                    self::customerFormSectorMessage,
                    'Required'
                );
            }
            $this->dsCustomer->setValue(
                DBECustomer::sectorID,
                @$value['sectorID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::leadStatusId,
                @$value['leadStatusId']
            );
            $this->dsCustomer->setValue(
                DBECustomer::createDate,
                @$value['createDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::gscTopUpAmount,
                @$value['gscTopUpAmount']
            );
            $this->dsCustomer->setValue(
                DBECustomer::becameCustomerDate,
                @$value['becameCustomerDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::droppedCustomerDate,
                @$value['droppedCustomerDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::lastReviewMeetingDate,
                @$value['lastReviewMeetingDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewMeetingFrequencyMonths,
                @$value['reviewMeetingFrequencyMonths']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewDate,
                @$value['reviewDate']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewMeetingEmailSentFlag,
                $this->getYN(@$value['reviewMeetingEmailSentFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewAction,
                @$value['reviewAction']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewUserID,
                @$value['reviewUserID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::accountManagerUserID,
                @$value['accountManagerUserID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewTime,
                @$value['reviewTime']
            );
            $this->dsCustomer->setValue(
                DBECustomer::noOfServers,
                @$value['noOfServers']
            );
            $this->dsCustomer->setValue(
                DBECustomer::activeDirectoryName,
                @$value['activeDirectoryName']
            );
            $this->dsCustomer->setValue(
                DBECustomer::noOfPCs,
                @$value['noOfPCs']
            );
            $this->dsCustomer->setValue(
                DBECustomer::noOfSites,
                @$value['noOfSites']
            );
            $this->dsCustomer->setValue(
                DBECustomer::comments,
                @$value['comments']
            );
            $this->dsCustomer->setValue(
                DBECustomer::techNotes,
                @$value['techNotes']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP1,
                @$value['slaP1']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP2,
                @$value['slaP2']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP3,
                @$value['slaP3']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP4,
                @$value['slaP4']
            );
            $this->dsCustomer->setValue(
                DBECustomer::slaP5,
                @$value['slaP5']
            );
            $this->dsCustomer->setValue(DBECustomer::slaFixHoursP1, @$value['slaFixHoursP1']);
            $this->dsCustomer->setValue(DBECustomer::slaFixHoursP2, @$value['slaFixHoursP2']);
            $this->dsCustomer->setValue(DBECustomer::slaFixHoursP3, @$value['slaFixHoursP3']);
            $this->dsCustomer->setValue(DBECustomer::slaFixHoursP4, @$value['slaFixHoursP4']);
            $this->dsCustomer->setValue(
                DBECustomer::pcxFlag,
                $this->getYN(@$value['pcxFlag'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::sortCode,
                @$value['sortCode']
            );
            if (isset($value['newSortCode'])) {
                $sortCode = null;
                if ($value['newSortCode']) {
                    $sortCode = Encryption::encrypt(
                        CUSTOMERS_ENCRYPTION_PUBLIC_KEY,
                        $value['newSortCode']
                    );
                }
                $this->dsCustomer->setValue(
                    DBECustomer::sortCode,
                    $sortCode
                );
            }
            $this->dsCustomer->setValue(
                DBECustomer::accountName,
                @$value['accountName']
            );
            $this->dsCustomer->setValue(
                DBECustomer::streamOneEmail,
                @$value['streamOneEmail']
            );
            $this->dsCustomer->setValue(
                DBECustomer::accountNumber,
                @$value['accountNumber']
            );
            if (isset($value['newAccountNumber'])) {
                $accountNumber = null;
                if ($value['newAccountNumber']) {
                    $accountNumber = Encryption::encrypt(
                        CUSTOMERS_ENCRYPTION_PUBLIC_KEY,
                        $value['newAccountNumber']
                    );
                }
                $this->dsCustomer->setValue(
                    DBECustomer::accountNumber,
                    $accountNumber
                );
            }
            $this->dsCustomer->post();
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
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
            case CTCUSTOMER_ACT_ADDCUSTOMER:
            case CTCUSTOMER_ACT_ADDSITE:
            case CTCUSTOMER_ACT_ADDCONTACT:
            case CTCUSTOMER_ACT_DISP_SUCCESS:
            case CTCNC_ACT_DISP_EDIT:
                $this->displayEditForm();
                break;
            case CTCUSTOMER_ACT_DELETECONTACT:
                $this->deleteContact();
                break;
            case CTCUSTOMER_ACT_DELETESITE:
                $this->deleteSite();
                break;
            case CTCUSTOMER_ACT_DELETECUSTOMER:
                $this->deleteCustomer();
                break;
            case CTCUSTOMER_ACT_DISP_CUST_POPUP:
                $this->displayCustomerSelectPopup();
                break;
            case 'saveContactPassword':
                $response = [];
                try {
                    $this->saveContactPassword();
                    $response["status"] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response["status"] = "error";
                    $response["error"]  = $exception->getMessage();
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
                    $response["error"]  = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::GET_CUSTOMER_REVIEW_CONTACTS:
                $response = [];
                try {
                    $response['data']   = $this->getCustomerReviewContacts();
                    $response["status"] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response["status"] = "error";
                    $response["error"]  = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::DECRYPT:
                $response = ["status" => "ok"];
                try {
                    $response['decryptedData'] = Encryption::decrypt(
                        CUSTOMERS_ENCRYPTION_PRIVATE_KEY,
                        @$this->getParam('passphrase'),
                        @$this->getParam('encryptedData')
                    );
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error']  = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case "removeSupportAndRefer":
                $customerID = @$this->getParam('customerID');
                $response   = ['status' => 'ok'];
                try {
                    $this->removeSupportForAllUsersAndReferCustomer($customerID);
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error']  = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case 'searchName':
                $itemsPerPage = 20;
                $page         = 1;
                $term         = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }
                if (isset($_REQUEST['itemsPerPage'])) {
                    $itemsPerPage = $_REQUEST['itemsPerPage'];
                }
                if (isset($_REQUEST['page'])) {
                    $page = $_REQUEST['page'];
                }
                $dsResult   = new DataSet($this);
                $buCustomer = new BUCustomer($this);
                $buCustomer->getActiveCustomers($dsResult);
                $customers = [];
                $buCustomer->getCustomersByNameMatch($dsResult, null, null, $term);
                while ($dsResult->fetchNext()) {
                    $customers[] = [
                        "id"             => $dsResult->getValue(DBECustomer::customerID),
                        "name"           => $dsResult->getValue(DBECustomer::name),
                        "streamOneEmail" => $dsResult->getValue(DBECustomer::streamOneEmail),
                    ];
                }
                echo json_encode($customers);
                break;
            case "getCurrentUser":
                echo $this->getCurrentUser();
                exit;
            case "searchCustomers":
                echo json_encode($this->searchCustomers(), JSON_NUMERIC_CHECK);
                exit;
            case "getCustomerSR":
                echo json_encode($this->getCustomerSR());
                exit;
                break;
            case "getCustomerSites":
                echo json_encode($this->getCustomerSites());
                exit;
                break;
            case "getCustomerAssets":
                echo json_encode($this->getCustomerAssets());
                exit;
                break;
            case self::CONTACTS_ACTION:
                echo json_encode($this->getCustomerContacts());
                exit;
                break;
            case "projects":
                echo json_encode($this->getCustomerProjects());
                exit;
                break;
            case "contracts":
                echo json_encode($this->getCustomerContracts());
                exit;
            case "getCustomersHaveOpenSR":
                echo json_encode($this->getCustomersHaveOpenSR());
                exit;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if ($this->getParam('parentIDField')) {
            $this->setSessionParam('parentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('parentDescField', $this->getParam('parentDescField'));
        }
    }

    /**
     * when customer folder link is clicked we call this routine which first checks
     * to see whether the folder exists. If not, it is created.
     * @access private
     * @throws Exception
     */
    function createCustomerFolder()
    {
        $this->setMethodName('createCustomerFolder');
        if (!$this->getCustomerID()) {
            $this->displayFatalError('CustomerID not passed');
        }
        $this->buCustomer->createCustomerFolder($this->getCustomerID());
        $nextURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'     => CTCNC_ACT_DISP_EDIT,
                'customerID' => $this->getCustomerID()
            )
        );
        header('Location: ' . $nextURL);
        exit;

    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar(
            'customerID',
            $customerID
        );
    }

    /**
     * Displays next prospect to review (if any)
     *
     * @throws Exception
     */
    function displayNextReviewProspect()
    {
        $this->setMethodName('displayNextReviewProspect');
        $dsCustomer = new DataSet($this);
        if ($this->buCustomer->getNextReviewProspect($dsCustomer)) {

            $nextURL = Controller::buildLink(
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
     * @throws Exception
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
        $dsCustomer = new DataSet($this);
        if ($this->buCustomer->getDailyCallList($this, $dsCustomer)) {

            $buUser = new BUUser($this);
            while ($dsCustomer->fetchNext()) {

                $linkURL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'displayEditForm',
                        'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                    )
                );
                if ($dsCustomer->getValue(DBECustomer::reviewUserID)) {
                    $dsUser = new DataSet($this);
                    $buUser->getUserByID(
                        $dsCustomer->getValue(DBECustomer::reviewUserID),
                        $dsUser
                    );
                    $user = $dsUser->getValue(DBEJUser::name);
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
     * Search for customers using customerString
     * @access private
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
// Parameter validation
        if (!$this->buCustomer->getCustomersByNameMatch(
            $this->dsCustomer,
            $this->getContactString(),
            $this->getPhoneString(),
            $this->getCustomerString(),
            $this->getAddress(),
            $this->getNewCustomerFromDate(),
            $this->getNewCustomerToDate(),
            $this->getDroppedCustomerFromDate(),
            $this->getDroppedCustomerToDate()
        )) {
            $this->setCustomerStringMessage(CTCUSTOMER_MSG_NONE_FND);
        }
        if (($this->formError) || ($this->dsCustomer->rowCount() > 1)) {
            $this->displaySearchForm();
        } else {
            // reload with this customer
            $nextURL = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCNC_ACT_DISP_EDIT,
                    'customerID' => $this->dsCustomer->getValue(DBECustomer::customerID)
                )
            );
            header('Location: ' . $nextURL);
            exit;

        }
    }

    function getContactString()
    {
        return $this->contactString;
    }

    function setContactString($contactString)
    {
        $this->contactString = $contactString;
    }

    function getPhoneString()
    {
        return $this->phoneString;
    }

    function setPhoneString($phoneString)
    {
        $this->phoneString = $phoneString;
    }

    function getCustomerString()
    {
        return $this->customerString;
    }

    function setCustomerString($customerString)
    {
        $this->customerString = $customerString;
    }

    function getAddress()
    {
        return $this->address;
    }

    function setAddress($address)
    {
        $this->address = $address;
    }

    function getNewCustomerFromDate()
    {
        return $this->newCustomerFromDate;
    }

    function setNewCustomerFromDate($newCustomerFromDate)
    {
        $this->newCustomerFromDate = $newCustomerFromDate;
    }

    function getNewCustomerToDate()
    {
        return $this->newCustomerToDate;
    }

    function setNewCustomerToDate($newCustomerToDate)
    {
        $this->newCustomerToDate = $newCustomerToDate;
    }

    function getDroppedCustomerFromDate()
    {
        return $this->droppedCustomerFromDate;
    }

    function setDroppedCustomerFromDate($droppedCustomerFromDate)
    {
        $this->droppedCustomerFromDate = $droppedCustomerFromDate;
    }

    function getDroppedCustomerToDate()
    {
        return $this->droppedCustomerToDate;
    }

    function setDroppedCustomerToDate($droppedCustomerToDate)
    {
        $this->droppedCustomerToDate = $droppedCustomerToDate;
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     * @throws Exception
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
        $submitURL        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_SEARCH)
        );
        $createURL        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_ADDCUSTOMER)
        );
        $customerPopupURL = Controller::buildLink(
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
                $customerURL = Controller::buildLink(
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

    function getCustomerStringMessage()
    {
        return $this->customerStringMessage;
    }

    function setCustomerStringMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->customerStringMessage = $message;
    }

    /**
     * Form for editing customer details
     * @access private
     * @throws Exception
     */
    function displayEditForm()
    {
        $this->setMethodName('displayEditForm');
        $deleteCustomerURL  = null;
        $deleteCustomerText = null;
        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {
            if ((!$this->formError) && ($this->getAction(
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
                $deleteCustomerURL  = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTCUSTOMER_ACT_DELETECUSTOMER,
                        'customerID' => $this->getCustomerID()
                    )
                );
                $deleteCustomerText = 'Delete Customer';
            }
        } else {
            $this->dsCustomer->clearRows();
            // Creating a new customer - creates new row on dataset, NOT on the database yet
            $this->dsSite->clearRows();
            $this->dsContact->clearRows();
            $this->buCustomer->addNewCustomerRow($this->dsCustomer);
        }
        $this->setTemplateFiles(
            'CustomerEdit',
            'CustomerEditSimple.inc'
        );
// Parameters
        $title = "Customer - " . $this->dsCustomer->getValue(DBECustomer::name);
        $color = "red";
        if ($this->dsCustomer->getValue(DBECustomer::websiteURL)) {
            $color = "green";
        }
        $this->setPageTitle(
            $title,
            $title . ' <i class="fas fa-globe" onclick="checkWebsite()" style="color:' . $color . '"></i>'
        );
        if ($this->getParam('save_page')) {
            $this->setSessionParam('save_page', $this->getParam('save_page'));
        } else {
            $this->setSessionParam('save_page', false);
        }
        $submitURL = Controller::buildLink(
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
        $addSiteURL = Controller::buildLink(
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
        /*
        Get the list of custom letter template file names from the custom letter directory
        */
        $dir                   = LETTER_TEMPLATE_DIR . "/custom/";
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
        $customerFolderLink = null;
        if (!$this->buCustomer->customerFolderExists(
            $this->dsCustomer->getValue(DBECustomer::customerID)
        )) {
            $urlCreateCustomerFolder = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'createCustomerFolder',
                    'customerID' => $this->getCustomerID(),
                )
            );
            $customerFolderLink      = '<a href="' . $urlCreateCustomerFolder . '" title="Create Folder">Create Customer Folder</a>';
        }
        $renewalLinkURL          = Controller::buildLink(
            'RenewalReport.php',
            array(
                'action'     => 'produceReport',
                'customerID' => $this->getCustomerID()
            )
        );
        $renewalLink             = '<a href="' . $renewalLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';

        $thirdPartyLinkURL       = Controller::buildLink(
            'ThirdPartyContact.php',
            [
                'action'     => 'list',
                'customerID' => $this->getCustomerID()
            ]
        );
        $thirdPartyLink          = '<a href="' . $thirdPartyLinkURL . '" target="_blank" title="Third Party Contacts">Third Party Contacts</a>';
        $showInactiveContactsURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'               => 'dispEdit',
                'customerID'           => $this->getCustomerID(),
                'showInactiveContacts' => '1'
            )
        );
        $showInactiveSitesURL    = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'            => 'dispEdit',
                'customerID'        => $this->getCustomerID(),
                'showInactiveSites' => '1'
            )
        );
        $bodyTagExtras           = 'onLoad="loadNote(\'last\')"';
        $urlContactPopup         = Controller::buildLink(
            CTCNC_PAGE_CONTACT,
            array(
//          'action' => CTCNC_ACT_CONTACT_EDIT,
'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $mainContacts            = [];
        if ($this->dsCustomer->getValue(DBECustomer::customerID)) {
            $mainContacts = $this->buCustomer->getMainSupportContacts(
                $this->dsCustomer->getValue(DBECustomer::customerID)
            );
        }
        $this->template->set_block(
            'CustomerEdit',
            'primaryMainContactBlock',
            'primaryMainContacts'
        );
        foreach ($mainContacts as $mainContact) {
            $this->template->set_var(
                array(
                    'primaryMainContactValue'       => $mainContact[DBEContact::contactID],
                    'primaryMainContactDescription' => $mainContact[DBEContact::firstName] . " " . $mainContact[DBEContact::lastName],
                    'primaryMainContactSelected'    => $mainContact[DBEContact::contactID] == $this->dsCustomer->getValue(
                        DBECustomer::primaryMainContactID
                    ) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'primaryMainContacts',
                'primaryMainContactBlock',
                true
            );
        }
        $buItem           = new BUCustomerItem($this);
        $forceDirectDebit = false;
        if ($this->dsCustomer->getValue(DBECustomer::customerID)) {
            $forceDirectDebit = $buItem->clientHasDirectDebit($this->dsCustomer->getValue(DBECustomer::customerID));
        }
        $this->template->set_var(
            array(
                'lastContractSent'                        => $this->dsCustomer->getValue(DBECustomer::lastContractSent),
                'urlContactPopup'                         => $urlContactPopup,
                'bodyTagExtras'                           => $bodyTagExtras,
                /* hidden */ 'reviewMeetingEmailSentFlag' => $this->dsCustomer->getValue(
                DBECustomer::reviewMeetingEmailSentFlag
            ),
                'customerNotePopupLink'                   => $this->getCustomerNotePopupLink($this->getCustomerID()),
                'showInactiveContactsURL'                 => $showInactiveContactsURL,
                'showInactiveSitesURL'                    => $showInactiveSitesURL,
                'customerID'                              => $this->getCustomerID() ? $this->getCustomerID() : 'null',
                'customerName'                            => $this->dsCustomer->getValue(DBECustomer::name),
                'reviewCount'                             => $this->buCustomer->getReviewCount(),
                'customerFolderLink'                      => $customerFolderLink,
                'websiteURL'                              => $this->dsCustomer->getValue(DBECustomer::websiteURL),
                'customerNameClass'                       => $this->dsCustomer->getValue(self::customerFormNameClass),
                'SectorMessage'                           => $this->dsCustomer->getValue(
                    self::customerFormSectorMessage
                ),
                'regNo'                                   => $this->dsCustomer->getValue(DBECustomer::regNo),
                'mailshotFlagChecked'                     => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::mailshotFlag)
                ),
                'excludeFromWebrootChecksChecked'         => $this->dsCustomer->getValue(
                    DBECustomer::excludeFromWebrootChecks
                ) ? 'checked' : '',
                'referredFlagChecked'                     => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::referredFlag)
                ),
                'specialAttentionFlagChecked'             => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::specialAttentionFlag)
                ),
                'specialAttentionEndDate'                 => $this->dsCustomer->getValue(
                    DBECustomer::specialAttentionEndDate
                ),
                'specialAttentionEndDateMessage'          => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'lastReviewMeetingDate'                   => $this->dsCustomer->getValue(
                    DBECustomer::lastReviewMeetingDate
                ),
                'lastReviewMeetingDateMessage'            => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'reviewMeetingBookedChecked'              => $this->dsCustomer->getValue(
                    DBECustomer::reviewMeetingBooked
                ) ? 'checked' : null,
                'inclusiveOOHCallOuts'                    => $this->dsCustomer->getValue(
                    DBECustomer::inclusiveOOHCallOuts
                ),
                'support24HourFlagChecked'                => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::support24HourFlag)
                ),
                'pcxFlagChecked'                          => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::pcxFlag)
                ),
                'createDate'                              => $this->dsCustomer->getValue(DBECustomer::createDate),
                'mailshot2FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot2FlagDesc
                ),
                'mailshot3FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot3FlagDesc
                ),
                'mailshot4FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot4FlagDesc
                ),
                'mailshot8FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot8FlagDesc
                ),
                'mailshot9FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot9FlagDesc
                ),
                'mailshot11FlagDesc'                      => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot11FlagDesc
                ),
                'submitURL'                               => $submitURL,
                'renewalLink'                             => $renewalLink,
                'thirdPartyContactsLink'                  => $thirdPartyLink,
                'deleteCustomerURL'                       => $deleteCustomerURL,
                'deleteCustomerText'                      => $deleteCustomerText,
                'cancelURL'                               => $cancelURL,
                'disabled'                                => $this->hasPermissions(
                    SALES_PERMISSION
                ) ? null : CTCNC_HTML_DISABLED,
                'gscTopUpAmount'                          => $this->dsCustomer->getValue(DBECustomer::gscTopUpAmount),
                'noOfServers'                             => $this->dsCustomer->getValue(DBECustomer::noOfServers),
                'activeDirectoryName'                     => $this->dsCustomer->getValue(
                    DBECustomer::activeDirectoryName
                ),
                'noOfSites'                               => $this->dsCustomer->getValue(DBECustomer::noOfSites),
                'noOfPCs'                                 => $this->dsCustomer->getValue(DBECustomer::noOfPCs),
                'patchManagementEligibleComputers'        => $this->dsCustomer->getValue(
                    DBECustomer::eligiblePatchManagement
                ),
                'modifyDate'                              => $this->dsCustomer->getValue(DBECustomer::modifyDate),
                'reviewDate'                              => $this->dsCustomer->getValue(DBECustomer::reviewDate),
                'reviewTime'                              => $this->dsCustomer->getValue(DBECustomer::reviewTime),
                'becameCustomerDate'                      => $this->dsCustomer->getValue(
                    DBECustomer::becameCustomerDate
                ),
                'droppedCustomerDate'                     => $this->dsCustomer->getValue(
                    DBECustomer::droppedCustomerDate
                ),
                'reviewAction'                            => $this->dsCustomer->getValue(DBECustomer::reviewAction),
                'comments'                                => $this->dsCustomer->getValue(DBECustomer::comments),
                'techNotes'                               => $this->dsCustomer->getValue(DBECustomer::techNotes),
                'slaP1'                                   => $this->dsCustomer->getValue(DBECustomer::slaP1),
                'slaP2'                                   => $this->dsCustomer->getValue(DBECustomer::slaP2),
                'slaP3'                                   => $this->dsCustomer->getValue(DBECustomer::slaP3),
                'slaP4'                                   => $this->dsCustomer->getValue(DBECustomer::slaP4),
                'slaP5'                                   => $this->dsCustomer->getValue(DBECustomer::slaP5),
                'slaP1PenaltiesAgreedChecked'             => $this->dsCustomer->getValue(
                    DBECustomer::slaP1PenaltiesAgreed
                ) ? 'checked' : null,
                'slaP2PenaltiesAgreedChecked'             => $this->dsCustomer->getValue(
                    DBECustomer::slaP2PenaltiesAgreed
                ) ? 'checked' : null,
                'slaP3PenaltiesAgreedChecked'             => $this->dsCustomer->getValue(
                    DBECustomer::slaP3PenaltiesAgreed
                ) ? 'checked' : null,
                'slaFixHoursP1'                           => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP1),
                'slaFixHoursP2'                           => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP2),
                'slaFixHoursP3'                           => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP3),
                'slaFixHoursP4'                           => $this->dsCustomer->getValue(DBECustomer::slaFixHoursP4),
                'isShowingInactive'                       => $this->getParam('showInactiveContacts') ? 'true' : 'false',
                'primaryMainMandatory'                    => count($mainContacts) ? 'required' : null,
                'sortCode'                                => $this->dsCustomer->getValue(DBECustomer::sortCode),
                'accountName'                             => $this->dsCustomer->getValue(DBECustomer::accountName),
                'accountNumber'                           => $this->dsCustomer->getValue(DBECustomer::accountNumber),
                'sortCodePencilColor'                     => $this->dsCustomer->getValue(
                    DBECustomer::sortCode
                ) ? "greenPencil" : "redPencil",
                'accountNumberPencilColor'                => $this->dsCustomer->getValue(
                    DBECustomer::accountNumber
                ) ? "greenPencil" : "redPencil",
                'forceDirectDebit'                        => $forceDirectDebit ? 'true' : 'false',
                'streamOneEmail'                          => $this->dsCustomer->getValue(DBECustomer::streamOneEmail)
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
        $addProjectURL = Controller::buildLink(
            'Projects.php',
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
                    $deleteProjectLink = Controller::buildLink(
                        'Project.php',
                        array(
                            'action'    => 'delete',
                            'projectID' => $dsProject->getValue(DBEProject::projectID)
                        )
                    );
                    $deleteProjectText = 'delete';
                }
                $editProjectLink = Controller::buildLink(
                    'Projects.php',
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
        if ((!$this->formError) & ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) & ($this->getAction(
                ) != CTCUSTOMER_ACT_DISP_SUCCESS)) {
            // Only get from DB if not displaying form error(s)
            $this->buCustomer->getSitesByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsSite,
                $this->getParam('showInactiveSites')
            );
            $this->buCustomer->getContactsByCustomerID(
                $this->dsCustomer->getValue(DBECustomer::customerID),
                $this->dsContact,
                $this->getParam('showInactiveContacts')
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
        while ($this->dsSite->fetchNext()) {
            if (!$this->formError) {                                                      // Only get from DB if not displaying form error(s)
                $this->dsSite->setValue(
                    self::siteFormAdd1Class,
                    CTCUSTOMER_CLS_TABLE_EDIT_HEADER
                );
                $this->dsSite->setValue(
                    self::siteFormTownClass,
                    CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC
                );
                $this->dsSite->setValue(
                    self::siteFormPostcodeClass,
                    CTCUSTOMER_CLS_TABLE_EDIT_HEADER_UC
                );
            }
            $addContactURL  = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => CTCUSTOMER_ACT_ADDCONTACT,
                    'customerID' => $this->dsSite->getValue(DBESite::customerID),
                    'siteNo'     => $this->dsSite->getValue(DBESite::siteNo)
                )
            );
            $deleteSiteURL  = null;
            $deleteSiteText = null;
            // If we can delete this site set the link
            if ($this->buCustomer->canDeleteSite(
                $this->dsSite->getValue(DBESite::customerID),
                $this->dsSite->getValue(DBESite::siteNo)
            )) {
                $deleteSiteURL  = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTCUSTOMER_ACT_DELETESITE,
                        'customerID' => $this->dsSite->getValue(DBESite::customerID),
                        'siteNo'     => $this->dsSite->getValue(DBESite::siteNo)
                    )
                );
                $deleteSiteText = 'Delete Site';
            }
            //Horrible hack cause I don't understand why these are empty strings when they should be zero values!
            if (!$this->dsCustomer->getValue(DBECustomer::invoiceSiteNo)) $this->dsCustomer->setValue(
                DBECustomer::invoiceSiteNo,
                0
            );
            if (!$this->dsCustomer->getValue(DBECustomer::deliverSiteNo)) $this->dsCustomer->setValue(
                DBECustomer::deliverSiteNo,
                0
            );
            $this->template->set_var(
                array(
                    'add1Class'              => $this->dsSite->getValue(self::siteFormAdd1Class),
                    'add1'                   => $this->dsSite->getValue(DBESite::add1),
                    'add2'                   => $this->dsSite->getValue(DBESite::add2),
                    'add3'                   => $this->dsSite->getValue(DBESite::add3),
                    'townClass'              => $this->dsSite->getValue(self::siteFormTownClass),
                    'town'                   => $this->dsSite->getValue(DBESite::town),
                    'county'                 => $this->dsSite->getValue(DBESite::county),
                    'postcodeClass'          => $this->dsSite->getValue(self::siteFormPostcodeClass),
                    'postcode'               => $this->dsSite->getValue(DBESite::postcode),
                    'what3Words'             => $this->dsSite->getValue(DBESite::what3Words),
                    'sitePhone'              => $this->dsSite->getValue(DBESite::phone),
                    'siteNo'                 => $this->dsSite->getValue(DBESite::siteNo),
                    'customerID'             => $this->dsSite->getValue(DBESite::customerID),
                    'sageRef'                => $this->dsSite->getValue(DBESite::sageRef),
                    'debtorCode'             => $this->dsSite->getValue(DBESite::debtorCode),
                    'maxTravelHours'         => $this->dsSite->getValue(DBESite::maxTravelHours),
                    'invoiceSiteFlagChecked' => ($this->dsCustomer->getValue(
                            DBECustomer::invoiceSiteNo
                        ) == $this->dsSite->getValue(DBESite::siteNo)) ? CT_CHECKED : null,
                    'deliverSiteFlagChecked' => ($this->dsCustomer->getValue(
                            DBECustomer::deliverSiteNo
                        ) == $this->dsSite->getValue(DBESite::siteNo)) ? CT_CHECKED : null,
                    'activeFlagChecked'      => ($this->dsSite->getValue(
                            DBESite::activeFlag
                        ) == 'Y') ? CT_CHECKED : null,
                    'nonUKFlagChecked'       => ($this->dsSite->getValue(
                            DBESite::nonUKFlag
                        ) == 'Y') ? CT_CHECKED : null,
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
                $this->dsSite->getValue(DBESite::invoiceContactID),
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
                $this->dsSite->getValue(DBESite::deliverContactID),
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
        }
        $this->template->set_block(
            'CustomerEdit',
            'customLetterBlock',
            'customLetters'
        );      //
        $this->template->set_block(
            'CustomerEdit',
            'templateCustomLetterBlock',
            'templateCustomLetters'
        );
        $this->template->set_block(
            'CustomerEdit',
            'selectSiteBlock',
            'selectSites'
        );
        $this->template->set_block(
            'CustomerEdit',
            'templateSelectSiteBlock',
            'templateSelectSites'
        );
        $this->template->set_block(
            'CustomerEdit',
            'supportLevelBlock',
            'selectSupportLevel'
        );
        $this->template->set_block(
            'CustomerEdit',
            'templateSupportLevelBlock',
            'templateSelectSupportLevel'
        );
        $this->template->set_block(
            'CustomerEdit',
            'contactBlock',
            'contacts'
        );      // have to declare innermost block first
        $this->dsContact->initialise();
        $this->dsContact->sortAscending(DBEContact::lastName);
        $this->template->set_block(
            'CustomerEdit',
            'templateSelectSites',
            null
        );
        $this->template->set_block(
            'CustomerEdit',
            'templateSelectSupportLevel',
            null
        );
        $this->template->set_block(
            'CustomerEdit',
            'templateCustomLetters',
            null
        );
        $this->siteDropdown(
            $this->dsCustomer->getValue(DBECustomer::customerID),
            null,
            'templateSelectSites',
            'templateSelectSiteBlock'
        );
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
        /*
        Display all the custom letters
        */
        foreach ($customLetterTemplates as $index => $filename) {

            $customLetterURL = Controller::buildLink(
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
        $mainCount       = 0;
        $supervisorCount = 0;
        $supportCount    = 0;
        $delegateCount   = 0;
        $furloughCount   = 0;
        $noLevelCount    = 0;
        $totalCount      = 0;
        while ($this->dsContact->fetchNext()) {

            $this->template->set_block(
                'CustomerEdit',
                'selectSites',
                null
            );
            $this->template->set_block(
                'CustomerEdit',
                'selectSupportLevel',
                null
            );
            $this->template->set_block(
                'CustomerEdit',
                'customLetters',
                null
            );
            $dearJohnURL       = null;
            $dmLetterURL       = null;
            $deleteContactLink = null;
            if ($this->dsContact->getValue(DBEContact::contactID)) {


                switch ($this->dsContact->getValue(DBEContact::supportLevel)) {
                    case 'main':
                        $mainCount++;
                        break;
                    case 'supervisor':
                        $supervisorCount++;
                        break;
                    case 'support':
                        $supportCount++;
                        break;
                    case 'delegate':
                        $delegateCount++;
                        break;
                    case 'furlough':
                        $furloughCount++;
                        break;
                    default:
                        $noLevelCount++;
                }
                $totalCount++;
                $deleteContactURL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCUSTOMER_ACT_DELETECONTACT,
                        'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                    )
                );
                /** @noinspection HtmlDeprecatedAttribute */
                $deleteContactLink = '<a href="' . $deleteContactURL . '"><img align=middle border=0 hspace=2 src="images/icondelete.gif" alt="Delete contact" onClick="if(!confirm(\'Are you sure you want to delete this contact?\')) return(false)"></a>';
                $dearJohnURL       = Controller::buildLink(
                    'DearJohnForm.php',
                    array(
                        'contactID' => $this->dsContact->getValue(DBEContact::contactID)
                    )
                );
                $dmLetterURL       = Controller::buildLink(
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
                    'specialAttentionContactFlagChecked'   => $this->getChecked(
                        $this->dsContact->getValue(
                            DBEContact::specialAttentionContactFlag
                        )
                    ),
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
                    'workStartedEmailFlagChecked'          => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::workStartedEmailFlag)
                    ),
                    'workUpdatesEmailFlagChecked'          => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::workUpdatesEmailFlag)
                    ),
                    'fixedEmailFlagChecked'                => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::fixedEmailFlag)
                    ),
                    'pendingClosureEmailFlagChecked'       => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::pendingClosureEmailFlag)
                    ),
                    'closureEmailFlagChecked'              => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::closureEmailFlag)
                    ),
                    'othersInitialLoggingEmailFlagChecked' => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersInitialLoggingEmailFlag)
                    ),
                    'othersWorkStartedEmailFlagChecked'    => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersWorkStartedEmailFlag)
                    ),
                    'othersWorkUpdatesEmailFlagChecked'    => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersWorkUpdatesEmailFlag)
                    ),
                    'othersFixedEmailFlagChecked'          => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersFixedEmailFlag)
                    ),
                    'othersPendingClosureEmailFlagChecked' => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersPendingClosureEmailFlag)
                    ),
                    'othersClosureEmailFlagChecked'        => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::othersClosureEmailFlag)
                    ),
                    'hrUserFlagChecked'                    => $this->getChecked(
                        $this->dsContact->getValue(DBEContact::hrUser)
                    ),
                    'topUpValidation'                      => $this->buCustomer->hasPrepayContract(
                        DBEContact::customerID
                    ) ? 'data-validation="atLeastOne"' : null,
                    'dearJohnURL'                          => $dearJohnURL,
                    'dmLetterURL'                          => $dmLetterURL,
                    'deleteContactLink'                    => $deleteContactLink,
                    'linkedInURL'                          => $this->dsContact->getValue(DBEContact::linkedInURL),
                    'linkedInColor'                        => $this->dsContact->getValue(
                        DBEContact::linkedInURL
                    ) ? 'green' : 'red'
                )
            );
            $this->template->set_var(
                [
                    "mainCount"       => $mainCount,
                    "supervisorCount" => $supervisorCount,
                    "supportCount"    => $supportCount,
                    "delegateCount"   => $delegateCount,
                    "furloughCount"   => $furloughCount,
                    "noLevelCount"    => $noLevelCount,
                    "totalCount"      => $totalCount,
                ]
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

                $customLetterURL = Controller::buildLink(
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
        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER && $this->dsCustomer->getValue(
                DBECustomer::referredFlag
            ) == 'Y') {

            $ordersTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
            $ordersTemplate->setFile('OrdersTemplate', 'CustomerEditOrders.html');
            $ordersTemplate->set_block(
                'OrdersTemplate',
                'orderBlock',
                'orders'
            );
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
            while ($dbeJOrdhead->fetchNext()) {

                $ordheadID = $dbeJOrdhead->getPKValue();
                $orderURL  = Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => CTCNC_ACT_DISP_SALESORDER,
                        'ordheadID' => $ordheadID
                    )
                );
                $ordersTemplate->set_var(
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
                $ordersTemplate->parse(
                    'orders',
                    'orderBlock',
                    true
                );

            }
            $ordersTemplate->parse('output', 'OrdersTemplate');
            $this->template->setVar(
                [
                    'orders' => $ordersTemplate->getVar('output')
                ]
            );

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
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getCustomerNotePopupLink($customerID)
    {
        if (!$customerID) {
            return null;
        }
        $url = Controller::buildLink(
            'CustomerNote.php',
            array(
                'action'     => 'customerNoteHistoryPopup',
                'customerID' => $customerID,
                'htmlFmt'    => CT_HTML_FMT_POPUP
            )
        );
        return '<A HREF="' . $url . ' " target="_blank" >Notes History</A>';
    }

    function getChecked($flag)
    {
        return ($flag == 'N' ? null : CT_CHECKED);
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

    function setSiteNo($siteNo)
    {
        $this->setNumericVar(
            'siteNo',
            $siteNo
        );
    }

    /**
     * Get and parse contact drop-down selector
     * @access private
     * @param $contactID
     * @param DataSet $dsContact
     * @param $blockVar
     * @param $blockName
     */
    function parseContactSelector($contactID,
                                  &$dsContact,
                                  $blockVar,
                                  $blockName
    )
    {
        $dsContact->initialise();
        while ($dsContact->fetchNext()) {
            $contactSelected = ($dsContact->getValue(DBEContact::contactID) == $contactID) ? CT_SELECTED : null;
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

    function siteDropdown($customerID,
                          $siteNo,
                          $templateName = "selectSites",
                          $blockName = 'selectSiteBlock'
    )
    {
        if (!$customerID) {
            return null;
        }
        // Site selection
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $dbeSite->getRowsByCustomerID();
        while ($dbeSite->fetchNext()) {
            $siteSelected = ($siteNo == $dbeSite->getValue(DBESite::siteNo)) ? CT_SELECTED : null;
            $siteDesc     = $dbeSite->getValue(DBESite::siteNo);
            $this->template->set_var(
                array(
                    'siteSelected'   => $siteSelected,
                    'selectSiteNo'   => $dbeSite->getValue(DBESite::siteNo),
                    'selectSiteDesc' => $siteDesc
                )
            );
            $this->template->parse(
                $templateName,
                $blockName,
                true
            );
        }

    }

    function getOrderTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
    }

    /**
     * @param $customerID
     * @param $templateName
     * @throws Exception
     */
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
            $dsPortalCustomerDocument = new DataSet($this);
            $buPortalCustomerDocument->getDocumentsByCustomerID(
                $customerID,
                $dsPortalCustomerDocument
            );
            $urlAddDocument = Controller::buildLink(
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

                $urlEditDocument   = Controller::buildLink(
                    'PortalCustomerDocument.php',
                    array(
                        'action'                   => 'edit',
                        'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::portalCustomerDocumentID
                        )
                    )
                );
                $urlViewFile       = Controller::buildLink(
                    'PortalCustomerDocument.php',
                    array(
                        'action'                   => 'viewFile',
                        'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::portalCustomerDocumentID
                        )
                    )
                );
                $urlDeleteDocument = Controller::buildLink(
                    'PortalCustomerDocument.php',
                    array(
                        'action'                   => 'delete',
                        'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::portalCustomerDocumentID
                        )
                    )
                );
                $this->template->set_var(
                    array(
                        'description'         => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::description
                        ),
                        'filename'            => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::filename
                        ),
                        'customerContract'    => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::customerContract
                        ) ? 'Y' : 'N',
                        'mainContactOnlyFlag' => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::mainContactOnlyFlag
                        ),
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

    /**
     * Delete contact
     * @access private
     * @throws Exception
     */
    function deleteContact()
    {
        $this->setMethodName('deleteContact');
        if (!$this->getContactID()) {
            $this->displayFatalError('ContactID not passed');
        }
        $dsContact = new DataSet($this);
        $this->buCustomer->getContactByID(
            $this->getContactID(),
            $dsContact
        );
        $this->setCustomerID($dsContact->getValue(DBEContact::customerID));
        $nextURL = Controller::buildLink(
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
            $message = 'Cannot delete contact ' . $dsContact->getValue(
                    DBEContact::firstName
                ) . ' ' . $dsContact->getValue(DBEContact::lastName) . ' because dependencies exist in the database';
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

    function getContactID()
    {
        return $this->contactID;
    }

    function setContactID($contactID)
    {
        $this->setNumericVar(
            'contactID',
            $contactID
        );
    }

    /**
     * Delete sites and associated contacts
     * @access private
     * @throws Exception
     */
    function deleteSite()
    {
        $this->setMethodName('deleteSite');
        if (!$this->getCustomerID()) {
            $this->displayFatalError('CustomerID not passed');
        }
        if (!$this->getSiteNo()) {
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
            throw new Exception('Cannot delete this site - dependencies exist');
        }
        $nextURL = Controller::buildLink(
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
     * Delete customer and associated sites/contacts
     * @access private
     * @throws Exception
     */
    function deleteCustomer()
    {
        $this->setMethodName('deleteCustomer');
        if (!$this->getCustomerID()) {
            $this->displayFatalError('CustomerID not passed');
        }
        if ($this->buCustomer->canDeleteCustomer(
            $this->getCustomerID(),
            $this->userID
        )) {
            $this->buCustomer->deleteCustomer($this->getCustomerID());
            $nextURL = Controller::buildLink(
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
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayCustomerSelectPopup()
    {
        $this->setMethodName('displayCustomerSelectPopup');
        $this->buCustomer->getCustomersByNameMatch(
            $this->dsCustomer,
            null,
            null,
            $this->getCustomerString(),
            null,
            null,
            null,
            null,
            null
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

    /**
     * @return bool
     * @throws Exception
     */
    protected function saveContactPassword()
    {
        $contactID = $this->getParam('contactID');
        $password  = $this->getParam('password');
        if (!$contactID || !$password) {
            throw new Exception("Contact ID and Password required");
        }
        checkContactPassword($password);
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($contactID);
        $dbeContact->setValue(
            DBEContact::portalPassword,
            password_hash(
                $password,
                PASSWORD_DEFAULT
            )
        );
        $dbeContact->updateRow();
        return true;

    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function clearContact()
    {
        $contactID = $this->getParam('contactID');
        if (!$contactID) {
            throw new Exception("Contact ID required");
        }
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($contactID);
        $dbeContact->setValue(DBEContact::active, 0);
        $dbeContact->updateRow();
        return true;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getCustomerReviewContacts()
    {
        if (!$this->getParam('customerID')) {
            throw new Exception('Customer ID is missing');
        }
        $customerID = $this->getParam('customerID');
        $dbeContact = new DBEContact($this);
        $dbeContact->getReviewContactsByCustomerID($customerID);
        $contacts = [];
        while ($dbeContact->fetchNext()) {
            $contacts[] = [
                "firstName" => $dbeContact->getValue(DBEContact::firstName),
                "lastName"  => $dbeContact->getValue(DBEContact::lastName)
            ];
        }
        return $contacts;
    }

    function removeSupportForAllUsersAndReferCustomer($customerID)
    {
        if (!$customerID) {
            throw new Exception('Customer Id is required');
        }
        return $this->buCustomer->removeSupportForAllUsersAndReferCustomer($customerID);
    }

    function getCurrentUser()
    {
        return json_encode(
            [
                'firstName'   => $this->dbeUser->getValue(DBEJUser::firstName),
                'lastName'    => $this->dbeUser->getValue(DBEJUser::lastName),
                'id'          => $this->dbeUser->getValue(DBEJUser::userID),
                'email'       => $this->dbeUser->getEmail(),
                'isSdManager' => $this->isSdManager(),
            ]
        );
    }

    /**
     * search customer by name and contact name
     * @return array
     */
    function searchCustomers()
    {
        $q = $_GET["q"];
        if (!$db = mysqli_connect(
            DB_HOST,
            DB_USER,
            DB_PASSWORD
        )) {
            echo 'Could not connect to mysql host ' . DB_HOST;
            exit;
        }
        $query = "SELECT 
                cus_custno,
                cus_name,
                con_contno,
                add_town AS site_name,
                concat(contact.con_first_name,' ',contact.con_last_name) contact_name,                
                contact.con_first_name,
                contact.con_last_name,
                concat(contact.con_phone, ' ') as con_phone,
                contact.con_notes,
                concat(address.add_phone,' ') as add_phone,
                supportLevel,
                con_position,
                cus_referred,
                specialAttentionContactFlag = 'Y' as specialAttentionContact,
                (
                SELECT
                    COUNT(*)
                FROM
                    problem
                WHERE
                    pro_custno = cus_custno
                    AND pro_status IN( 'I', 'P')
                ) AS openSrCount,
                (SELECT cui_itemno IS NOT NULL FROM custitem WHERE custitem.`cui_itemno` = 4111 AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` and renewalStatus  <> 'D' limit 1) AS hasPrepay,
                (SELECT count(*) > 0 FROM custitem LEFT JOIN item ON cui_itemno = item.`itm_itemno` WHERE `itm_itemtypeno` = 56 and renewalStatus <> 'D' AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` ) AS hasServiceDesk,
                cus_special_attention_flag specialAttentionCustomer
                FROM customer
    
                JOIN contact ON con_custno = cus_custno
                JOIN address ON add_custno = cus_custno AND add_siteno = con_siteno
                WHERE supportLevel is not null ";
        if ($q && $q != "") {
            mysqli_select_db(
                $db,
                DB_NAME
            );
            $query .= " AND (";
            $query .= " concat(con_first_name,' ',con_last_name) LIKE '%" . mysqli_real_escape_string(
                    $db,
                    $q
                ) . "%' ";
            $query .= " OR customer.cus_name LIKE '%" . mysqli_real_escape_string(
                    $db,
                    $q
                ) . "%' ";
            $query .= " OR customer.cus_custno LIKE '%" . mysqli_real_escape_string(
                    $db,
                    $q
                ) . "%' ";
            $query .= " ) ";
        }
        $query .= " and active and cus_referred <> 'Y'
                    ORDER BY cus_name, con_last_name, con_first_name 
                    ";
        // echo $query;
        // exit;
        $result = mysqli_query(
            $db,
            $query
        );
        return mysqli_fetch_all($result, MYSQLI_ASSOC);

    }

    function getCustomerSR()
    {
        $this->setMethodName("getCustomerSR");
        $buActivity  = new BUActivity($this);
        $customerId  = $_GET['customerId'];
        $dsActiveSrs = $buActivity->getActiveProblemsByCustomer($customerId);
        $customerSR  = array();
        while ($dsActiveSrs->fetchNext()) {

            $urlProblemHistoryPopup = Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $dsActiveSrs->getValue(DBEJProblem::problemID),
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );
            array_push(
                $customerSR,
                array(
                    'problemID'              => $dsActiveSrs->getValue(DBEJProblem::problemID),
                    'dateRaised'             => $dsActiveSrs->getValue(DBEJProblem::dateRaised),
                    'reason'                 => Utils::truncate($dsActiveSrs->getValue(DBEJProblem::reason), 100),
                    'lastReason'             => Utils::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'engineerName'           => $dsActiveSrs->getValue(DBEJProblem::engineerName),
                    'activityID'             => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
                    'contactName'            => $dsActiveSrs->getValue('contactName'),
                    'contactId'              => $dsActiveSrs->getValue('contactId'),
                    'urlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'priority'               => $dsActiveSrs->getValue(DBEJProblem::priority),
                    'status'                 => $dsActiveSrs->getValue(DBEJProblem::status),
                    'isSpecialAttention'     => $this->isSpecialAttention($dsActiveSrs),
                    "assetName"              => $dsActiveSrs->getValue('assetName'),
                )
            );
        }
        return $customerSR;
    }

    private function isSpecialAttention($dbejProblem)
    {
        return $dbejProblem->getValue(DBEJProblem::specialAttentionContactFlag) == 'Y' || $dbejProblem->getValue(
                DBEJProblem::specialAttentionFlag
            ) == 'Y';
    }

    function getCustomerSites()
    {
        $customerId = $_GET["customerId"];
        if (!$customerId) return [];
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerId
        );
        $dbeSite->getRowsByCustomerID();
        $sites = array();
        while ($dbeSite->fetchNext()) {
            $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                    DBESite::town
                ) . ' ' . $dbeSite->getValue(DBESite::postcode);
            $siteNo   = $dbeSite->getValue(DBESite::siteNo);
            array_push($sites, ["id" => $siteNo, "title" => $siteDesc]);
        }
        return $sites;
    }

    function getCustomerAssets(): array
    {
        $customerId = $_GET["customerId"];
        $labtechDB  = $this->getLabtechDB();
        $query      = "SELECT
  computers.name AS `name`,
  computers.assetTag AS `assetTag`,
  computers.LastUsername AS `lastUsername`,
  computers.BiosVer AS `biosVer`,
  computers.BiosName AS `biosName`
FROM
  computers
  JOIN clients
    ON computers.clientid = clients.clientid
    AND clients.externalID = ?
UNION
ALL
SELECT
  plugin_vm_esxhosts.`DeviceName` AS `name`,
  NULL AS `assetTag`,
  NULL AS `lastUsername`,
  NULL AS `biosVer`,
  NULL AS `biosName`
FROM
  plugin_vm_esxhosts
  JOIN `networkdevices`
    ON networkdevices.deviceID = plugin_vm_esxhosts.deviceID
  JOIN locations
    ON networkdevices.LocationID = locations.LocationID
  JOIN clients
    ON locations.ClientID = clients.ClientID
WHERE clients.`ExternalID` = ?
ORDER BY NAME,
  lastUsername,
  biosVer
        ";
        $statement  = $labtechDB->prepare($query);
        $statement->execute([$customerId, $customerId]);
        $customerAssets = $statement->fetchAll(PDO::FETCH_ASSOC);
        $unsupportedCustomerAssetsService = new UnsupportedCustomerAssetService();
        $unsupportedCustomerAssets = $unsupportedCustomerAssetsService->getAllForCustomer($customerId);
        foreach ($customerAssets as $key => $customerAsset){
            $customerAssets[$key]['unsupported'] = in_array($customerAsset['name'],$unsupportedCustomerAssets);
        }
        return $customerAssets;
    }

    /**
     * @param $customerID
     * @param $contactID
     * @param string $templateName
     */
    function getCustomerContacts()
    {
        $customerID = $_REQUEST["customerID"];
        $dbeContact = new DBEContact($this);
        $dbeSite    = new DBESite($this);
        $dbeContact->getRowsByCustomerID($customerID, true);
        $buCustomer     = new BUCustomer($this);
        $primaryContact = $buCustomer->getPrimaryContact($customerID);
        $contacts       = array();
        while ($dbeContact->fetchNext()) {
            $dbeSite->setValue(
                DBESite::customerID,
                $dbeContact->getValue(DBEContact::customerID)
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue(DBEContact::siteNo)
            );
            $dbeSite->getRow();
            array_push(
                $contacts,
                array(
                    'id'           => $dbeContact->getValue(DBEContact::contactID),
                    'position'     => $dbeContact->getValue(DBEContact::position),
                    'firstName'    => $dbeContact->getValue(DBEContact::firstName),
                    'lastName'     => $dbeContact->getValue(DBEContact::lastName),
                    'siteNo'       => $dbeSite->getValue(DBESite::siteNo),
                    'active'       => $dbeContact->getValue(DBEContact::active),
                    'siteTitle'    => $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                            DBESite::town
                        ) . ' ' . $dbeSite->getValue(DBESite::postcode),
                    "sitePhone"    => $dbeSite->getValue(DBESite::phone),
                    "phone"        => $dbeContact->getValue(DBEContact::phone),
                    "mobilePhone"  => $dbeContact->getValue(DBEContact::mobilePhone),
                    "email"        => $dbeContact->getValue(DBEContact::email),
                    'supportLevel' => $dbeContact->getValue(DBEContact::supportLevel),
                    "notes"        => $dbeContact->getValue(DBEContact::notes),
                    "isPrimary"    => $primaryContact && $primaryContact->getValue(
                            DBEContact::contactID
                        ) === $dbeContact->getValue(DBEContact::contactID)
                )
            );
        }
        return $contacts;
    }

    function getCustomerProjects()
    {
        $customerID = $_REQUEST["customerID"];
        return BUProject::getCustomerProjects($customerID);
    }

    function getCustomerContracts()
    {
        $customerID         = $_REQUEST["customerId"];
        $linkedToSalesOrder = $_REQUEST["linkedToSalesOrder"];
        $contracts          = array();
        $buCustomerItem     = new BUCustomerItem($this);
        $dsContract         = new DataSet($this);
        if ($customerID) {
            $buCustomerItem->getContractsByCustomerID(
                $customerID,
                $dsContract,
                null
            );
        }
        while ($dsContract->fetchNext()) {

            $description = $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
                    DBEJContract::adslPhone
                ) . ' ' . $dsContract->getValue(DBEJContract::notes) . ' ' . $dsContract->getValue(
                    DBEJContract::postcode
                );
            array_push(
                $contracts,
                array(
                    'contractCustomerItemID' => $dsContract->getValue(DBEJContract::customerItemID),
                    'contractDescription'    => $description,
                    'prepayContract'         => $dsContract->getValue(DBEJContract::itemTypeID) == 57,
                    'isDisabled'             => !$dsContract->getValue(
                        DBEJContract::allowSRLog
                    ) || $linkedToSalesOrder == 'true' ? true : false,
                    'renewalType'            => $dsContract->getValue(DBEJContract::renewalType)
                )
            );
        }
        return $contracts;
    }

    function getCustomersHaveOpenSR()
    {
        $customers = DBConnect::fetchAll(
            "SELECT `cus_custno` id,`cus_name` name FROM`customer` WHERE `cus_custno` IN(
            SELECT  `pro_custno` FROM `problem` WHERE `pro_status` <> 'C' AND `pro_status` <> 'F'
            )
            ",
            []
        );
        return $customers;
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
            // Update the database
            if ($this->getCustomerID() == 0) {      // New customer
                $this->buCustomer->insertCustomer(
                    $this->dsCustomer,
                    $this->dsSite,
                    $this->dsContact
                );
                $this->dsCustomer->initialise();
                $this->dsCustomer->fetchNext();
                $this->setCustomerID($this->dsCustomer->getValue(DBECustomer::customerID));
            } else {                // Updates to customer and updates/inserts to sites and contacts
                $this->buCustomer->updateCustomer($this->dsCustomer);
                $this->buCustomer->updateSite($this->dsSite);
                if (isset($this->postVars["form"]["contact"])) {
                    $this->buCustomer->updateContact($this->dsContact);
                }
            }
            $this->setAction(CTCUSTOMER_ACT_DISP_SUCCESS);
            if ($this->getSessionParam('save_page')) {
                header('Location: ' . $_SESSION['save_page']);
                exit;
            } else {
                $nextURL = Controller::buildLink(
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
