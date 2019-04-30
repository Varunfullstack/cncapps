<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;

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
    const DECRYPT = "decrypt";
    public $customerID;
    public $customerString;                      // Used when searching for an entity by string
    public $contactString;                      // Used when searching for an entity by string
    public $phoneString;                      // Used when searching for an entity by string
    public $newCustomerFromDate;                      // Used when searching for an entity by string
    public $newCustomerToDate;                      // Used when searching for an entity by string
    public $droppedCustomerFromDate;                      // Used when searching for an entity by string
    public $droppedCustomerToDate;                      // Used when searching for an entity by string
    public $address;                                // Used when searching for customer
    public $buCustomer;
    public $customerStringMessage;
    public $dsCustomer;
    public $dsContact;
    public $dsSite;
    public $siteNo;
    public $contactID;
    public $dsHeader;

    const contactFormTitleClass = 'TitleClass';
    const contactFormFirstNameClass = 'FirstNameClass';
    const contactFormLastNameClass = 'LastNameClass';
    const contactFormEmailClass = 'EmailClass';
    const contactFormHasPassword = 'hasPassword';

    const siteFormAdd1Class = 'Add1Class';
    const siteFormTownClass = 'TownClass';
    const siteFormPostcodeClass = 'PostcodeClass';

    const customerFormNameClass = 'NameClass';
    const customerFormInvoiceSiteMessage = 'InvoiceSiteMessage';
    const customerFormDeliverSiteMessage = 'DeliverSiteMessage';
    const customerFormSectorMessage = 'SectorMessage';
    const customerFormSpecialAttentionEndDateMessage = 'specialAttentionEndDateMessage';
    const customerFormLastReviewMeetingDateMessage = 'lastReviewMeetingDateMessage';


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
            "reports",
            "technical"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
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
        foreach ($contactArray as $key => $value) {

            if (@$value['contactID']) {

                $dbeContact = new DBEContact($this);
                $dbeContact->getRow($value['contactID']);

                $this->dsContact->setValue(
                    DBEContact::portalPassword,
                    $dbeContact->getValue(DBEContact::portalPassword)
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
                common_convertDateDMYToYMD(@$value[DBEContact::pendingLeaverDate])
            );

            // Determine whether a new contact is to be added
            if (!$this->dsContact->getValue(DBEContact::contactID)) {
                if (
                    ($this->dsContact->getValue(DBEContact::title)) |
                    ($this->dsContact->getValue(DBEContact::firstName)) |
                    ($this->dsContact->getValue(DBEContact::lastName))
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
                DBECustomer::regNo,
                @$value['regNo']
            );
            $this->dsCustomer->setValue(
                DBECustomer::invoiceSiteNo,
                @$value['invoiceSiteNo']
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
                $this->convertDateYMD(@$value['specialAttentionEndDate'])
            );

            if (
                $this->dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &
                $this->dsCustomer->getValue(DBECustomer::specialAttentionEndDate)
            ) {
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
                DBECustomer::leadStatusID,
                @$value['leadStatusID']
            );
            $this->dsCustomer->setValue(
                DBECustomer::prospectFlag,
                $this->getYN(@$value['prospectFlag'])
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
                $this->convertDateYMD(@$value['becameCustomerDate'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::droppedCustomerDate,
                $this->convertDateYMD(@$value['droppedCustomerDate'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::lastReviewMeetingDate,
                $this->convertDateYMD(@$value['lastReviewMeetingDate'])
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewMeetingFrequencyMonths,
                @$value['reviewMeetingFrequencyMonths']
            );
            $this->dsCustomer->setValue(
                DBECustomer::reviewDate,
                $this->convertDateYMD(@$value['reviewDate'])
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
        return ($flag == 'N' ? null : CT_CHECKED);
    }

    function convertDateYMD($dateDMY)
    {
        if ($dateDMY) {
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
            return null;
        }
    }

    function getOrderTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
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
            case 'displayContractAndNumbersReport':
                $this->displayContractAndNumbersReport();
                break;
            case 'csvContractAndNumbersReport':
                $this->csvContractAndNumbersReport();
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


    private function getContractAndNumberData()
    {
        global $db; //PHPLib DB object


        $queryString =
            "SELECT
  `cus_custno`,
  cus_name AS customerName,
  serviceDeskProduct,
  COALESCE(serviceDeskUsers,0) AS serviceDeskUsers,
  COALESCE(serviceDeskContract,0) AS serviceDeskContract,
  COALESCE(serviceDeskCostPerUserMonth,0) AS serviceDeskCostPerUserMonth,
  serverCareProduct,
  COALESCE(virtualServers,0) AS virtualServers,
  COALESCE(physicalServers,0) AS physicalServers,
  COALESCE(serverCareContract,0) AS serverCareContract
FROM
  customer
  LEFT JOIN
  (SELECT
     `cui_custno`                      AS customerId,
     itm_desc                          AS serviceDeskProduct,
     custitem.`cui_users`              AS serviceDeskUsers,
     round(custitem.cui_sale_price, 0) AS serviceDeskContract,
     ROUND(
         custitem.cui_sale_price / custitem.cui_users / 12,
         2
     )                                 AS serviceDeskCostPerUserMonth
   FROM
     custitem
     LEFT JOIN item
       ON item.`itm_itemno` = custitem.`cui_itemno`
   WHERE itm_desc LIKE '%servicedesk%'
         AND itm_discontinued <> 'Y'
         AND custitem.`declinedFlag` <> 'Y') AS test1
    ON test1.customerId = customer.`cus_custno`
  LEFT JOIN
  (SELECT
     custitem.`cui_custno`               AS customerId,
     item.itm_desc                       AS serverCareProduct,
     SUM(
         serverItem.`itm_desc` LIKE '%virtual%'
     )                                   AS virtualServers,
     SUM(
         serverItem.itm_desc NOT LIKE '%virtual%'
     )                                   AS physicalServers,
     round(custitem.`cui_sale_price`, 0) AS serverCareContract
   FROM
     custitem
     LEFT JOIN item
       ON item.`itm_itemno` = custitem.`cui_itemno`
     LEFT JOIN custitem_contract
       ON custitem_contract.`cic_contractcuino` = cui_cuino
     LEFT JOIN custitem AS servers
       ON custitem_contract.`cic_cuino` = servers.cui_cuino
     LEFT JOIN item AS serverItem
       ON servers.cui_itemno = serverItem.`itm_itemno`
   WHERE item.`itm_desc` LIKE '%servercare%'
         AND item.itm_discontinued <> 'Y'
         AND custitem.`declinedFlag` <> 'Y'
   GROUP BY custitem.`cui_cuino`) test2
    ON customer.cus_custno = test2.customerId
WHERE serviceDeskProduct IS NOT NULL OR serverCareProduct IS NOT NULL
ORDER BY cus_name ASC  ";

        $db->query($queryString);
        return $db;
    }

    function csvContractAndNumbersReport()
    {
        $csv_export = '';
        $db = $this->getContractAndNumberData();

        $headersSet = false;

        while ($db->next_record()) {
            $row = $db->Record;
            if (!$headersSet) {
                foreach (array_keys($row) as $key) {
                    if (!is_numeric($key)) {
                        $csv_export .= $key . ';';
                    }
                }
            }
            $this->template->set_var(
                array(
                    'customerName'                => $row["customerName"],
                    'serviceDeskProduct'          => $row['serviceDeskProduct'],
                    'serviceDeskUsers'            => $row['serviceDeskUsers'],
                    'serviceDeskContract'         => $row['serviceDeskContract'],
                    'serviceDeskCostPerUserMonth' => $row['serviceDeskCostPerUserMonth'],
                    'serverCareProduct'           => $row['serverCareProduct'],
                    'virtualServers'              => $row['virtualServers'],
                    'physicalServers'             => $row['physicalServers'],
                    'serverCareContract'          => $row['serverCareContract']

                )
            );

            $this->template->parse(
                'contracts',
                'contractItemBlock',
                true
            );

        }
    }

    /**
     * @throws Exception
     */
    function displayContractAndNumbersReport()
    {

        $this->setPageTitle("Service Contracts Ratio");

        $this->setTemplateFiles(
            'ContractAndNumbersReport',
            'ContractAndNumbersReport'
        );


        $db = $this->getContractAndNumberData();


        $this->template->set_block(
            'ContractAndNumbersReport',
            'contractItemBlock',
            'contracts'
        );

        while ($db->next_record()) {
            $row = $db->Record;
            $this->template->set_var(
                array(
                    'customerName'                => $row["customerName"],
                    'serviceDeskProduct'          => $row['serviceDeskProduct'],
                    'serviceDeskUsers'            => $row['serviceDeskUsers'],
                    'serviceDeskContract'         => $row['serviceDeskContract'],
                    'serviceDeskCostPerUserMonth' => $row['serviceDeskCostPerUserMonth'],
                    'serverCareProduct'           => $row['serverCareProduct'],
                    'virtualServers'              => $row['virtualServers'],
                    'physicalServers'             => $row['physicalServers'],
                    'serverCareContract'          => $row['serverCareContract']

                )
            );

            $this->template->parse(
                'contracts',
                'contractItemBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'ContractAndNumbersReport',
            true
        );


        $this->parsePage();

        exit;
    }

    /**
     * Displays list of customers with 24 Hour Support
     *
     * @throws Exception
     */
    function display24HourSupportCustomers()
    {
        $this->setMethodName('display24HourSupportCustomers');

        $this->setPageTitle("24 Hour Support Customers");
        $dsCustomer = new DataSet($this);
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
                    Controller::buildLink(
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
     * @throws Exception
     */
    function displaySpecialAttentionCustomers()
    {
        $this->setMethodName('displaySpecialAttentionCustomers');

        $this->setPageTitle("Special Attention Customers");
        global $cfg;
        $customerTemplate = new Template (
            $cfg["path_templates"],
            "remove"
        );

        $contactTemplate = new Template(
            $cfg["path_templates"],
            "remove"
        );


        $this->setTemplateFiles(
            'SpecialAttention',
            'SpecialAttention'
        );

        $buContact = new BUContact($this);
        $dsContact = new DataSet($this);
        if ($buContact->getSpecialAttentionContacts($dsContact)) {
            $contactTemplate->setFile(
                'ContactSpecialAttention',
                'ContactSpecialAttention.html'
            );

            $contactTemplate->set_block(
                'ContactSpecialAttention',
                'contactBlock',
                'contacts'
            );
            $dbeCustomer = new DBECustomer($this);
            while ($dsContact->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsContact->getValue(DBEContact::customerID)
                        )
                    );

                if ($dbeCustomer->getValue(DBECustomer::customerID) != $dsContact->getValue(DBEContact::customerID)) {
                    $dbeCustomer->getRow($dsContact->getValue(DBEContact::customerID));
                }

                $contactTemplate->set_var(
                    array(
                        'contactName'  => ($dsContact->getValue(DBEContact::firstName) . " " . $dsContact->getValue(
                                DBEContact::lastName
                            )),
                        'linkURL'      => $linkURL,
                        'customerName' => $dbeCustomer->getValue(DBECustomer::name)
                    )
                );

                $contactTemplate->parse(
                    'contacts',
                    'contactBlock',
                    true
                );

            }

            $contactTemplate->parse(
                'OUTPUT',
                'ContactSpecialAttention',
                true
            );


        } else {
            $contactTemplate->setFile(
                'SimpleMessage',
                'SimpleMessage.inc.html'
            );

            $contactTemplate->set_var(array('message' => 'There are no special attention contacts'));

            $contactTemplate->parse(
                'OUTPUT',
                'SimpleMessage',
                true
            );
        };
        $dsCustomer = new DataSet($this);
        if ($this->buCustomer->getSpecialAttentionCustomers($dsCustomer)) {


            $customerTemplate->setFile(
                'CustomerSpecialAttention',
                'CustomerSpecialAttention.inc.html'
            );

            $customerTemplate->set_block(
                'CustomerSpecialAttention',
                'customerBlock',
                'customers'
            );

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $customerTemplate->set_var(
                    array(
                        'customerName'            => $dsCustomer->getValue(DBECustomer::name),
                        'specialAttentionEndDate' => $dsCustomer->getValue(DBECustomer::specialAttentionEndDate),
                        'linkURL'                 => $linkURL
                    )
                );

                $customerTemplate->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $customerTemplate->parse(
                'OUTPUT',
                'CustomerSpecialAttention',
                true
            );

        } else {

            $customerTemplate->setFile(
                'SimpleMessage',
                'SimpleMessage.inc.html'
            );

            $customerTemplate->set_var(array('message' => 'There are no special attention customers'));

            $customerTemplate->parse(
                'OUTPUT',
                'SimpleMessage',
                true
            );
        }

        $this->template->setVar(
            [
                "customerSpecialAttention" => $customerTemplate->getVar('OUTPUT'),
                "contactSpecialAttention"  => $contactTemplate->getVar('OUTPUT')
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'SpecialAttention'
        );

        $this->parsePage();

        exit;
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
            $this->convertDateYMD($this->getNewCustomerFromDate()),
            $this->convertDateYMD($this->getNewCustomerToDate()),
            $this->convertDateYMD($this->getDroppedCustomerFromDate()),
            $this->convertDateYMD($this->getDroppedCustomerToDate())
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
            'CustomerEditSimple.inc'
        );


// Parameters
        $this->setPageTitle("Customer");
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
        $bodyTagExtras = 'onLoad="loadNote(\'last\')"';

        $urlContactPopup =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
//          'action' => CTCNC_ACT_CONTACT_EDIT,
'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $mainContacts = $this->buCustomer->getMainSupportContacts($this->dsCustomer->getValue(DBECustomer::customerID));
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

        $forceDirectDebit = $buItem->clientHasDirectDebit($this->dsCustomer->getValue(DBECustomer::customerID));


        $this->template->set_var(
            array(
                'lastContractSent'               => $this->dsCustomer->getValue(DBECustomer::lastContractSent),
                'urlContactPopup'                => $urlContactPopup,
                'bodyTagExtras'                  => $bodyTagExtras,
                /* hidden */
                'reviewMeetingEmailSentFlag'     => $this->dsCustomer->getValue(
                    DBECustomer::reviewMeetingEmailSentFlag
                ),
                'customerNotePopupLink'          => $this->getCustomerNotePopupLink($this->getCustomerID()),
                'showInactiveContactsURL'        => $showInactiveContactsURL,
                'showInactiveSitesURL'           => $showInactiveSitesURL,
                'customerID'                     => $this->dsCustomer->getValue(DBECustomer::customerID),
                'customerName'                   => $this->dsCustomer->getValue(DBECustomer::name),
                'reviewCount'                    => $this->buCustomer->getReviewCount(),
                'customerFolderLink'             => $customerFolderLink,
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
                'specialAttentionEndDate'        => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::specialAttentionEndDate)
                ),
                'specialAttentionEndDateMessage' => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'lastReviewMeetingDate'          => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::lastReviewMeetingDate)
                ),
                'lastReviewMeetingDateMessage'   => $this->dsCustomer->getValue(
                    self::customerFormSpecialAttentionEndDateMessage
                ),
                'support24HourFlagChecked'       => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::support24HourFlag)
                ),
                'prospectFlagChecked'            => $this->getChecked(
                    $this->dsCustomer->getValue(DBECustomer::prospectFlag)
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
                    PHPLIB_PERM_SALES
                ) ? null : CTCNC_HTML_DISABLED,
                'gscTopUpAmount'                 => $this->dsCustomer->getValue(DBECustomer::gscTopUpAmount),
                'noOfServers'                    => $this->dsCustomer->getValue(DBECustomer::noOfServers),
                'activeDirectoryName'            => $this->dsCustomer->getValue(DBECustomer::activeDirectoryName),
                'noOfSites'                      => $this->dsCustomer->getValue(DBECustomer::noOfSites),
                'modifyDate'                     => $this->dsCustomer->getValue(DBECustomer::modifyDate),
                'reviewDate'                     => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::reviewDate)
                ),
                'reviewTime'                     => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::reviewTime)
                ),
                'becameCustomerDate'             => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::becameCustomerDate)
                ),
                'droppedCustomerDate'            => Controller::dateYMDtoDMY(
                    $this->dsCustomer->getValue(DBECustomer::droppedCustomerDate)
                ),
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
                    'leadStatusID'          => $dsLeadStatus->getValue(DBELeadStatus::leadStatusID),
                    'leadStatusDescription' => $dsLeadStatus->getValue(DBELeadStatus::description),
                    'leadStatusSelected'    => ($dsLeadStatus->getValue(
                            DBELeadStatus::leadStatusID
                        ) == $this->dsCustomer->getValue(
                            DBECustomer::leadStatusID
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
            $addContactURL =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTCUSTOMER_ACT_ADDCONTACT,
                        'customerID' => $this->dsSite->getValue(DBESite::customerID),
                        'siteNo'     => $this->dsSite->getValue(DBESite::siteNo)
                    )
                );
            $deleteSiteURL = null;
            $deleteSiteText = null;
            // If we can delete this site set the link
            if ($this->buCustomer->canDeleteSite(
                $this->dsSite->getValue(DBESite::customerID),
                $this->dsSite->getValue(DBESite::siteNo)
            )) {
                $deleteSiteURL = Controller::buildLink(
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
                    'add1Class'      => $this->dsSite->getValue(self::siteFormAdd1Class),
                    'add1'           => $this->dsSite->getValue(DBESite::add1),
                    'add2'           => $this->dsSite->getValue(DBESite::add2),
                    'add3'           => $this->dsSite->getValue(DBESite::add3),
                    'townClass'      => $this->dsSite->getValue(self::siteFormTownClass),
                    'town'           => $this->dsSite->getValue(DBESite::town),
                    'county'         => $this->dsSite->getValue(DBESite::county),
                    'postcodeClass'  => $this->dsSite->getValue(self::siteFormPostcodeClass),
                    'postcode'       => $this->dsSite->getValue(DBESite::postcode),
                    'sitePhone'      => $this->dsSite->getValue(DBESite::phone),
                    'siteNo'         => $this->dsSite->getValue(DBESite::siteNo),
                    'customerID'     => $this->dsSite->getValue(DBESite::customerID),
                    'sageRef'        => $this->dsSite->getValue(DBESite::sageRef),
                    'debtorCode'     => $this->dsSite->getValue(DBESite::debtorCode),
                    'maxTravelHours' => $this->dsSite->getValue(DBESite::maxTravelHours),

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
                    'pendingLeaverDate'                    => Controller::dateYMDtoDMY(
                        $this->dsContact->getValue(DBEContact::pendingLeaverDate)
                    ),
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
                    'deleteContactLink'                    => $deleteContactLink
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
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getCustomerNotePopupLink($customerID)
    {
        if (!$customerID) {
            return null;
        }
        $url =
            Controller::buildLink(
                'CustomerNote.php',
                array(
                    'action'     => 'customerNoteHistoryPopup',
                    'customerID' => $customerID,
                    'htmlFmt'    => CT_HTML_FMT_POPUP
                )
            );

        return '<A HREF="' . $url . ' " target="_blank" >Notes History</A>';
    }

    function siteDropdown(
        $customerID,
        $siteNo,
        $templateName = "selectSites",
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

    } // end siteDropdown

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

            $urlAddDocument =
                Controller::buildLink(
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
                    Controller::buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action'                   => 'edit',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                                DBEPortalCustomerDocument::portalCustomerDocumentID
                            )
                        )
                    );

                $urlViewFile =
                    Controller::buildLink(
                        'PortalCustomerDocument.php',
                        array(
                            'action'                   => 'viewFile',
                            'portalCustomerDocumentID' => $dsPortalCustomerDocument->getValue(
                                DBEPortalCustomerDocument::portalCustomerDocumentID
                            )
                        )
                    );

                $urlDeleteDocument =
                    Controller::buildLink(
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
                        'startersFormFlag'    => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::startersFormFlag
                        ),
                        'leaversFormFlag'     => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::leaversFormFlag
                        ),
                        'mainContactOnlyFlag' => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::mainContactOnlyFlag
                        ),
                        'createDate'          => $dsPortalCustomerDocument->getValue(
                            DBEPortalCustomerDocument::createdDate
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

        $dbeContact->setValue(
            DBEContact::email,
            null
        );
        $dbeContact->setValue(
            DBEContact::email,
            null
        );
        $dbeContact->setValue(
            DBEContact::supportLevel,
            null
        );
        $dbeContact->setValue(
            DBEContact::reviewUser,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::hrUser,
            "N"
        );

        $dbeContact->setValue(
            DBEContact::sendMailshotFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::discontinuedFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::accountsFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot2Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot3Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot4Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot8Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot9Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::mailshot11Flag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::initialLoggingEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::workStartedEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::workUpdatesEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::fixedEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::pendingClosureEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::closureEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersInitialLoggingEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersWorkStartedEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersWorkUpdatesEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersFixedEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersPendingClosureEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::othersClosureEmailFlag,
            "N"
        );
        $dbeContact->setValue(
            DBEContact::pendingLeaverFlag,
            "N"
        );

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
}
