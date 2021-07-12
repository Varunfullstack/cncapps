<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\Business\BUActivity;
use CNCLTD\Data\DBConnect;
use CNCLTD\Data\DBEJProblem;
use CNCLTD\Encryption;
use CNCLTD\Exceptions\APIException;
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
require_once($cfg["path_bu"] . "/Burencontract.php");
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
    const GET_CUSTOMER_REVIEW_CONTACTS               = "getCustomerReviewContacts";
    const GET_CUSTOMER_PROJECTS                      = 'getCustomerProjects';
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
    const GET_PORTAL_CUSTOMER_DOCUMENTS              = 'getPortalCustomerDocuments';
    const GET_CUSTOMER_SITES                         = "getSites";
    const GET_CUSTOMER_CONTACTS                      = "getContacts";
    const UPDATE_SITE                                = "updateSite";
    const GET_CUSTOMER_ORDERS                        = 'getCustomerOrders';
    const GET_CUSTOMER_DATA                          = 'getCustomer';
    const ADD_PORTAL_CUSTOMER_DOCUMENT               = 'addPortalCustomerDocument';
    const DELETE_PORTAL_DOCUMENT                     = "deletePortalDocument";
    const ADD_SITE                                   = "addSite";
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
                DBEContact::mailshot2Flag,
                $this->getYN(@$value['mailshot2Flag'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot3Flag,
                $this->getYN(@$value['mailshot3Flag'])
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
                DBEContact::initialLoggingEmail,
                @$value['initialLoggingEmail']
            );
            $this->dsContact->setValue(
                DBEContact::othersInitialLoggingEmailFlag,
                $this->getYN(@$value['othersInitialLoggingEmailFlag'])
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
                DBEContact::reviewUser,
                $this->getYN(@$value['reviewUser'])
            );
            $this->dsContact->setValue(
                DBEContact::hrUser,
                $this->getYN(@$value['hrUser'])
            );
            $this->dsContact->setValue(
                DBEContact::mailshot,
                @$value['mailshot']
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
                $this->getEncrypt();
                exit;
            }
            case self::GET_CUSTOMER_ORDERS:
            {
                $this->getCustomerOrdersController();
                exit;
            }
            case self::GET_CUSTOMER_DATA:
            {
                $this->getCustomerData();
                break;
            }
            case 'getMainContacts':
            {
                $this->_getMainContacts(@$_REQUEST['customerID']);
                break;
            }
            case 'getLeadStatuses':
            {
                $this->getLeadStatuses();
                break;
            }
            case 'updateCustomer':
            {
                echo json_encode($this->updateCustomer(),JSON_NUMERIC_CHECK);
                break;
            }
            case self::UPDATE_SITE:
            {
                $this->updateSite($this->getJSONData()); 
                exit;
            }
            case 'addContact':
                {
                     $this->addContact($this->getJSONData()); 
                    exit;
                }
            case 'updateContact':
            {
                 $this->updateContact($this->getJSONData()); 
                exit;
            }
            case self::DELETE_PORTAL_DOCUMENT:
            {
                $this->deletePortalDocument($this->getJSONData());               
                exit;
            }
            case self::ADD_PORTAL_CUSTOMER_DOCUMENT:
            {
                $this->addPortalCustomerDocument($this->getJSONData());
                exit;
            }
            case
            'getSectors':
            {
                $this->getSectors();
                break;
            }
            case 'getCustomerTypes':
            {
                $this->getCustomerTypes();
                break;
            }
            case 'getAccountManagers':
            {
                $this->getAccountManagers();
                break;
            }
            case 'getReviewEngineers':
                return $this->getReviewEngineersController();
            case 'getCustomerReviewData':
                return $this->getCustomerReviewDataController();
            case 'updateCustomerReview':
                return $this->updateCustomerReviewController();
            case self::GET_PORTAL_CUSTOMER_DOCUMENTS:
                return $this->getPortalCustomerDocumentsController($_REQUEST['customerID']);
            case 'createCustomerFolder':
                $this->createCustomerFolder();
                break;
            case 'displayReviewList':
                $this->displayReviewList();
                break;
            case self::GET_CUSTOMER_SITES:
                return $this->getCustomerSitesController();
            case self::GET_CUSTOMER_CONTACTS:
                return $this->getCustomerContactsController();
            case self::ADD_SITE:
                $this->addSite($this->getJSONData());
                exit;
            case CTCUSTOMER_ACT_SEARCH:
                $this->search();
                break;
            case CTCUSTOMER_ACT_ADDCUSTOMER:
            case CTCUSTOMER_ACT_ADDCONTACT:
            case CTCUSTOMER_ACT_DISP_SUCCESS:
            case CTCNC_ACT_DISP_EDIT:
                //$this->displayEditForm();
                $this->displayForm();
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
                $this->_saveContactPassword();
                break;
            case 'archiveContact':
                $this->archiveContact();
                break;
            case self::GET_CUSTOMER_REVIEW_CONTACTS:
                $this->_getCustomerReviewContacts();
                break;
            case self::GET_CUSTOMER_PROJECTS:
                return $this->getCustomerProjectsController();
            case self::DECRYPT:
                $this->decrypt();
                break;
            case "removeSupportAndRefer":
                $this->removeSupportAndRefer(@$this->getParam('customerID'));
                break;
            case 'searchName':
                $this->searchName();
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
                echo json_encode($this->getCustomerProjects(@$_REQUEST['customerID']));
                exit;
                break;
            case "contracts":
                echo json_encode($this->getCustomerContracts(@$_REQUEST['customerID']));
                exit;
            case "getCustomersHaveOpenSR":
                echo json_encode($this->getCustomersHaveOpenSR());
                exit;
            case "letters":
                echo json_encode($this->getCustomeLetters(),JSON_NUMERIC_CHECK);
                exit;
            case "CRM":
                switch ($this->requestMethod) {
                    case 'POST':
                        echo $this->response($this->updateCRM());                        
                        break;                    
                    default:
                        # code...
                        break;
                }
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
        $customerId  = $this->getParam('customerId');
        $dbeCustomer = new DBECustomer($this);
        $orders      = [];
        if ($dbeCustomer->getRow($customerId) && $dbeCustomer->getValue(DBECustomer::referredFlag) != 'Y') {
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
                $orderURL  = Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => CTCNC_ACT_DISP_SALESORDER,
                        'ordheadID' => $ordheadID
                    )
                );
                $orders[]  = [
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

    private function getFileDecodedAndMimeTypeFromBase64EncodedFile($encodedFile)
    {
        $data     = base64_decode($encodedFile);
        $f        = finfo_open();
        $mimeType = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
        return (object)["file" => $data, "mimeType" => $mimeType];
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

    function getPortalCustomerDocumentsController($customerID)
    {
        if (!isset($customerID)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer ID is mandatory"]);
            exit;
        }
        echo json_encode(
            ["status" => "ok", "data" => $this->getPortalDocuments($customerID)]
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
            $documents[] = [
                'id'                  => $portalDocuments->getValue(
                    DBEPortalCustomerDocument::portalCustomerDocumentID
                ),
                'description'         => $portalDocuments->getValue(
                    DBEPortalCustomerDocumentWithoutFile::description
                ),
                'filename'            => $portalDocuments->getValue(
                    DBEPortalCustomerDocumentWithoutFile::filename
                ),
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

    function getCustomerSitesController()
    {
        if (!isset($_REQUEST['customerId'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer Id is required"]);
            exit;
        }
        $dbeSite    = new DBESite($this);
        $customerId = $_REQUEST['customerId'];
        $dbeSite->setValue(DBESite::customerID, $customerId);
        $dbeSite->getRowsByCustomerID(false);
        $sites = [];
        while ($dbeSite->fetchNext()) {
            $sites[] = [
                "customerID"          => $dbeSite->getValue(DBESite::customerID),
                "siteNo"              => $dbeSite->getValue(DBESite::siteNo),
                "address1"            => $dbeSite->getValue(DBESite::add1),
                "address2"            => $dbeSite->getValue(DBESite::add2),
                "address3"            => $dbeSite->getValue(DBESite::add3),
                "town"                => $dbeSite->getValue(DBESite::town),
                "county"              => $dbeSite->getValue(DBESite::county),
                "postcode"            => $dbeSite->getValue(DBESite::postcode),
                "invoiceContact"      => $dbeSite->getValue(DBESite::invoiceContactID),
                "deliverContact"      => $dbeSite->getValue(DBESite::deliverContactID),
                "debtorCode"          => $dbeSite->getValue(DBESite::debtorCode),
                "sageRef"             => $dbeSite->getValue(DBESite::sageRef),
                "phone"               => $dbeSite->getValue(DBESite::phone),
                "maxTravelHours"      => $dbeSite->getValue(DBESite::maxTravelHours),
                "active"              => $dbeSite->getValue(DBESite::activeFlag) == 'Y',
                "nonUKFlag"           => $dbeSite->getValue(DBESite::nonUKFlag) == 'Y',
                "what3Words"          => $dbeSite->getValue(DBESite::what3Words),
                "lastUpdatedDateTime" => $dbeSite->getValue(DBESite::lastUpdatedDateTime),
                "canDelete"           => $this->buCustomer->canDeleteSite(
                    $customerId,
                    $dbeSite->getValue(DBESite::siteNo)
                )
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
                "mailshot"                      => $contact->getValue(DBEContact::mailshot),
                "accountsFlag"                  => $contact->getValue(DBEContact::accountsFlag),
                "mailshot2Flag"                 => $contact->getValue(DBEContact::mailshot2Flag),
                "mailshot3Flag"                 => $contact->getValue(DBEContact::mailshot3Flag),
                "mailshot8Flag"                 => $contact->getValue(DBEContact::mailshot8Flag),
                "mailshot9Flag"                 => $contact->getValue(DBEContact::mailshot9Flag),
                "mailshot11Flag"                => $contact->getValue(DBEContact::mailshot11Flag),
                "notes"                         => $contact->getValue(DBEContact::notes),
                "failedLoginCount"              => $contact->getValue(DBEContact::failedLoginCount),
                "reviewUser"                    => $contact->getValue(DBEContact::reviewUser),
                "hrUser"                        => $contact->getValue(DBEContact::hrUser),
                "supportLevel"                  => $contact->getValue(DBEContact::supportLevel),
                "initialLoggingEmail"           => $contact->getValue(DBEContact::initialLoggingEmail),
                "othersInitialLoggingEmailFlag" => $contact->getValue(DBEContact::othersInitialLoggingEmailFlag),
                "othersWorkUpdatesEmailFlag"    => $contact->getValue(DBEContact::othersWorkUpdatesEmailFlag),
                "othersFixedEmailFlag"          => $contact->getValue(DBEContact::othersFixedEmailFlag),
                "pendingLeaverFlag"             => $contact->getValue(DBEContact::pendingLeaverFlag),
                "pendingLeaverDate"             => $contact->getValue(DBEContact::pendingLeaverDate),
                "specialAttentionContactFlag"   => $contact->getValue(DBEContact::specialAttentionContactFlag),
                "linkedInURL"                   => $contact->getValue(DBEContact::linkedInURL),
                "active"                        => $contact->getValue(DBEContact::active),
            ];
        }
        echo json_encode(["status" => "ok", "data" => $contacts]);
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
        $this->loadReactScript('CustomerEditComponent.js');
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
                /* hidden */ 'reviewMeetingEmailSentFlag' => $this->dsCustomer->getValue(
                DBECustomer::reviewMeetingEmailSentFlag
            ),
                'showInactiveContactsURL'                 => $showInactiveContactsURL,
                'showInactiveSitesURL'                    => $showInactiveSitesURL,
                'customerID'                              => $this->getCustomerID() ? $this->getCustomerID() : 'null',
                'customerName'                            => $this->dsCustomer->getValue(DBECustomer::name),
                'deliverSiteNo'                           => $this->dsCustomer->getValue(DBECustomer::deliverSiteNo),
                'invoiceSiteNo'                           => $this->dsCustomer->getValue(DBECustomer::invoiceSiteNo),
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

                'createDate'                              => $this->dsCustomer->getValue(DBECustomer::createDate),
                'mailshot2FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot2FlagDesc
                ),
                'mailshot3FlagDesc'                       => $this->buCustomer->dsHeader->getValue(
                    DBEHeader::mailshot3FlagDesc
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
                    'accountManagerUserSelected' => ($dsUser->getValue(
                            DBEUser::userID
                        ) == $this->dsCustomer->getValue(
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
        $data = $this->getJSONData();
        $this->setMethodName('deleteSite');
        if (empty($data['customerId'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Customer Id Required');
        }
        if (empty($data['siteNo'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Site Number Required');
        }
        if (!$this->buCustomer->canDeleteSite(
            $data['customerId'],
            $data['siteNo']
        )) {
            throw new \CNCLTD\Exceptions\JsonHttpException(403, 'Site Number Required');
        }
        $this->buCustomer->deleteSite(
            $data['customerId'],
            $data['siteNo']
        );
        echo json_encode(["status" => "ok"]);
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

    function getCustomerProjectsController()
    {
        $response = [];
        try {
            $response['data']   = $this->getCustomerProjects($_REQUEST['customerId']);
            $response["status"] = "ok";
        } catch (Exception $exception) {
            http_response_code(400);
            $response["status"] = "error";
            $response["error"]  = $exception->getMessage();
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
                'firstName'   => $this->dbeUser->getValue(DBEJUser::firstName),
                'lastName'    => $this->dbeUser->getValue(DBEJUser::lastName),
                'id'          => $this->dbeUser->getValue(DBEJUser::userID),
                'email'       => $this->dbeUser->getEmail(),
                'isSdManager' => $this->isSdManager(),
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
                    "emailSubjectSummary"    => $dsActiveSrs->getValue('emailSubjectSummary'),
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
        $showInActive= $_GET["showInActive"]??"N";
        if (!$customerId) return [];
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerId
        );        
        $dbeSite->getRowsByCustomerID($showInActive=='N');
        $sites = array();
        while ($dbeSite->fetchNext()) {
            $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                    DBESite::town
                ) . ' ' . $dbeSite->getValue(DBESite::postcode);
            $customerID = $dbeSite->getValue(DBESite::customerID);
            $siteNo   = $dbeSite->getValue(DBESite::siteNo);
            $add1     = $dbeSite->getValue(DBESite::add1);
            $add2     = $dbeSite->getValue(DBESite::add2);
            $add3     = $dbeSite->getValue(DBESite::add3);
            $town     = $dbeSite->getValue(DBESite::town);
            $county   = $dbeSite->getValue(DBESite::county);
            $postcode = $dbeSite->getValue(DBESite::postcode);
            $invoiceContactID = $dbeSite->getValue(DBESite::invoiceContactID);
            $deliverContactID = $dbeSite->getValue(DBESite::deliverContactID);
            $debtorCode       = $dbeSite->getValue(DBESite::debtorCode);
            $sageRef          = $dbeSite->getValue(DBESite::sageRef);
            $phone            = $dbeSite->getValue(DBESite::phone);
            $maxTravelHours   = $dbeSite->getValue(DBESite::maxTravelHours);
            $activeFlag       = $dbeSite->getValue(DBESite::activeFlag);
            $nonUKFlag        = $dbeSite->getValue(DBESite::nonUKFlag);
            $what3Words       = $dbeSite->getValue(DBESite::what3Words);
            $lastUpdatedDateTime   = $dbeSite->getValue(DBESite::lastUpdatedDateTime);
            array_push($sites, [
                "id"     => $siteNo, 
                "customerID" => $customerID, 
                "title"  => $siteDesc,
                "siteNo" => $siteNo,
                "add1"   => $add1,
                "add2"   => $add2,
                "add3"   => $add3,
                "town"   => $town,
                "county" => $county,
                "postcode" => $postcode,
                "invoiceContactID" => $invoiceContactID,
                "deliverContactID" => $deliverContactID,
                "debtorCode" => $debtorCode,
                "sageRef"    => $sageRef,
                "phone"      => $phone,
                "maxTravelHours"      => $maxTravelHours,
                "activeFlag"          => $activeFlag,
                "what3Words"          => $what3Words,
                "lastUpdatedDateTime" => $lastUpdatedDateTime,
            ]);
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
        $customerAssets                   = $statement->fetchAll(PDO::FETCH_ASSOC);
        $unsupportedCustomerAssetsService = new UnsupportedCustomerAssetService();
        $unsupportedCustomerAssets        = $unsupportedCustomerAssetsService->getAllForCustomer($customerId);
        foreach ($customerAssets as $key => $customerAsset) {
            $customerAssets[$key]['unsupported'] = in_array($customerAsset['name'], $unsupportedCustomerAssets);
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
                    'customerID'   => $dbeContact->getValue(DBEContact::customerID),
                    'title'        => $dbeContact->getValue(DBEContact::title),
                    'position'     => $dbeContact->getValue(DBEContact::position),
                    'firstName'    => $dbeContact->getValue(DBEContact::firstName),
                    'lastName'     => $dbeContact->getValue(DBEContact::lastName),
                    "email"        => $dbeContact->getValue(DBEContact::email),
                    "phone"        => $dbeContact->getValue(DBEContact::phone),
                    "mobilePhone"  => $dbeContact->getValue(DBEContact::mobilePhone),
                    "fax"          => $dbeContact->getValue(DBEContact::fax),
                    "portalPassword"    => $dbeContact->getValue(DBEContact::portalPassword),
                    "mailshot"          => $dbeContact->getValue(DBEContact::mailshot),
                    //"discontinuedFlag"  => $dbeContact->getValue(DBEContact::discontinuedFlag),
                    //"accountsFlag"      => $dbeContact->getValue(DBEContact::accountsFlag),
                    "mailshot2Flag"     => $dbeContact->getValue(DBEContact::mailshot2Flag),
                    "mailshot3Flag"     => $dbeContact->getValue(DBEContact::mailshot3Flag),
                    "mailshot8Flag"     => $dbeContact->getValue(DBEContact::mailshot8Flag),
                    "mailshot9Flag"     => $dbeContact->getValue(DBEContact::mailshot9Flag),
                    "mailshot11Flag"    => $dbeContact->getValue(DBEContact::mailshot11Flag),
                    "notes"             => $dbeContact->getValue(DBEContact::notes),
                    "failedLoginCount"  => $dbeContact->getValue(DBEContact::failedLoginCount),
                    "reviewUser"        => $dbeContact->getValue(DBEContact::reviewUser),
                    "hrUser"            => $dbeContact->getValue(DBEContact::hrUser),
                    "notes"             => $dbeContact->getValue(DBEContact::notes),
                    'supportLevel'      => $dbeContact->getValue(DBEContact::supportLevel),
                    //'main'              => $dbeContact->getValue(DBEContact::supportLevelMain),
                    //'supervisor'        => $dbeContact->getValue(DBEContact::supportLevelSupervisor),
                    //'support'           => $dbeContact->getValue(DBEContact::supportLevelSupport),
                    //'delegate'          => $dbeContact->getValue(DBEContact::supportLevelDelegate),
                    //'furlough'          => $dbeContact->getValue(DBEContact::supportLevelFurlough),
                    'initialLoggingEmail' => $dbeContact->getValue(DBEContact::initialLoggingEmail),
                    'othersInitialLoggingEmailFlag' => $dbeContact->getValue(DBEContact::othersInitialLoggingEmailFlag),
                    'othersWorkUpdatesEmailFlag'    => $dbeContact->getValue(DBEContact::othersWorkUpdatesEmailFlag),
                    'othersFixedEmailFlag'          => $dbeContact->getValue(DBEContact::othersFixedEmailFlag),
                    'pendingLeaverFlag'             => $dbeContact->getValue(DBEContact::pendingLeaverFlag),
                    'pendingLeaverDate'             => $dbeContact->getValue(DBEContact::pendingLeaverDate),
                    'specialAttentionContactFlag'   => $dbeContact->getValue(DBEContact::specialAttentionContactFlag),
                    'linkedInURL'                   => $dbeContact->getValue(DBEContact::linkedInURL),
                    'pendingFurloughAction'         => $dbeContact->getValue(DBEContact::pendingFurloughAction),
                    'pendingFurloughActionDate'     => $dbeContact->getValue(DBEContact::pendingFurloughActionDate),
                    'pendingFurloughActionLevel'    => $dbeContact->getValue(DBEContact::pendingFurloughActionLevel),
                    'siteNo'       => $dbeSite->getValue(DBESite::siteNo),
                    'active'       => $dbeContact->getValue(DBEContact::active),
                    'siteTitle'    => $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                            DBESite::town
                        ) . ' ' . $dbeSite->getValue(DBESite::postcode),
                    "sitePhone"    => $dbeSite->getValue(DBESite::phone),
                    "isPrimary"    => $primaryContact && $primaryContact->getValue(
                            DBEContact::contactID
                        ) === $dbeContact->getValue(DBEContact::contactID)
                )
            );
        }
        return $contacts;
    }

    function getCustomerContracts($customerID)
    {
        $customerID         = $customerID;
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
                    $this->dsContact,
                    $this->dbeUser
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
    
    //--------------------------------new
    function displayForm(){
        $this->setPageTitle('Customer');
        $this->setTemplateFiles(
            'CustomerEdit',
            'CustomerEditSimple.inc'
        );
        $this->loadReactScript('CustomerEditComponent.js');
        $this->loadReactCSS('CustomerEditComponent.css');
        $this->template->parse(
            'CONTENTS',
            'CustomerEdit',
            true
        );
        $this->parsePage();
    }
    function getEncrypt()
    {
        $value     = @$_REQUEST['value'];
        $encrypted = Encryption::encrypt(
            CUSTOMERS_ENCRYPTION_PUBLIC_KEY,
            $value
        );
        echo json_encode(["status" => "ok", "data" => $encrypted]);
    }

    function getCustomerData()
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
                            "dateMeetingConfirmed"         => $dbeCustomer->getValue(DBECustomer::dateMeetingConfirmed),
                            "invoiceSiteNo"                => $dbeCustomer->getValue(DBECustomer::invoiceSiteNo),
                            "deliverSiteNo"                => $dbeCustomer->getValue(DBECustomer::deliverSiteNo),
                            "lastUpdatedDateTime"          => $dbeCustomer->getValue(DBECustomer::lastUpdatedDateTime),
                            "opportunityDeal"              => $dbeCustomer->getValue(DBECustomer::opportunityDeal),
                            "reviewAction"                 => $dbeCustomer->getValue(DBECustomer::reviewAction),
                            "lastContractSent"             => $dbeCustomer->getValue(DBECustomer::lastContractSent),
                            "statementContactId"           => $dbeCustomer->getValue(DBECustomer::statementContactId),
                            "inclusiveOOHCallOuts"         => $dbeCustomer->getValue(DBECustomer::inclusiveOOHCallOuts),
                            "eligiblePatchManagement"      => $dbeCustomer->getValue(DBECustomer::eligiblePatchManagement ),
                            "excludeFromWebrootChecks"     => $dbeCustomer->getValue(DBECustomer::excludeFromWebrootChecks),  
                            "inviteSent"                   => $dbeCustomer->getValue(DBECustomer::inviteSent),  
                            "reportProcessed"              => $dbeCustomer->getValue(DBECustomer::reportProcessed),  
                            "reportSent"                   => $dbeCustomer->getValue(DBECustomer::reportSent),  
                            "rating"                       => $dbeCustomer->getValue(DBECustomer::rating),  
                            "meeting_datetime"             => $dbeCustomer->getValue(DBECustomer::meetingDateTime),  
                        ]
                    ]
                );
    }

    function _getMainContacts($customerID)
    {
        if (!$customerID) {
            http_response_code(400);
            echo json_encode(["status" => "error", "description" => "Customer ID Not provided"]);
            exit;
        }
        echo json_encode(["status" => "ok", "data" => $this->getMainContacts($customerID)]);
    }

    function getLeadStatuses()
    {
        $dbeLeadStatus = new DBECustomerLeadStatus($this);
        $dbeLeadStatus->getRows(DBECustomerLeadStatus::sortOrder);
        echo json_encode(["status" => "ok", "data" => $dbeLeadStatus->fetchArray()]);
    }

    function updateCustomer()
    {
        $json        = file_get_contents('php://input');
                $data        = json_decode($json, true);
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($data['customerID']);
                if (empty($data['lastUpdatedDateTime']) || $data['lastUpdatedDateTime'] < $dbeCustomer->getValue(
                        DBECustomer::lastUpdatedDateTime
                    )) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(
                        400, "Updated by another user", [
                               "errorCode"           => 1002,
                               "lastUpdatedDateTime" => $dbeCustomer->getValue(DBECustomer::lastUpdatedDateTime)
                           ]
                    );
                }
                foreach ($data as $key => $value) {
                    $dbeCustomer->setValue($key, $value);
                }
                $dbeCustomer->updateRow();
                return $this->success(["lastUpdatedDateTime" => $dbeCustomer->getValue(DBECustomer::lastUpdatedDateTime)]);                
    }

    function updateSite($data)
    {
                $dbeSite = new DBESite($this);
                if (!isset($data['customerID'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, "Customer ID is mandatory");
                }
                if (!isset($data['siteNo'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, "siteNo is mandatory");
                }
                $dbeSite->setValue(DBESite::customerID, $data['customerID']);
                $dbeSite->setValue(DBESite::siteNo, $data['siteNo']);
                $dbeSite->getRow($data['siteNo']);
                //$dbeSite->getRowByCustomerIDSiteNo();
                /*if (empty($data['lastUpdatedDateTime']) || $data['lastUpdatedDateTime'] < $dbeSite->getValue(
                        DBESite::lastUpdatedDateTime
                    )) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(
                        400, "Updated by another user", [
                               "errorCode"           => 1002,
                               "lastUpdatedDateTime" => $dbeSite->getValue(DBESite::lastUpdatedDateTime)
                           ]
                    );
                }*/
                if(isset($data['fieldValueMap'])) {
                    if (isset($data['fieldValueMap']['active']) && $data['siteNo'] == 0) {
                        throw new \CNCLTD\Exceptions\JsonHttpException(400, "Cannot deactivate Site 0");
                    }
                    $dbeSite = \CNCLTD\Data\SiteMapper::fromDTOToDB($data['fieldValueMap'], $dbeSite);
                }
                $dbeSite->setValue(DBESite::add1, $data['add1']);
                $dbeSite->setValue(DBESite::add2, $data['add2']);
                $dbeSite->setValue(DBESite::add3, $data['add3']);
                $dbeSite->setValue(DBESite::town, $data['town']);
                $dbeSite->setValue(DBESite::postcode, $data['postcode']);
                $dbeSite->setValue(DBESite::phone, $data['phone']);
                $dbeSite->setValue(DBESite::county, $data['county']);
                $dbeSite->setValue(DBESite::what3Words, $data['what3Words']);
                $dbeSite->setValue(DBESite::maxTravelHours, $data['maxTravelHours']);
                $dbeSite->setValue(DBESite::activeFlag, $data['activeFlag']);
                $dbeSite->updateRow();
                echo json_encode(
                    [
                        "status"              => "ok",
                        "lastUpdatedDateTime" => $dbeSite->getValue(DBECustomer::lastUpdatedDateTime)
                    ]
                );
    }

    function deletePortalDocument($data)
    {
                if (!isset($data['portalDocumentId'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, "portal document Id is required");
                }
                $dbePortalCustomerDocumentWithoutFile = new DBEPortalCustomerDocumentWithoutFile($this);
                $dbePortalCustomerDocumentWithoutFile->deleteRow($data['portalDocumentId']);
                echo json_encode(["status" => "ok"]);
    }

    function addPortalCustomerDocument($data)
    {
        if (!isset($data['customerId'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "customerId is required");
        }
        if (!isset($data['description'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "description is required");
        }
        if (!isset($data['fileName'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "fileName is required");
        }
        if (!isset($data['encodedFile'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "encodedFile is required");
        }
        if (!isset($data['customerContract'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "customerContract is required");
        }
        if (!isset($data['mainContractOnly'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "mainContractOnly is required");
        }
        $fileAndMimeType           = $this->getFileDecodedAndMimeTypeFromBase64EncodedFile(
            $data['encodedFile']
        );
        $dbePortalCustomerDocument = new DBEPortalCustomerDocument($this);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::customerID, $data['customerId']);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::description, $data['description']);
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::file, $fileAndMimeType->file);
        $dbePortalCustomerDocument->setValue(
            DBEPortalCustomerDocument::fileMimeType,
            $fileAndMimeType->mimeType
        );
        $dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::filename, $data['fileName']);
        $dbePortalCustomerDocument->setValue(
            DBEPortalCustomerDocument::customerContract,
            $data['customerContract']
        );
        $dbePortalCustomerDocument->setValue(
            DBEPortalCustomerDocument::mainContactOnlyFlag,
            $data['mainContractOnly'] ? 'Y' : 'N'
        );
        $dbePortalCustomerDocument->insertRow();
        $document = [
            'id'                  => $dbePortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::portalCustomerDocumentID
            ),
            'description'         => $dbePortalCustomerDocument->getValue(
                DBEPortalCustomerDocumentWithoutFile::description
            ),
            'filename'            => $dbePortalCustomerDocument->getValue(
                DBEPortalCustomerDocumentWithoutFile::filename
            ),
            'customerContract'    => $dbePortalCustomerDocument->getValue(
                DBEPortalCustomerDocumentWithoutFile::customerContract
            ),
            'mainContactOnlyFlag' => $dbePortalCustomerDocument->getValue(
                    DBEPortalCustomerDocument::mainContactOnlyFlag
                ) === 'Y',
        ];
        echo json_encode(["status" => "ok", "data" => $document]);
    }

    function getSectors()
    {
        $dbeSector = new DBESector($this);
                $dbeSector->getRows(DBESector::description);
                echo json_encode(["status" => "ok", "data" => $dbeSector->fetchArray()]);
    }

    function getCustomerTypes()
    {
        $dbeCustomerTypes = new DBECustomerType($this);
        $dbeCustomerTypes->getRows(DBECustomerType::description);
        echo json_encode(["status" => "ok", "data" => $dbeCustomerTypes->fetchArray()]);
    }

    function getAccountManagers()
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        echo json_encode(["status" => "ok", "data" => $dbeUser->fetchArray()]);
    }

    function _saveContactPassword()
    {
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
    }

    function archiveContact()
    {
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
    }

    function _getCustomerReviewContacts()
    {
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
    }

    function decrypt()
    {
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
    }

    function removeSupportAndRefer($customerID)
    {
        $response   = ['status' => 'ok'];
                try {
                    $this->removeSupportForAllUsersAndReferCustomer($customerID);
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error']  = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
    }

    function searchName()
    {
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
    }

    function addSite($data)
    {
                if (!isset($data['customerID'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'customerID is required');
                }
                if (!isset($data['add1'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'addressLine is required');
                }
                if (!isset($data['town'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'town is required');
                }
                if (!isset($data['postcode'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'postcode is required');
                }
                if (!isset($data['phone'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'phone is required');
                }
                if (!isset($data['maxTravelHours'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'maxTravelHours is required');
                }
                $dbeSite = new DBESite($this);
                $dbeSite->setValue(DBESite::customerID, $data['customerID']);
                $dbeSite->setValue(DBESite::add1, $data['add1']);
                $dbeSite->setValue(DBESite::add2, $data['add2']);
                $dbeSite->setValue(DBESite::add3, $data['add3']);
                $dbeSite->setValue(DBESite::town, $data['town']);
                $dbeSite->setValue(DBESite::postcode, $data['postcode']);
                $dbeSite->setValue(DBESite::phone, $data['phone']);
                $dbeSite->setValue(DBESite::county, $data['county']);
                $dbeSite->setValue(DBESite::what3Words, $data['what3Words']);
                $dbeSite->setValue(DBESite::maxTravelHours, $data['maxTravelHours']);
                $dbeSite->setValue(DBESite::activeFlag, 'Y');
                $dbeSite->insertRow();
                $site = [
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
                    "canDelete"      => true
                ];
                echo json_encode(["status" => "ok", "data" => $site]);
                
    }


    function addContact($data)
    {
        $dbeContact = new DBEContact($this);
        if (!isset($data['customerID'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Customer ID is mandatory");
        }
        $dbeContact->setValue(DBEContact::siteNo, $data['siteNo']);   
        $dbeContact->setValue(DBEContact::customerID, $data['customerID']);   
       // $dbeContact->setValue(DBEContact::supplierID, $data['supplierID']);   
        $dbeContact->setValue(DBEContact::title, $data['title']);   
        $dbeContact->setValue(DBEContact::position, $data['position']);   
        $dbeContact->setValue(DBEContact::firstName, $data['firstName']);   
        $dbeContact->setValue(DBEContact::lastName, $data['lastName']);   
        $dbeContact->setValue(DBEContact::email, $data['email']);   
        $dbeContact->setValue(DBEContact::phone, $data['phone']);   
        $dbeContact->setValue(DBEContact::mobilePhone, $data['mobilePhone']);   
        $dbeContact->setValue(DBEContact::fax, $data['fax']);   
        //$dbeContact->setValue(DBEContact::portalPassword, $data['portalPassword']);   
        $dbeContact->setValue(DBEContact::mailshot, $data['mailshot']);   
        $dbeContact->setValue(DBEContact::mailshot2Flag, $data['mailshot2Flag']);   
        $dbeContact->setValue(DBEContact::mailshot3Flag, $data['mailshot3Flag']);   
        $dbeContact->setValue(DBEContact::mailshot8Flag, $data['mailshot8Flag']);   
        $dbeContact->setValue(DBEContact::mailshot9Flag, $data['mailshot9Flag']);   
        $dbeContact->setValue(DBEContact::mailshot11Flag, $data['mailshot11Flag']);   
        $dbeContact->setValue(DBEContact::notes, $data['notes']);   
        $dbeContact->setValue(DBEContact::failedLoginCount, $data['failedLoginCount']);   
        $dbeContact->setValue(DBEContact::reviewUser, $data['reviewUser']);   
        $dbeContact->setValue(DBEContact::hrUser, $data['hrUser']);   
        $dbeContact->setValue(DBEContact::supportLevel, $data['supportLevel']);   
        $dbeContact->setValue(DBEContact::initialLoggingEmail, $data['initialLoggingEmail']);   
        $dbeContact->setValue(DBEContact::othersInitialLoggingEmailFlag, $data['othersInitialLoggingEmailFlag']);   
        $dbeContact->setValue(DBEContact::othersWorkUpdatesEmailFlag, $data['othersWorkUpdatesEmailFlag']);   
        $dbeContact->setValue(DBEContact::othersFixedEmailFlag, $data['othersFixedEmailFlag']);   
        $dbeContact->setValue(DBEContact::pendingLeaverFlag, $data['pendingLeaverFlag']);   
        $dbeContact->setValue(DBEContact::pendingLeaverDate, $data['pendingLeaverDate']);   
        $dbeContact->setValue(DBEContact::specialAttentionContactFlag, $data['specialAttentionContactFlag']);   
        $dbeContact->setValue(DBEContact::linkedInURL, $data['linkedInURL']);   
        $dbeContact->setValue(DBEContact::active, $data['active']);   
        $dbeContact->insertRow();
        echo json_encode(
            [
                "status" => "ok",
            ]
        );
    }

    function updateContact($data)
    {
        $dbeContact = new DBEContact($this);
        if (!isset($data['customerID'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Customer ID is mandatory");
        }
        if (!isset($data['id'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Contact ID is mandatory");
        }
        $dbeContact->getRow($data['id']);
        $dbeContact->setValue(DBEContact::siteNo, $data['siteNo']);   
        $dbeContact->setValue(DBEContact::customerID, $data['customerID']);   
       // $dbeContact->setValue(DBEContact::supplierID, $data['supplierID']);   
        $dbeContact->setValue(DBEContact::title, $data['title']);   
        $dbeContact->setValue(DBEContact::position, $data['position']);   
        $dbeContact->setValue(DBEContact::firstName, $data['firstName']);   
        $dbeContact->setValue(DBEContact::lastName, $data['lastName']);   
        $dbeContact->setValue(DBEContact::email, $data['email']);   
        $dbeContact->setValue(DBEContact::phone, $data['phone']);   
        $dbeContact->setValue(DBEContact::mobilePhone, $data['mobilePhone']);   
        $dbeContact->setValue(DBEContact::fax, $data['fax']);   
        //$dbeContact->setValue(DBEContact::portalPassword, $data['portalPassword']);   
        $dbeContact->setValue(DBEContact::mailshot, $data['mailshot']);   
        $dbeContact->setValue(DBEContact::mailshot2Flag, $data['mailshot2Flag']);   
        $dbeContact->setValue(DBEContact::mailshot3Flag, $data['mailshot3Flag']);   
        $dbeContact->setValue(DBEContact::mailshot8Flag, $data['mailshot8Flag']);   
        $dbeContact->setValue(DBEContact::mailshot9Flag, $data['mailshot9Flag']);   
        $dbeContact->setValue(DBEContact::mailshot11Flag, $data['mailshot11Flag']);   
        $dbeContact->setValue(DBEContact::notes, $data['notes']);   
        $dbeContact->setValue(DBEContact::failedLoginCount, $data['failedLoginCount']);   
        $dbeContact->setValue(DBEContact::reviewUser, $data['reviewUser']);   
        $dbeContact->setValue(DBEContact::hrUser, $data['hrUser']);   
        $dbeContact->setValue(DBEContact::supportLevel, $data['supportLevel']);   
        $dbeContact->setValue(DBEContact::initialLoggingEmail, $data['initialLoggingEmail']);   
        $dbeContact->setValue(DBEContact::othersInitialLoggingEmailFlag, $data['othersInitialLoggingEmailFlag']);   
        $dbeContact->setValue(DBEContact::othersWorkUpdatesEmailFlag, $data['othersWorkUpdatesEmailFlag']);   
        $dbeContact->setValue(DBEContact::othersFixedEmailFlag, $data['othersFixedEmailFlag']);   
        $dbeContact->setValue(DBEContact::pendingLeaverFlag, $data['pendingLeaverFlag']);   
        $dbeContact->setValue(DBEContact::pendingLeaverDate, $data['pendingLeaverDate']);   
        $dbeContact->setValue(DBEContact::specialAttentionContactFlag, $data['specialAttentionContactFlag']);   
        $dbeContact->setValue(DBEContact::linkedInURL, $data['linkedInURL']);   
        $dbeContact->setValue(DBEContact::active, $data['active']);   
        $dbeContact->updateRow();
        echo json_encode(
            [
                "status" => "ok",
            ]
        );
    }
    function getCustomeLetters(){
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
        return $this->success( $customLetterTemplates);
    }
    /*
    update customer CRM Info
    */
    function updateCRM(){
        $body=$this->getBody();
        $dbeCustomer=new DBECustomer($this);
        $dbeCustomer->getRow($body->customerID);
        if(!$dbeCustomer->rowCount())
            return $this->fail(APIException::notFound,"Customer Not Found");
        $dbeCustomer->setValue(DBECustomer::leadStatusId,$body->leadStatusId);
        $dbeCustomer->setValue(DBECustomer::mailshotFlag,$body->mailshotFlag);
        $dbeCustomer->setValue(DBECustomer::dateMeetingConfirmed,$body->dateMeetingConfirmed);
        $dbeCustomer->setValue(DBECustomer::meetingDateTime,$body->meeting_datetime);
        $dbeCustomer->setValue(DBECustomer::inviteSent,$body->inviteSent);
        $dbeCustomer->setValue(DBECustomer::reportProcessed,$body->reportProcessed);
        $dbeCustomer->setValue(DBECustomer::reportSent,$body->reportSent);
        $dbeCustomer->setValue(DBECustomer::rating,$body->rating);
        $dbeCustomer->setValue(DBECustomer::opportunityDeal,$body->opportunityDeal);
        $dbeCustomer->updateRow();
        return $this->success( );
    }
}
