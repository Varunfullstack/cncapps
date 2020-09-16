<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;

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
    const GET_CUSTOMER_REVIEW_CONTACTS = "getCustomerReviewContacts";
    const GET_CUSTOMER_PROJECTS = 'getCustomerProjects';
    const DECRYPT = "decrypt";
    const contactFormTitleClass = 'TitleClass';
    const contactFormFirstNameClass = 'FirstNameClass';                      // Used when searching for an entity by string
    const contactFormLastNameClass = 'LastNameClass';                      // Used when searching for an entity by string
    const contactFormEmailClass = 'EmailClass';                      // Used when searching for an entity by string
    const contactFormHasPassword = 'hasPassword';                      // Used when searching for an entity by string
    const siteFormAdd1Class = 'Add1Class';                      // Used when searching for an entity by string
    const siteFormTownClass = 'TownClass';                      // Used when searching for an entity by string
    const siteFormPostcodeClass = 'PostcodeClass';                      // Used when searching for an entity by string
    const customerFormNameClass = 'NameClass';                                // Used when searching for customer
    const customerFormInvoiceSiteMessage = 'InvoiceSiteMessage';
    const customerFormDeliverSiteMessage = 'DeliverSiteMessage';
    const customerFormSectorMessage = 'SectorMessage';
    const customerFormSpecialAttentionEndDateMessage = 'specialAttentionEndDateMessage';
    const customerFormLastReviewMeetingDateMessage = 'lastReviewMeetingDateMessage';
    const GET_CUSTOMER_PORTAL_DOCUMENTS = 'getCustomerPortalDocuments';
    const GET_CUSTOMER_SITES = "getSites";
    const GET_CUSTOMER_CONTACTS = "getContacts";
    const UPDATE_SITE = "updateSite";
    const GET_CUSTOMER_ORDERS = 'getCustomerOrders';
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
        $this->dsContact = new DataSet($this);
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
                DBEContact::supplierID,
                @$value['supplierID']
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
            $email = !@$value['email'] ? null : $value['email'];


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
                if (
                    ($this->dsContact->getValue(DBEContact::title)) |
                    ($this->dsContact->getValue(DBEContact::firstName)) |
                    ($this->dsContact->getValue(DBEContact::lastName))
                ) {
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

        foreach ($customerArray as $customerID => $customer) {


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
            case 'encrypt':
            {
                $value = @$_REQUEST['value'];
                $encrypted = Encryption::encrypt(
                    CUSTOMERS_ENCRYPTION_PUBLIC_KEY,
                    $value
                );
                echo json_encode(["status" => "ok", "data" => $encrypted]);
                exit;
            }
            case self::GET_CUSTOMER_ORDERS:
            {
                $this->getCustomerOrdersController();
                exit;
            }
            case 'getCustomer':
            {
                $customerID = @$_REQUEST['customerID'];
                if (!$customerID) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "description" => "Customer ID Not provided"]);
                    exit;
                }
                $dbeCustomer = new DBECustomer($this);
                if (!$dbeCustomer->getRow($customerID)) {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "description" => "Customer not found"]);
                    exit;
                }
                echo json_encode(
                    [
                        "status" => "ok",
                        "data"   => [
                            "accountManagerUserID"         => $dbeCustomer->getValue(DBECustomer::accountManagerUserID),
                            "accountName"                  => $dbeCustomer->getValue(DBECustomer::accountName),
                            "accountNumber"                => $dbeCustomer->getValue(DBECustomer::accountNumber),
                            "activeDirectoryName"          => $dbeCustomer->getValue(DBECustomer::activeDirectoryName),
                            "becameCustomerDate"           => $dbeCustomer->getValue(DBECustomer::becameCustomerDate),
                            "customerID"                   => $dbeCustomer->getValue(DBECustomer::customerID),
                            "customerTypeID"               => $dbeCustomer->getValue(DBECustomer::customerTypeID),
                            "droppedCustomerDate"          => $dbeCustomer->getValue(DBECustomer::droppedCustomerDate),
                            "gscTopUpAmount"               => $dbeCustomer->getValue(DBECustomer::gscTopUpAmount),
                            "lastReviewMeetingDate"        => $dbeCustomer->getValue(
                                DBECustomer::lastReviewMeetingDate
                            ),
                            "leadStatusId"                 => $dbeCustomer->getValue(DBECustomer::leadStatusId),
                            "mailshotFlag"                 => $dbeCustomer->getValue(DBECustomer::mailshotFlag),
                            "modifyDate"                   => $dbeCustomer->getValue(DBECustomer::modifyDate),
                            "name"                         => $dbeCustomer->getValue(DBECustomer::name),
                            "noOfPCs"                      => $dbeCustomer->getValue(DBECustomer::noOfPCs),
                            "noOfServers"                  => $dbeCustomer->getValue(DBECustomer::noOfServers),
                            "noOfSites"                    => $dbeCustomer->getValue(DBECustomer::noOfSites),
                            "primaryMainContactID"         => $dbeCustomer->getValue(DBECustomer::primaryMainContactID),
                            "referredFlag"                 => $dbeCustomer->getValue(DBECustomer::referredFlag),
                            "regNo"                        => $dbeCustomer->getValue(DBECustomer::regNo),
                            "reviewMeetingBooked"          => $dbeCustomer->getValue(DBECustomer::reviewMeetingBooked),
                            "reviewMeetingFrequencyMonths" => $dbeCustomer->getValue(
                                DBECustomer::reviewMeetingFrequencyMonths
                            ),
                            "sectorID"                     => $dbeCustomer->getValue(DBECustomer::sectorID),
                            "slaP1"                        => $dbeCustomer->getValue(DBECustomer::slaP1),
                            "slaP2"                        => $dbeCustomer->getValue(DBECustomer::slaP2),
                            "slaP3"                        => $dbeCustomer->getValue(DBECustomer::slaP3),
                            "slaP4"                        => $dbeCustomer->getValue(DBECustomer::slaP4),
                            "slaP5"                        => $dbeCustomer->getValue(DBECustomer::slaP5),
                            "slaFixHoursP1"                => $dbeCustomer->getValue(DBECustomer::slaFixHoursP1),
                            "slaFixHoursP2"                => $dbeCustomer->getValue(DBECustomer::slaFixHoursP2),
                            "slaFixHoursP3"                => $dbeCustomer->getValue(DBECustomer::slaFixHoursP3),
                            "slaFixHoursP4"                => $dbeCustomer->getValue(DBECustomer::slaFixHoursP4),
                            "slaP1PenaltiesAgreed"         => $dbeCustomer->getValue(DBECustomer::slaP1PenaltiesAgreed),
                            "slaP2PenaltiesAgreed"         => $dbeCustomer->getValue(DBECustomer::slaP2PenaltiesAgreed),
                            "slaP3PenaltiesAgreed"         => $dbeCustomer->getValue(DBECustomer::slaP3PenaltiesAgreed),
                            "sortCode"                     => $dbeCustomer->getValue(DBECustomer::sortCode),
                            "specialAttentionEndDate"      => $dbeCustomer->getValue(
                                DBECustomer::specialAttentionEndDate
                            ),
                            "specialAttentionFlag"         => $dbeCustomer->getValue(DBECustomer::specialAttentionFlag),
                            "support24HourFlag"            => $dbeCustomer->getValue(DBECustomer::support24HourFlag),
                            "techNotes"                    => $dbeCustomer->getValue(DBECustomer::techNotes),
                            "websiteURL"                   => $dbeCustomer->getValue(DBECustomer::websiteURL),
                            "reviewDate"                   => $dbeCustomer->getValue(DBECustomer::reviewDate),
                            "reviewTime"                   => $dbeCustomer->getValue(DBECustomer::reviewTime),
                        ]
                    ]
                );
                break;
            }
            case 'getMainContacts':
            {
                $customerID = @$_REQUEST['customerID'];
                if (!$customerID) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "description" => "Customer ID Not provided"]);
                    exit;
                }
                echo json_encode(["status" => "ok", "data" => $this->getMainContacts($customerID)]);
                break;
            }
            case 'getLeadStatuses':
            {
                $dbeLeadStatus = new DBECustomerLeadStatus($this);
                $dbeLeadStatus->getRows(DBECustomerLeadStatus::sortOrder);
                echo json_encode(["status" => "ok", "data" => $dbeLeadStatus->fetchArray()]);
                break;
            }
            case 'updateCustomer':
            {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($data['customerID']);
                foreach ($data as $key => $value) {
                    $dbeCustomer->setValue($key, $value);
                }

                $dbeCustomer->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            }
            case 'getSectors':
            {
                $dbeSector = new DBESector($this);
                $dbeSector->getRows(DBESector::description);
                echo json_encode(["status" => "ok", "data" => $dbeSector->fetchArray()]);
                break;
            }
            case 'getCustomerTypes':
            {
                $dbeCustomerTypes = new DBECustomerType($this);
                $dbeCustomerTypes->getRows(DBECustomerType::description);
                echo json_encode(["status" => "ok", "data" => $dbeCustomerTypes->fetchArray()]);
                break;
            }
            case 'getAccountManagers':
            {
                $dbeUser = new DBEUser($this);
                $dbeUser->getRows();
                echo json_encode(["status" => "ok", "data" => $dbeUser->fetchArray()]);
                break;
            }
            case 'getReviewEngineers':
                return $this->getReviewEngineersController();
            case 'getCustomerReviewData':
                return $this->getCustomerReviewDataController();
            case 'updateCustomerReview':
                return $this->updateCustomerReviewController();
            case self::GET_CUSTOMER_PORTAL_DOCUMENTS:
                return $this->getCustomerPortalDocumentsController();
            case 'createCustomerFolder':
                $this->createCustomerFolder();
                break;
            case 'displayNextReviewProspect':
                $this->displayNextReviewProspect();
                break;
            case 'displayReviewList':
                $this->displayReviewList();
                break;
            case self::GET_CUSTOMER_SITES:
                return $this->getCustomerSitesController();
            case self::GET_CUSTOMER_CONTACTS:
                return $this->getCustomerContactsController();
            case self::UPDATE_SITE:
                return $this->updateSiteController();
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
            case self::GET_CUSTOMER_REVIEW_CONTACTS:
                $response = [];
                try {
                    $response['data'] = $this->getCustomerReviewContacts();
                    $response["status"] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response["status"] = "error";
                    $response["error"] = $exception->getMessage();
                }

                echo json_encode($response);
                break;
            case self::GET_CUSTOMER_PROJECTS:
                return $this->getCustomerProjectsController();
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
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case "removeSupportAndRefer":
                $customerID = @$this->getParam('customerID');
                $response = ['status' => 'ok'];
                try {
                    $this->removeSupportForAllUsersAndReferCustomer($customerID);
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case 'searchName':
                $itemsPerPage = 20;
                $page = 1;
                $term = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }

                if (isset($_REQUEST['itemsPerPage'])) {
                    $itemsPerPage = $_REQUEST['itemsPerPage'];
                }

                if (isset($_REQUEST['page'])) {
                    $page = $_REQUEST['page'];
                }
                $dsResult = new DataSet($this);
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

    function getCustomerOrdersController()
    {
        $customerId = $this->getParam('customerId');
        $dbeCustomer = new DBECustomer($this);
        $orders = [];
        if ($dbeCustomer->getRow($customerId) && $dbeCustomer->getValue(DBECustomer::referredFlag) == 'Y') {
            $dbeJOrdhead = new DBEJOrdhead($this);
            $dbeJOrdhead->getRowsBySearchCriteria(
                $customerId,
                false,
                false,
                false,
                false,
                false,
                false
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

                $orders[] = [
                    'url'       => $orderURL,
                    'id'        => $ordheadID,
                    'type'      => $this->getOrderTypeDescription($dbeJOrdhead->getValue(DBEJOrdhead::type)),
                    'date'      => strftime(
                        "%d/%m/%Y",
                        strtotime($dbeJOrdhead->getValue(DBEJOrdhead::date))
                    ),
                    'custPORef' => $dbeJOrdhead->getValue(DBEJOrdhead::custPORef)
                ];

            }
        }
        echo json_encode(["status" => "ok", "data" => $orders]);
    }

    function getOrderTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
    }

    private function getMainContacts($customerID)
    {
        $dbeContact = new DBEContact($this);
        $dbeContact->getMainContacts($customerID);
        return $dbeContact->fetchArray();
    }

    function getReviewEngineersController()
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        echo json_encode(["status" => "ok", "data" => $dbeUser->fetchArray()]);
    }

    function getCustomerReviewDataController()
    {
        if (!isset($_REQUEST['customerID'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer ID is mandatory"]);
            exit;
        }

        $dbeCustomer = new DBECustomer($this);
        if (!$dbeCustomer->getRow($_REQUEST['customerID'])) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Customer does not exist"]);
            exit;
        }
        echo json_encode(
            [
                "status" => "ok",
                "data"   => [
                    "toBeReviewedOnDate"         => $dbeCustomer->getValue(DBECustomer::reviewDate),
                    "toBeReviewedOnTime"         => $dbeCustomer->getValue(DBECustomer::reviewTime),
                    "toBeReviewedOnByEngineerId" => $dbeCustomer->getValue(DBECustomer::reviewUserID),
                    "toBeReviewedOnAction"       => $dbeCustomer->getValue(DBECustomer::reviewAction)
                ]
            ]
        );
    }

    function updateCustomerReviewController()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['customerId'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer ID is mandatory"]);
            exit;
        }

        $dbeCustomer = new DBECustomer($this);
        if (!$dbeCustomer->getRow($data['customerId'])) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Customer does not exist"]);
            exit;
        }
        $dbeCustomer->setValue(DBECustomer::reviewDate, $data["toBeReviewedOnDate"]);
        $dbeCustomer->setValue(DBECustomer::reviewTime, $data["toBeReviewedOnTime"]);
        $dbeCustomer->setValue(DBECustomer::reviewUserID, $data["toBeReviewedOnByEngineerId"]);
        $dbeCustomer->setValue(DBECustomer::reviewAction, $data["toBeReviewedOnAction"]);
        $dbeCustomer->updateRow();
        echo json_encode(
            ["status" => "ok",]
        );
    }

    function getCustomerPortalDocumentsController()
    {
        if (!isset($_REQUEST['customerId'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer ID is mandatory"]);
            exit;
        }
        echo json_encode(
            ["status" => "ok", "data" => $this->getPortalDocuments($_REQUEST['customerId'])]
        );
    }

    /**
     * @param $customerID
     * @return array
     * @throws Exception
     */
    function getPortalDocuments($customerID)
    {
        $portalDocuments = new DBEPortalCustomerDocumentWithoutFile($customerID);
        $portalDocuments->setValue(DBEPortalCustomerDocumentWithoutFile::customerID, $customerID);
        $portalDocuments->getRowsByColumn(
            DBEPortalCustomerDocument::customerID,
            DBEPortalCustomerDocumentWithoutFile::description
        );
        $documents = [];
        while ($portalDocuments->fetchNext()) {
            $documents[] =
                [
                    'id'                  => $portalDocuments->getValue(
                        DBEPortalCustomerDocument::portalCustomerDocumentID
                    ),
                    'description'         => $portalDocuments->getValue(
                        DBEPortalCustomerDocumentWithoutFile::description
                    ),
                    'filename'            => $portalDocuments->getValue(DBEPortalCustomerDocumentWithoutFile::filename),
                    'customerContract'    => $portalDocuments->getValue(
                        DBEPortalCustomerDocumentWithoutFile::customerContract
                    ),
                    'mainContactOnlyFlag' => $portalDocuments->getValue(
                            DBEPortalCustomerDocument::mainContactOnlyFlag
                        ) === 'Y',
                ];
        }
        return $documents;
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

    function getCustomerID()
    {
        return $_REQUEST['customerID'];
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

            $nextURL =
                Controller::buildLink(
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

                $linkURL =
                    Controller::buildLink(
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

    function getCustomerSitesController()
    {
        if (!isset($_REQUEST['customerId'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer Id is required"]);
            exit;
        }
        $dbeSite = new DBESite($this);
        $customerId = $_REQUEST['customerId'];
        $dbeSite->setValue(DBESite::customerID, $customerId);
        $dbeSite->getRowsByCustomerID(false);
        $sites = [];
        while ($dbeSite->fetchNext()) {
            $sites[] = [
                "customerID"     => $dbeSite->getValue(DBESite::customerID),
                "siteNo"         => $dbeSite->getValue(DBESite::siteNo),
                "address1"       => $dbeSite->getValue(DBESite::add1),
                "address2"       => $dbeSite->getValue(DBESite::add2),
                "address3"       => $dbeSite->getValue(DBESite::add3),
                "town"           => $dbeSite->getValue(DBESite::town),
                "county"         => $dbeSite->getValue(DBESite::county),
                "postcode"       => $dbeSite->getValue(DBESite::postcode),
                "invoiceContact" => $dbeSite->getValue(DBESite::invoiceContactID),
                "deliverContact" => $dbeSite->getValue(DBESite::deliverContactID),
                "debtorCode"     => $dbeSite->getValue(DBESite::debtorCode),
                "sageRef"        => $dbeSite->getValue(DBESite::sageRef),
                "phone"          => $dbeSite->getValue(DBESite::phone),
                "maxTravelHours" => $dbeSite->getValue(DBESite::maxTravelHours),
                "active"         => $dbeSite->getValue(DBESite::activeFlag) == 'Y',
                "nonUKFlag"      => $dbeSite->getValue(DBESite::nonUKFlag) == 'Y',
                "what3Words"     => $dbeSite->getValue(DBESite::what3Words),
                "canDelete"      => $this->buCustomer->canDeleteSite($customerId, $dbeSite->getValue(DBESite::siteNo))
            ];
        }
        echo json_encode(["status" => "ok", "data" => $sites]);
    }

    function getCustomerContactsController()
    {
        if (!isset($_REQUEST['customerId'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer Id is required"]);
            exit;
        }
        $contact = new DBEContact($this);

        $contact->getRowsByCustomerID($_REQUEST['customerId']);
        $contacts = [];
        while ($contact->fetchNext()) {
            $contacts[] = [
                "id"                            => $contact->getValue(DBEContact::contactID),
                "siteNo"                        => $contact->getValue(DBEContact::siteNo),
                "customerID"                    => $contact->getValue(DBEContact::customerID),
                "supplierID"                    => $contact->getValue(DBEContact::supplierID),
                "title"                         => $contact->getValue(DBEContact::title),
                "position"                      => $contact->getValue(DBEContact::position),
                "lastName"                      => $contact->getValue(DBEContact::lastName),
                "firstName"                     => $contact->getValue(DBEContact::firstName),
                "email"                         => $contact->getValue(DBEContact::email),
                "phone"                         => $contact->getValue(DBEContact::phone),
                "mobilePhone"                   => $contact->getValue(DBEContact::mobilePhone),
                "fax"                           => $contact->getValue(DBEContact::fax),
                "portalPassword"                => $contact->getValue(DBEContact::portalPassword),
                "sendMailshotFlag"              => $contact->getValue(DBEContact::sendMailshotFlag),
                "discontinuedFlag"              => $contact->getValue(DBEContact::discontinuedFlag),
                "accountsFlag"                  => $contact->getValue(DBEContact::accountsFlag),
                "mailshot2Flag"                 => $contact->getValue(DBEContact::mailshot2Flag),
                "mailshot3Flag"                 => $contact->getValue(DBEContact::mailshot3Flag),
                "mailshot4Flag"                 => $contact->getValue(DBEContact::mailshot4Flag),
                "mailshot8Flag"                 => $contact->getValue(DBEContact::mailshot8Flag),
                "mailshot9Flag"                 => $contact->getValue(DBEContact::mailshot9Flag),
                "mailshot11Flag"                => $contact->getValue(DBEContact::mailshot11Flag),
                "notes"                         => $contact->getValue(DBEContact::notes),
                "failedLoginCount"              => $contact->getValue(DBEContact::failedLoginCount),
                "reviewUser"                    => $contact->getValue(DBEContact::reviewUser),
                "hrUser"                        => $contact->getValue(DBEContact::hrUser),
                "supportLevel"                  => $contact->getValue(DBEContact::supportLevel),
                "initialLoggingEmailFlag"       => $contact->getValue(DBEContact::initialLoggingEmailFlag),
                "workStartedEmailFlag"          => $contact->getValue(DBEContact::workStartedEmailFlag),
                "workUpdatesEmailFlag"          => $contact->getValue(DBEContact::workUpdatesEmailFlag),
                "fixedEmailFlag"                => $contact->getValue(DBEContact::fixedEmailFlag),
                "pendingClosureEmailFlag"       => $contact->getValue(DBEContact::pendingClosureEmailFlag),
                "closureEmailFlag"              => $contact->getValue(DBEContact::closureEmailFlag),
                "othersInitialLoggingEmailFlag" => $contact->getValue(DBEContact::othersInitialLoggingEmailFlag),
                "othersWorkStartedEmailFlag"    => $contact->getValue(DBEContact::othersWorkStartedEmailFlag),
                "othersWorkUpdatesEmailFlag"    => $contact->getValue(DBEContact::othersWorkUpdatesEmailFlag),
                "othersFixedEmailFlag"          => $contact->getValue(DBEContact::othersFixedEmailFlag),
                "othersPendingClosureEmailFlag" => $contact->getValue(DBEContact::othersPendingClosureEmailFlag),
                "othersClosureEmailFlag"        => $contact->getValue(DBEContact::othersClosureEmailFlag),
                "pendingLeaverFlag"             => $contact->getValue(DBEContact::pendingLeaverFlag),
                "pendingLeaverDate"             => $contact->getValue(DBEContact::pendingLeaverDate),
                "specialAttentionContactFlag"   => $contact->getValue(DBEContact::specialAttentionContactFlag),
                "linkedInURL"                   => $contact->getValue(DBEContact::linkedInURL),
                "active"                        => $contact->getValue(DBEContact::active),
            ];
        }
        echo json_encode(["status" => "ok", "data" => $contacts]);
    }

    function updateSiteController()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['siteNo']) || !isset($data['customerID'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "CustomerId and siteNo are required"]);
            exit;
        }
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(DBESite::customerID, $data['customerID']);
        $dbeSite->setValue(DBESite::siteNo, $data['siteNo']);
        $dbeSite->getRow();
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($data['customerID']);
        if ($dbeCustomer->getValue(DBECustomer::invoiceSiteNo) != $data['invoiceSiteNo']
            ||
            $dbeCustomer->getValue(DBECustomer::deliverSiteNo) != $data['deliverSiteNo']
        ) {
            $dbeCustomer->setValue(DBECustomer::deliverSiteNo, $data['deliverSiteNo']);
            $dbeCustomer->setValue(DBECustomer::invoiceSiteNo, $data['invoiceSiteNo']);
            $dbeCustomer->updateRow();
        }
        $dbeSite->setValue(DBESite::add1, $data["address1"]);
        $dbeSite->setValue(DBESite::add2, $data["address2"]);
        $dbeSite->setValue(DBESite::add3, $data["address3"]);
        $dbeSite->setValue(DBESite::town, $data["town"]);
        $dbeSite->setValue(DBESite::county, $data["county"]);
        $dbeSite->setValue(DBESite::postcode, $data["postcode"]);
        $dbeSite->setValue(DBESite::invoiceContactID, $data["invoiceContact"]);
        $dbeSite->setValue(DBESite::deliverContactID, $data["deliverContact"]);
        $dbeSite->setValue(DBESite::debtorCode, $data["debtorCode"]);
        $dbeSite->setValue(DBESite::sageRef, $data["sageRef"]);
        $dbeSite->setValue(DBESite::phone, $data["phone"]);
        $dbeSite->setValue(DBESite::maxTravelHours, $data["maxTravelHours"]);
        $dbeSite->setValue(DBESite::activeFlag, $data["active"] ? 'Y' : 'N');
        $dbeSite->setValue(DBESite::nonUKFlag, $data["nonUKFlag"] ? 'Y' : 'N');
        $dbeSite->setValue(DBESite::what3Words, $data["what3Words"]);
        $dbeSite->updateRow();

        echo json_encode(
            ["status" => "ok",]
        );
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
        )
        ) {
            $this->setCustomerStringMessage(CTCUSTOMER_MSG_NONE_FND);
        }
        if (($this->formError) || ($this->dsCustomer->rowCount() > 1)) {
            $this->displaySearchForm();
        } else {
            // reload with this customer
            $nextURL =
                Controller::buildLink(
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
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_SEARCH)
        );
        $createURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_ADDCUSTOMER)
        );
        $customerPopupURL =
            Controller::buildLink(
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
                    Controller::buildLink(
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
        $deleteCustomerURL = null;
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
//            var_dump($this->dsCustomer->getValue(DBECustomer::reviewDate));
//            exit;


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

//        $this->template->setVar(
//            'javaScript',
//            "<script src='/components/customerEditMain/dist/CustomerEditComponent.js?version=1.0.0'></script>"
//        );

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

        $customerFolderLink = null;
        if (!$this->buCustomer->customerFolderExists(
            $this->dsCustomer->getValue(DBECustomer::customerID)
        )) {
            $urlCreateCustomerFolder =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'createCustomerFolder',
                        'customerID' => $this->getCustomerID(),
                    )
                );
            $customerFolderLink =
                '<a href="' . $urlCreateCustomerFolder . '" title="Create Folder">Create Customer Folder</a>';
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

        $thirdPartyLinkURL = Controller::buildLink(
            'ThirdPartyContact.php',
            [
                'action'     => 'list',
                'customerID' => $this->getCustomerID()
            ]
        );

        $thirdPartyLink = '<a href="' . $thirdPartyLinkURL . '" target="_blank" title="Third Party Contacts">Third Party Contacts</a>';

        $showInactiveContactsURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'               => 'dispEdit',
                    'customerID'           => $this->getCustomerID(),
                    'showInactiveContacts' => '1'
                )
            );
        $showInactiveSitesURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'            => 'dispEdit',
                    'customerID'        => $this->getCustomerID(),
                    'showInactiveSites' => '1'
                )
            );

        $urlContactPopup =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
//          'action' => CTCNC_ACT_CONTACT_EDIT,
'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


        $mainContacts = [];
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


        $buItem = new BUCustomerItem($this);
        $forceDirectDebit = false;
        if ($this->dsCustomer->getValue(DBECustomer::customerID)) {
            $forceDirectDebit = $buItem->clientHasDirectDebit($this->dsCustomer->getValue(DBECustomer::customerID));
        }

        $this->template->set_var(
            array(
                'lastContractSent'               => $this->dsCustomer->getValue(DBECustomer::lastContractSent),
                'urlContactPopup'                => $urlContactPopup,
                /* hidden */
                'reviewMeetingEmailSentFlag'     => $this->dsCustomer->getValue(
                    DBECustomer::reviewMeetingEmailSentFlag
                ),
                'showInactiveContactsURL'        => $showInactiveContactsURL,
                'showInactiveSitesURL'           => $showInactiveSitesURL,
                'customerID'                     => $this->getCustomerID() ? $this->getCustomerID() : 'null',
                'customerName'                   => $this->dsCustomer->getValue(DBECustomer::name),
                'deliverSiteNo'                  => $this->dsCustomer->getValue(DBECustomer::deliverSiteNo),
                'invoiceSiteNo'                  => $this->dsCustomer->getValue(DBECustomer::invoiceSiteNo),
                'reviewCount'                    => $this->buCustomer->getReviewCount(),
                'customerFolderLink'             => $customerFolderLink,
                'websiteURL'                     => $this->dsCustomer->getValue(DBECustomer::websiteURL),
                'customerNameClass'              => $this->dsCustomer->getValue(self::customerFormNameClass),
                'SectorMessage'                  => $this->dsCustomer->getValue(self::customerFormSectorMessage),
                'regNo'                          => $this->dsCustomer->getValue(DBECustomer::regNo),
                'mailshotFlagChecked'            => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::mailshotFlag)
                ),
                'referredFlagChecked'            => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::referredFlag)
                ),
                'specialAttentionFlagChecked'    => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::specialAttentionFlag)
                ),
                'specialAttentionEndDate'        => $this->dsCustomer->getValue(DBECustomer::specialAttentionEndDate),
                'specialAttentionEndDateMessage' => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'lastReviewMeetingDate'          => $this->dsCustomer->getValue(DBECustomer::lastReviewMeetingDate),
                'lastReviewMeetingDateMessage'   => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'reviewMeetingBookedChecked'     => $this->dsCustomer->getValue(
                    DBECustomer::reviewMeetingBooked
                ) ? 'checked' : null,
                'support24HourFlagChecked'       => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::support24HourFlag)
                ),
                'pcxFlagChecked'                 => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::pcxFlag)
                ),
                'createDate'                     => $this->dsCustomer->getValue(DBECustomer::createDate),
                'mailshot2FlagDesc'              => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot2FlagDesc
                ),
                'mailshot3FlagDesc'              => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot3FlagDesc
                ),
                'mailshot4FlagDesc'              => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot4FlagDesc
                ),
                'mailshot8FlagDesc'              => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot8FlagDesc
                ),
                'mailshot9FlagDesc'              => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot9FlagDesc
                ),
                'mailshot11FlagDesc'             => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot11FlagDesc
                ),
                'submitURL'                      => $submitURL,
                'renewalLink'                    => $renewalLink,
                'passwordLink'                   => $passwordLink,
                'thirdPartyContactsLink'         => $thirdPartyLink,
                'deleteCustomerURL'              => $deleteCustomerURL,
                'deleteCustomerText'             => $deleteCustomerText,
                'cancelURL'                      => $cancelURL,
                'disabled'                       => $this->hasPermissions(
                    SALES_PERMISSION
                ) ? null : CTCNC_HTML_DISABLED,
                'gscTopUpAmount'                 => $this->dsCustomer->getValue(DBECustomer::gscTopUpAmount),
                'noOfServers'                    => $this->dsCustomer->getValue(DBECustomer::noOfServers),
                'activeDirectoryName'            => $this->dsCustomer->getValue(DBECustomer::activeDirectoryName),
                'noOfSites'                      => $this->dsCustomer->getValue(DBECustomer::noOfSites),
                'noOfPCs'                        => $this->dsCustomer->getValue(DBECustomer::noOfPCs),
                'modifyDate'                     => $this->dsCustomer->getValue(DBECustomer::modifyDate),
                'reviewDate'                     => $this->dsCustomer->getValue(DBECustomer::reviewDate),
                'reviewTime'                     => $this->dsCustomer->getValue(DBECustomer::reviewTime),
                'becameCustomerDate'             => $this->dsCustomer->getValue(DBECustomer::becameCustomerDate),
                'droppedCustomerDate'            => $this->dsCustomer->getValue(DBECustomer::droppedCustomerDate),
                'reviewAction'                   => $this->dsCustomer->getValue(DBECustomer::reviewAction),
                'comments'                       => $this->dsCustomer->getValue(DBECustomer::comments),
                'techNotes'                      => $this->dsCustomer->getValue(DBECustomer::techNotes),
                'slaP1'                          => $this->dsCustomer->getValue(DBECustomer::slaP1),
                'slaP2'                          => $this->dsCustomer->getValue(DBECustomer::slaP2),
                'slaP3'                          => $this->dsCustomer->getValue(DBECustomer::slaP3),
                'slaP4'                          => $this->dsCustomer->getValue(DBECustomer::slaP4),
                'slaP5'                          => $this->dsCustomer->getValue(DBECustomer::slaP5),
                'isShowingInactive'              => $this->getParam('showInactiveContacts') ? 'true' : 'false',
                'primaryMainMandatory'           => count($mainContacts) ? 'required' : null,
                'sortCode'                       => $this->dsCustomer->getValue(DBECustomer::sortCode),
                'accountName'                    => $this->dsCustomer->getValue(DBECustomer::accountName),
                'accountNumber'                  => $this->dsCustomer->getValue(DBECustomer::accountNumber),
                'sortCodePencilColor'            => $this->dsCustomer->getValue(
                    DBECustomer::sortCode
                ) ? "greenPencil" : "redPencil",
                'accountNumberPencilColor'       => $this->dsCustomer->getValue(
                    DBECustomer::accountNumber
                ) ? "greenPencil" : "redPencil",
                'forceDirectDebit'               => $forceDirectDebit ? 'true' : 'false'

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
                $this->buCustomer->addNewSiteRow($this->getCustomerID());
            }
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

        $mainCount = 0;
        $supervisorCount = 0;
        $supportCount = 0;
        $delegateCount = 0;
        $furloughCount = 0;
        $totalCount = 0;

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
            $dearJohnURL = null;
            $dmLetterURL = null;
            $deleteContactLink = null;

            if ($this->dsContact->getValue(DBEContact::contactID)) {

                if ($this->dsContact->getValue(DBEContact::supportLevel)) {

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
                    }
                    $totalCount++;
                }

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

                $orderURL =
                    Controller::buildLink(
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

        $this->template->parse(
            'CONTENTS',
            'CustomerEdit',
            true
        );
        $this->parsePage();
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

    function siteDropdown(
        $customerID,
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
            $siteDesc = $dbeSite->getValue(DBESite::siteNo);

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

        $nextURL =
            Controller::buildLink(
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

            $nextURL =
                Controller::buildLink(
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
        $password = $this->getParam('password');

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

    function getCustomerProjectsController()
    {
        $response = [];
        try {
            $response['data'] = $this->getCustomerProjects($_REQUEST['customerId']);
            $response["status"] = "ok";
        } catch (Exception $exception) {
            http_response_code(400);
            $response["status"] = "error";
            $response["error"] = $exception->getMessage();
        }

        echo json_encode($response);
    }

    function getCustomerProjects($customerId)
    {
        $buProject = new BUProject($this);
        $dsResults = new DataSet($this);
        $buProject->getProjectsByCustomerID($customerId, $dsResults);
        $projects = [];
        while ($dsResults->fetchNext()) {
            $projects[] = [
                "id"          => $dsResults->getValue(DBEProject::projectID),
                "name"        => $dsResults->getValue(DBEProject::description),
                "notes"       => $dsResults->getValue(DBEProject::notes),
                "startDate"   => $dsResults->getValue(DBEProject::commenceDate),
                "expiryDate"  => $dsResults->getValue(DBEProject::completedDate),
                "isDeletable" => $buProject->canDelete($dsResults->getValue(DBEProject::projectID)),
            ];
        }
        return $projects;
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
                'firstName' => $this->dbeUser->getValue(DBEJUser::firstName),
                'lastName'  => $this->dbeUser->getValue(DBEJUser::lastName),
                'id'        => $this->dbeUser->getValue(DBEJUser::userID),
                'email'     => $this->dbeUser->getEmail()
            ]
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

                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($this->getCustomerID());
                $customerData = $_REQUEST['form']['customer'];
                $dbeCustomer->setValue(DBECustomer::reviewDate, $customerData['reviewDate']);
                $dbeCustomer->setValue(DBECustomer::reviewTime, $customerData['reviewTime']);
                $dbeCustomer->setValue(DBECustomer::reviewUserID, $customerData['reviewUserID']);
                $dbeCustomer->setValue(DBECustomer::reviewAction, $customerData['reviewAction']);
                $dbeCustomer->updateRow();
//                $this->buCustomer->updateSite($this->dsSite);
                if (isset($this->postVars["form"]["contact"])) {
                    $this->buCustomer->updateContact($this->dsContact);
                }
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
