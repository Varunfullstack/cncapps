<?php
/**
 * CNC base controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Exceptions\APIException;
use CNCLTD\FavouriteMenu;
use CNCLTD\MenuItem;
use CNCLTD\SideMenu;

require_once($cfg ['path_gc'] . '/DataSet.inc.php');
require_once($cfg ['path_gc'] . '/Controller.inc.php');
require_once($cfg ['path_dbe'] . '/DBEJUser.inc.php');
require_once($cfg ['path_dbe'] . '/DBETeam.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");
define(
    'CTCNC_ACT_DISP_CUST_POPUP',
    'dispCustPopup'
);
define(
    'CTCNC_ACT_DISP_ITEM_POPUP',
    'dispItemPopup'
);
define(
    'CTCNC_ACT_DISP_TEMPLATE_QUOTATION_POPUP',
    'dispTemplateQuotationPopup'
);
define(
    'CTCNC_ACT_CUSTOMERITEM_POPUP',
    'dispCItemPopup'
);
define(
    'CTCNC_ACT_DISP_SUPPLIER_POPUP',
    'dispSupplierPopup'
);
define(
    'CTCNC_ACT_SUPPLIER_ADD',
    'addSupplier'
);
define(
    'CTCNC_ACT_SUPPLIER_EDIT',
    'editSupplier'
);
define(
    'CTCNC_ACT_CONTACT_POPUP',
    'contactPopup'
);
define(
    'CTCNC_ACT_CONTACT_ADD',
    'addContact'
);
define(
    'CTCNC_ACT_CONTACT_EDIT',
    'editContact'
);
define(
    'CTCNC_ACT_DELETE_QUOTE',
    'deleteQuote'
);
define(
    'CTCNC_ACT_ITEM_ADD',
    'addItem'
);
define(
    'CTCNC_ACT_ITEM_EDIT',
    'editItem'
);
define(
    'CTCNC_ACT_SITE_ADD',
    'addSite'
);
define(
    'CTCNC_ACT_SITE_EDIT',
    'editSite'
);
define(
    'CTCNC_ACT_SITE_POPUP',
    'popupSite'
);
define(
    'CTCNC_ACT_INVOICE_REPRINT',
    'invoiceReprint'
);
define(
    'CTCNC_ACT_GENERATE_POS_FROM_SO',
    'generatePOs'
);
define(
    'CTCNC_ACT_DISPLAY_PO',
    'display'
);
define(
    'CTCNC_ACT_DISP_SALESORDER',
    'displaySalesOrder'
);
define(
    'CTCNC_ACT_SEARCH',
    'search'
);
define(
    'CTCNC_ACT_VIEW',
    'view'
);
//define('CTCNC_ACT_DELETE', 'delete');
define(
    'CTCNC_ACT_DISPLAY_SEARCH_FORM',
    'dispSearchForm'
);
define(
    'CTCNC_ACT_LOGOUT',
    'logout'
);
define(
    'FLAG_AS_FAVOURITE',
    'flagAsFavourite'
);
define(
    'UNFLAG_AS_FAVOURITE',
    'unflagAsFavourite'
);
define(
    'CTCNC_ACT_DISPLAY_DESPATCH',
    'displayDespatch'
);
define(
    'CTCNC_ACT_DISPLAY_DEL_NOTE_DOC',
    'displayNote'
);
define(
    'CTCNC_ACT_INVOICE_REPRINT_GENERATE',
    'invoiceReprintGenerate'
);
define(
    'CTCNC_ACT_INVOICE_PRINT_UNPRINTED',
    'invUnprinted'
);
define(
    'CTCNC_ACT_DISPLAY_INVOICE',
    'displayInvoice'
);
define(
    'CTCNC_ACT_DISPLAY_GOODS_IN',
    'displayGoodsIn'
);
define(
    'CTCNC_PAGE_CUSTOMER',
    'Customer.php'
);
define(
    'CTCNC_PAGE_CUSTOMERITEM',
    'CustomerItem.php'
);
define(
    'CTCNC_PAGE_CONTACT',
    'Contact.php'
);
define(
    'CTCNC_PAGE_ITEM',
    'Item.php'
);
define(
    'CTCNC_PAGE_INVOICE',
    'Invoice.php'
);
define(
    'CTCNC_PAGE_SUPPLIER',
    'Supplier.php'
);
define(
    'CTCNC_PAGE_SITE',
    'Site.php'
);
define(
    'CTCNC_PAGE_SALESORDER',
    'SalesOrder.php'
);
define(
    'CTCNC_PAGE_PURCHASEORDER',
    'PurchaseOrder.php'
);
define(
    'CTCNC_PAGE_SALESORDEREDIT',
    'SalesOrderEdit.php'
);
define(
    'CTCNC_PAGE_GOODSIN',
    'GoodsIn.php'
);
define(
    'CTCNC_PAGE_DESPATCH',
    'Despatch.php'
);
define(
    'CTCNC_PAGE_PURCHASEINV',
    'PurchaseInv.php'
);
define(
    'CTCNC_ACT_DISP_EDIT',
    'dispEdit'
);
define(
    'CTCNC_NONE_SELECTED',
    -9
);
define(
    'CTCNC_HTML_DISABLED',
    'disabled'
);
define(
    'CTCNC_HTML_READONLY',
    'readonly'
);
// messages
define(
    'CTCNC_MSG_INVALID_DATE',
    'Invalid date'
);

class CTCNC extends Controller
{
    var $userID;
    /** @var DBEUser */
    public  $dbeUser;
    var     $dbeTeam;
    private $user;
    /**
     * @var FavouriteMenu
     */
    private $favouriteMenu;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {

        if ($this->getParam('action')) {
            $this->setAction($this->getAction());
        }
        if (!$this->isRunningFromCommandLine() && isset($GLOBALS ['auth'])) {
            $this->userID = $GLOBALS ['auth']->is_authenticated();
        } else {
            $this->userID = CONFIG_SCHEDULED_TASK_USER_ID;
        }
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        $dbeUser->getRow();
        $this->favouriteMenu = new FavouriteMenu($this->userID);
        $this->user          = new BUUser($this);
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
    }


    /**
     * Is the request from the command line (or scheduled task)
     *
     * @return mixed
     */
    function isRunningFromCommandLine()
    {
        return ($GLOBALS['isRunningFromCommandLine']);
    }

    function getDbeUser()
    {
        if (!$this->dbeUser) {
            $this->dbeUser = new DBEUser ($this);
            if ($this->userID) {
                $this->dbeUser->getRow($this->userID);
            }

        }
        return $this->dbeUser;
    }

    function canAccess($roles)
    {
        $perms = explode(
            ',',
            $this->dbeUser->getValue(DBEUser::perms)
        );
        $array = array_intersect(
            $perms,
            $roles
        );
        return !!count($array);

    }

    function getUser()
    {

    }

    /**
     * Check a date in yyyy/mm/dd format
     * @access private
     * @param $dateString
     * @return bool
     */
    function isValidDate($dateString)
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        return !!$date;
    }

    /**
     * @throws Exception
     */
    function parsePage()
    {
        global $userName;
        $menu      = new SideMenu($this->favouriteMenu);
        $urlLogout = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_LOGOUT)
        );
        // if new session then username not set yet
        if (!$userName) {
            $dbeUser = new DBEUser ($this);
            $dbeUser->setValue(
                DBEUser::userID,
                $this->userID
            );
            $dbeUser->getRow();
            $userName = $dbeUser->getValue(DBEUser::name);
        }
        $this->template->set_var(
            array(
                'userName'   => $userName,
                'fromDate'   => null,
                'urlLogout2' => $urlLogout,
                'urlLogout'  => $urlLogout
            )
        );
        $technicalSection = [
            "key"  => "Technical",
            "icon" => 'fal fa-laptop',
        ];
        if ($this->hasPermissions(TECHNICAL_PERMISSION)) {
            $menu->addSection($technicalSection['key'], $technicalSection['icon'], $this->getDefaultTechnicalMenu());
        }
        $this->addConditionalMenu(
            $menu,
            $technicalSection['icon'],
            $technicalSection['key'],
            $this->isStarterLeaverManger(),
            114,
            "Starter Leaver Management",
            "StarterLeaverManagement.php"
        );
        $SDManagerSection = [
            "key"   => "SDManagement",
            "icon"  => 'fal fa-chalkboard-teacher',
            "label" => "SD Management",
        ];
        if ($this->isUserSDManager()) {
            $menu->addSection(
                $SDManagerSection["key"],
                $SDManagerSection["icon"],
                $this->getDefaultSDManagerMenu(),
                $SDManagerSection["label"],
            );
        }
        $this->addConditionalMenu(
            $menu,
            $SDManagerSection['icon'],
            $SDManagerSection['key'],
            $this->isSdManager() || $this->isSRQueueManager(),
            201,
            "SD Management",
            "SDManagerDashboard.php",
            $SDManagerSection['label']
        );
        $this->addConditionalMenu(
            $menu,
            $SDManagerSection["icon"],
            $SDManagerSection["key"],
            $this->isAppraiser(),
            224,
            "Staff Appraisals",
            "StaffAppraisalQuestionnaire.php",
            $SDManagerSection["label"]
        );
        $salesSection = [
            "key"  => "Sales",
            "icon" => 'fal fa-tag',
        ];
        if ($this->hasPermissions(SALES_PERMISSION)) {
            $menu->addSection($salesSection['key'], $salesSection['icon'], $this->getDefaultSalesMenu());
            $this->addConditionalMenu(
                $menu,
                $salesSection['icon'],
                $salesSection['key'],
                $this->dbeUser->getValue(DBEUser::streamOneLicenseManagement) == 1,
                313,
                "StreamOne Licenses",
                "CustomerLicenses.php?action=searchCustomers"
            );
        }
        $this->addConditionalMenu(
            $menu,
            $salesSection['icon'],
            $salesSection['key'],
            $this->dbeUser->getValue(DBEUser::createRenewalSalesOrdersFlag) == 'Y',
            312,
            "Create Renewals Sales Orders",
            "CreateRenewalSalesOrdersManager.php"
        );
        if ($this->hasPermissions(ACCOUNT_MANAGEMENT_PERMISSION)) {
            $menu->addSection(
                'AccountManagement',
                'fal fa-user-cog',
                $this->getDefaultAccountManagementMenu(),
                "Account Management"
            );
        }
        if ($this->hasPermissions(REPORTS_PERMISSION)) {
            $menu->addSection('Reports', "fal fa-file", $this->getDefaultReportsMenu());
        }
        if ($this->hasPermissions(RENEWALS_PERMISSION)) {
            $menu->addSection(
                'ServiceRenewals',
                'fal fa-tasks',
                $this->getDefaultServiceRenewalsMenu(),
                "Service Renewals"
            );
        }
        if ($this->hasPermissions(ACCOUNTS_PERMISSION)) {
            $menu->addSection('Accounts', 'fal fa-calculator', $this->getDefaultAccountsMenu());
        }
        if ($this->hasPermissions(MAINTENANCE_PERMISSION)) {
            $menu->addSection("Maintenance", 'fal fa-wrench', $this->getDefaultMaintenanceMenu());
        }
        if ($this->hasPermissions(SENIOR_MANAGEMENT_PERMISSION)) {
            $menu->addSection("Management", 'fal fa-project-diagram', $this->getDefaultManagementMenu());
        }
        $menu->addSection(
            $this->getDbeUser()->getValue(DBEUser::name),
            'fal fa-user',
            [
                [
                    "id"    => 1001,
                    "label" => "Expenses/Overtime",
                    "href"  => "ExpenseDashboard.php",
                ],
                [
                    "id"    => 1002,
                    "label" => "My Account",
                    "href"  => "MySettings.php",
                ],
            ]
        );
        global $twig;
        $menu->sort();
        $sideMenu = $twig->render('@internal/sideMenu/sideMenuItems.html.twig', ["sideMenu" => $menu]);
        $this->template->setVar("sideMenu", $sideMenu);
        $favouriteItemsHTML = $twig->render(
            '@internal/sideMenu/favouritesMenuItems.html.twig',
            ["favouriteItems" => $menu->getFavouriteItems()]
        );
        $this->template->setVar('favouritesMenuItems', $favouriteItemsHTML);
        parent::parsePage();
    }

    function hasPermissions($levels)
    {
        if ($this->isRunningFromCommandLine()) {
            return true;
        }
        $permissions = explode(
            ",",
            self::getDbeUser()->getValue(DBEUser::perms)
        );
        if (is_array($levels)) {
            return array_intersect(
                $levels,
                $permissions
            );
        }
        if ($this->userID) {
            return in_array(
                $levels,
                $permissions
            );
        }
        return true;
    }

    private function getDefaultTechnicalMenu()
    {
        return [
            [
                "id"    => 101,
                //"href"  => "Activity.php?action=activityCreate1",
                "href"  => "LogServiceRequest.php",
                "label" => "Log Service Request",
            ],
            [
                "id"    => 102,
                "href"  => "Activity.php",
                "label" => "Search Service Requests",
            ],
            [
                "id"    => 103,
                "href"  => "CurrentActivityReport.php",
                "label" => "Current Service Requests",
            ],
            [
                "id"    => 104,
                "href"  => "Password.php",
                "label" => "Passwords",
            ],
            [
                "id"         => 105,
                "href"       => "#",
                "label"      => "Generate Password",
                "attributes" => [
                    "onclick" => "window.open('Password.php?action=generate&htmlFmt=popup','reason','scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0' )",
                ],
            ],
            [
                "id"    => 106,
                "href"  => "CustomerItem.php",
                "label" => "Customer Items",
            ],
            [
                "id"    => 107,
                "href"  => "Projects.php",
                "label" => "Projects",
            ],
            [
                "id"    => 108,
                "href"  => "OffsiteBackupStatus.php",
                "label" => "OBRS Backup Status",
            ],
            [
                "id"    => 109,
                "href"  => "OffsiteBackupReplicationStatus.php",
                "label" => "OBRS Replication Status",
            ],
            [
                "id"    => 110,
                "href"  => "AgedService.php",
                //"href"  => "DailyReport.php?action=outstandingIncidents&onScreen=true&dashboard=true&daysAgo=7",
                "label" => "Aged Service Requests",
            ],
            // [
            //     "id"    => 111,
            //     "href"  => "24HoursSupportCustomersReport.php",
            //     "label" => "24 Hour Support Customers",
            // ],
            // [
            //     "id"    => 112,
            //     "href"  => "SpecialAttentionCustomersReport.php",
            //     "label" => "Special Attention Customers",
            // ],
            [
                "id"    => 113,
                "href"  => "CustomerInfo.php",
                "label" => "Customer Information",
            ],
        ];
    }

    private function addConditionalMenu(SideMenu $menu,
                                        $icon,
                                        $menuKey,
                                        $condition,
                                        $id,
                                        $label,
                                        $href,
                                        $menuName = null
    )
    {
        if (!$condition) {
            return;
        }
        $section = $menu->getSection($menuKey);
        if (!$section) {
            $menu->addSection($menuKey, $icon, [], $menuName);
        }
        $menu->addItemToSection($menuKey, new MenuItem($id, $label, $href));
    }

    protected function isStarterLeaverManger()
    {
        return $this->dbeUser->getValue(DBEUser::starterLeaverQuestionManagementFlag) == 'Y';
    }

    function isUserSDManager()
    {
        if ($this->isRunningFromCommandLine()) {
            return true;
        }
        return self::getDbeUser()->getValue(DBEUser::receiveSdManagerEmailFlag) == 'Y';
    }

    private function getDefaultSDManagerMenu()
    {

        return [
            // [
            //     "id"    => 202,
            //     "label" => "Time Requests",
            //     "href"  => "TimeRequestDashboard.php"
            // ],
            // [
            //     "id"    => 203,
            //     "label" => "Change Requests",
            //     "href"  => "ChangeRequestDashboard.php?HD=null&ES=null&SP=null&P=null"
            // ],
            // [
            //     "id"    => 204,
            //     "label" => "Sales Requests",
            //     "href"  => "SalesRequestDashboard.php"
            // ],
            [
                "id"    => 202,
                "label" => "Request Dashboard",
                "href"  => "RequestDashBoard.php"
            ],
            [
                "id"    => 205,
                "label" => "Schedule SR",
                "href"  => "SRScheduler.php"
            ],
            [
                "id"    => 206,
                "label" => "First Time Fixes",
                "href"  => "FirstTimeFixReport.php"
            ],
            [
                "id"    => 207,
                "label" => "Team & User Statistics",
                "href"  => "TeamAndUserStatistics.php"
            ],
            [
                "id"    => 208,
                "label" => "SLA Performance",
                "href"  => "SLAPerformance.php"
            ],
            [
                "id"    => 225,
                "label" => "SR Source",
                "href"  => "SRSource.php"
            ],
            [
                "id"    => 209,
                "label" => "3CX Call Reporting",
                "href"  => "CallReporting.php"
            ],
            [
                "id"    => 210,
                "label" => "OBRS Failure Analysis",
                "href"  => "OffsiteBackupStatus.php?action=failureAnalysis"
            ],
            [
                "id"    => 211,
                "label" => "SR Report",
                "href"  => "ServiceRequestReport.php"
            ],
            [
                "id"    => 212,
                "label" => "Customer SR Analysis",
                "href"  => "CustomerSrAnalysisReport.php"
            ],
            [
                "id"    => 213,
                "label" => "SRs by Customer",
                "href"  => "ServiceRequestsByCustomerReport.php"
            ],
            [
                "id"    => 214,
                "label" => "Starters & Leavers Report",
                "href"  => "StartersAndLeaversReport.php"
            ],
            [
                "id"    => 215,
                "label" => "Questionnaires",
                "href"  => "Questionnaire.php"
            ],
            [
                "id"    => 217,
                "label" => "Utility Email Addresses",
                "href"  => "UtilityEmails.php"
            ],
            [
                "id"    => 218,
                "label" => "Ignored AD Domains",
                "href"  => "IgnoredADDomains.php"
            ],
            [
                "id"    => 219,
                "label" => "Keyword Matching Ignores",
                "href"  => "KeywordMatchingIgnores.php"
            ],
            [
                "id"    => 220,
                "label" => "OS Support Dates",
                "href"  => "OSSupportDates.php"
            ],
            [
                "id"    => 221,
                "label" => "Office 365 Licenses",
                "href"  => "Office365Licenses.php"
            ],
            [
                "id"    => 222,
                "label" => "Password Services",
                "href"  => "PasswordServices.php"
            ],
            [
                "id"    => 226,
                "label" => "Customer Feedback",
                "href"  => "CustomerFeedback.php"
            ],
        ];

    }

    protected function isAppraiser()
    {
        return $this->dbeUser->getValue(DBEUser::staffAppraiserFlag) == 'Y';
    }

    private function getDefaultSalesMenu()
    {
        return [
            [
                "id"    => 701,
                "label" => "Invoices",
                "href"  => "Invoice.php",
            ],
            [
                "id"    => 301,
                "label" => "Sales Orders",
                "href"  => "SalesOrder.php",
            ],
            [
                "id"    => 302,
                "label" => "Purchase Orders",
                "href"  => "PurchaseOrder.php",
            ],
            [
                "id"    => 303,
                "label" => "Customer Search",
                "href"  => "Customer.php",
            ],
            [
                "id"    => 304,
                "label" => "Items",
                "href"  => "Item.php"
            ],
            [
                "id"    => 305,
                "label" => "Create Sales Request",
                "href"  => "createSalesRequest.php",
            ],
            [
                "id"    => 306,
                "label" => "Contracts",
                "href"  => "ContractReport.php",
            ],
            [
                "id"    => 307,
                "label" => "Renewal Report",
                "href"  => "RenewalReport.php",
            ],
            [
                "id"    => 308,
                "label" => "Goods In",
                "href"  => "GoodsIn.php",
            ],           
            [
                "id"    => 309,
                "label" => "PO Status Report",
                "href"  => "POStatusReport.php",
            ],
            [
                "id"    => 310,
                "label" => "Renewals Update",
                "href"  => "RenewalsUpdate.php",
            ],
            [
                "id"    => 311,
                "label" => "Quote Templates",
                "href"  => "QuoteTemplates.php",
            ],
            // [
            //     "id"    => 313,
            //     "label" => "TechData Orders",
            //     "href"  => "CustomerLicenses.php?action=searchOrders",
            // ],
        ];
    }

    private function getDefaultAccountManagementMenu()
    {
        return [
            [
                "id"    => 401,
                "label" => "Daily Call List",
                "href"  => "ReviewList.php",
            ],
            [
                "id"    => 402,
                "label" => "Lead Status",
                "href"  => "LeadStatusReport.php",
            ],
            [
                "id"    => 403,
                "label" => "Customer CRM",
                "href"  => "CustomerCRM.php",
            ],
            [
                "id"    => 404,
                "label" => "Customer Review Meetings",
                "href"  => "CustomerReviewMeetingsReport.php",
            ],
            [
                "id"    => 405,
                "label" => "Customer Review Agenda",
                "href"  => "CustomerReviewMeeting.php",
            ],
            [
                "id"    => 406,
                "label" => "Review Meeting Docs",
                "href"  => "CustomerReviewMeetingDocuments.php",
            ],
            [
                "id"    => 407,
                "label" => "Book Sales Visit",
                "href"  => "BookSalesVisit.php",
            ],
        ];
    }

    private function getDefaultReportsMenu()
    {
        return [
            // [
            //     "id"    => 501,
            //     "label" => "Contact Audit Log",
            //     "href"  => "ContactAudit.php",
            // ],
            [
                "id"    => 502,
                "label" => "Office 365 Backup Audit",
                "href"  => "Office365BackupAudit.php",
            ],
            [
                "id"    => 503,
                "label" => "Office 365 Storage Reports",
                "href"  => "Office365StorageReports.php",
            ],
            [
                "id"    => 504,
                "label" => "Service Contracts Ratio",
                "href"  => "ContractAndNumbersReport.php",
            ],
            [
                "id"    => 505,
                "label" => "Sales/Customer",
                "href"  => "ManagementReports.php?action=SalesByCustomer",
            ],
            [
                "id"    => 506,
                "label" => "Spend/Supplier",
                "href"  => "ManagementReports.php?action=SpendBySupplier",
            ],
            [
                "id"    => 507,
                "label" => "Spend/Manufacturer",
                "href"  => "ManagementReports.php?action=SpendByManufacturer",
            ],
            [
                "id"    => 509,
                "label" => "Customer Profitability",
                "href"  => "CustomerProfitabilityReport.php",
            ],
            [
                "id"    => 510,
                "label" => "Customer Analysis",
                "href"  => "CustomerAnalysisReport.php",
            ],
            [
                "id"    => 511,
                "label" => "Contract Analysis",
                "href"  => "ContractAnalysisReport.php",
            ],
            [
                "id"    => 512,
                "label" => "Customer Profitability Export",
                "href"  => "CustomerProfitabilityMonthsReport.php",
            ],
            [
                "id"    => 513,
                "label" => "KPI Reports",
                "href"  => "KPIReport.php",
            ],
        ];
    }

    private function getDefaultServiceRenewalsMenu()
    {
        return [
            [
                "id"    => 601,
                "label" => "Renewals",
                "href"  => "RenewalsDashboard.php",
            ],
            // [
            //     "id"    => 601,
            //     "label" => "Renewal",
            //     "href"  => "RenQuotation.php?action=list&orderBy=customerName&orderDirection=asc",
            // ],
            // [
            //     "id"    => 602,
            //     "label" => "Contract",
            //     "href"  => "RenContract.php",
            // ],
            // [
            //     "id"    => 603,
            //     "label" => "Internet",
            //     "href"  => "RenBroadband.php",
            // ],
            // [
            //     "id"    => 604,
            //     "label" => "Domain",
            //     "href"  => "RenDomain.php",
            // ],
            // [
            //     "id"    => 605,
            //     "label" => "Hosting",
            //     "href"  => "RenHosting.php",
            // ],
            [
                "id"    => 606,
                "label" => "Contract Matrix",
                "href"  => "ContractMatrix.php",
            ],
        ];
    }

    private function getDefaultAccountsMenu()
    {
        return [
            [
                "id"    => 702,
                "label" => "Purchase Invoice Auth",
                "href"  => "PurchaseInv.php",
            ],
            [
                "id"    => 703,
                "label" => "Unprinted Invoices",
                "href"  => "Invoice.php?action=invUnprinted",
            ],
            [
                "id"    => 704,
                "label" => "Reprint Invoices",
                "href"  => "Invoice.php?action=invoiceReprint",
            ],
            [
                "id"    => 705,
                "label" => "Sage Export",
                "href"  => "SageExport.php",
            ],
            [
                "id"    => 706,
                "label" => "PrePay Export",
                "href"  => "PrePay.php",
            ],
            [
                "id"    => 707,
                "label" => "Prepay Adjustment",
                "href"  => "PrepayAdjustment.php",
            ],
            [
                "id"    => 708,
                "label" => "Expenses & OT Export",
                "href"  => "Expense.php?action=exportForm",
            ],
            [
                "id"    => 709,
                "label" => "Excel Sales Report",
                "href"  => "ExcelExport.php",
            ],
            [
                "id"    => 710,
                "label" => "Payment Terms",
                "href"  => "PaymentTerms.php",
            ],
        ];
    }

    private function getDefaultMaintenanceMenu()
    {
        return [
            [
                "id"    => 801,
                "label" => "Activity Types",
                "href"  => "ActivityType.php",
            ],
            [
                "id"    => 802,
                "label" => "Root Causes",
                "href"  => "RootCause.php",
            ],
            [
                "id"    => 803,
                "label" => "Manufacturers",
                "href"  => "Manufacturer.php",
            ],
            [
                "id"    => 804,
                "label" => "Expense Types",
                "href"  => "ExpenseType.php",
            ],
            [
                "id"    => 805,
                "label" => "Item Types",
                "href"  => "ItemType.php",
            ],
            
            [
                "id"    => 806,
                "label" => "Standard Text",
                "href"  => "StandardText.php",
            ],
            [
                "id"    => 807,
                "label" => "Item Billing Category",
                "href"  => "ItemBillingCategory.php",
            ],
            [
                "id"    => 808,
                "label" => "Business Sectors",
                "href"  => "Sector.php",
            ],
            [
                "id"    => 809,
                "label" => "Referral Types",
                "href"  => "CustomerType.php",
            ],
            [
                "id"    => 810,
                "label" => "Suppliers",
                "href"  => "Supplier.php",
            ],
            [
                "id"    => 811,
                "label" => "Lead Status Types",
                "href"  => "LeadStatusTypes.php",
            ],
            [
                "id"    => 813,
                "label" => "Project Options",
                "href"  => "ProjectOptions.php",
            ],
        ];
    }

    private function getDefaultManagementMenu()
    {
        return [
            [
                "id"    => 901,
                "label" => "System Header",
                "href"  => "Header.php",
            ],
            [
                "id"    => 902,
                "label" => "Teams",
                "href"  => "Team.php",
            ],
            [
                "id"    => 903,
                "label" => "Users",
                "href"  => "User.php",
            ],
            [
                "id"    => 904,
                "label" => "Contact Extraction",
                "href"  => "ContactExport.php",
            ],
            [
                "id"    => 905,
                "label" => "Staff Productivity Report",
                "href"  => "StaffProductivityReport.php",
            ],
        ];
    }


    function initialProcesses()
    {
        if ($this->getParam('htmlFmt')) {
            $this->setHTMLFmt($_REQUEST ['htmlFmt']);
        }
        self::getDbeUser();
        switch ($this->getParam('action')) {
            case CTCNC_ACT_LOGOUT :
                $this->logout();
                break;
            case FLAG_AS_FAVOURITE:
                $this->favouriteMenu->addFavourite($this->getParam('menuItemId'));
                echo json_encode(["status" => "ok"]);
                exit;
            case UNFLAG_AS_FAVOURITE:
                $this->favouriteMenu->removeFavourite($this->getParam('menuItemId'));
                echo json_encode(["status" => "ok"]);
                exit;
        }
    }

    function logout()
    {
        $GLOBALS ['sess']->delete();
        $GLOBALS ['auth']->logout();
        header("Location: index.php");
        exit;
    }

    function checkPermissions($levels)
    {
        if (!$this->hasPermissions($levels)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    function canChangeSrPriority()
    {
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        $dbeUser->getRow();
        if ($dbeUser->getValue(DBEUser::changePriorityFlag) == 'Y') {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

    function getChecked($flag)
    {
        return ($flag == 'N' ? null : CT_CHECKED);
    }

    function getLabtechDB()
    {
        $dsn       = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options   = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ];
        $labtechDB = new PDO(
            $dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options
        );
        return $labtechDB;
    }

    function getFullPath()
    {
        // redirect to new page
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $url = "https://"; else
            $url = "http://";
        // Append the host(domain name, ip) to the URL.
        $url .= $_SERVER['HTTP_HOST'];
        // Append the requested resource location to the URL
        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }

    protected function isExpenseApprover()
    {
        return $this->dbeUser->getValue(DBEUser::isExpenseApprover) || $this->dbeUser->getValue(
                DBEUser::globalExpenseApprover
            );
    }

    protected function isSRQueueManager()
    {
        return $this->dbeUser->isSRQueueManager();
    }

    protected function isSdManager()
    {
        return $this->dbeUser->isSDManager();
    }

    protected function setMenuId(int $int)
    {
        $this->template->setVar('menuId', $int);
    }

    protected function isRenewalSalesOrderManager()
    {
        return $this->dbeUser->getValue(DBEUser::createRenewalSalesOrdersFlag) == 'Y';
    }

    protected function fetchAll($query, $params)
    {
        $db   = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASSWORD
        );
        $stmt = $db->prepare($query, $params);
        foreach ($params as $key => $value) {
            if (($params[$key] != null || $params[$key] == '0') && is_numeric($params[$key])) {
                $params[$key] = (int)$params[$key];
                $stmt->bindParam($key, $params[$key], PDO::PARAM_INT);
            } else
                $stmt->bindParam($key, $params[$key]);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    protected function console_log($output, $with_script_tags = true)
    {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }

    function getBody($associative=false){
        return json_decode(file_get_contents('php://input'),$associative);
    }

    function hideMenu()
    {
        $this->setHTMLFmt(CT_HTML_FMT_POPUP);
    }

    public function getParamOrNull($paramName)
    {
        if (!$paramName) {
            return null;
        }
        if (!isset($_REQUEST[$paramName])) {
            return null;
        }
        if (@$_REQUEST[$paramName] == '') {
            return null;
        }
        return $_REQUEST[$paramName];
    }

    function getRequestMethodeName()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    function getResponseError($code, $message)
    {
        http_response_code($code);
        return ["status" => false, "error" => $message];
    }
    public function success($data=null)
    {
        return ["state"=>true,"data"=>$data];
    }
    public function fail($code,$message="")
    {
        return new APIException($code,$message);

    }
}
