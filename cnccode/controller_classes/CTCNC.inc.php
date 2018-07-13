<?php
/**
 * CNC base controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once($cfg ['path_gc'] . '/DataSet.inc.php');
require_once($cfg ['path_gc'] . '/Controller.inc.php');
require_once($cfg ['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg ['path_dbe'] . '/DBETeam.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');

define(
    'CTCNC_ACT_DISP_CUST_POPUP',
    'dispCustPopup'
);
define(
    'CTCNC_ACT_DISP_ITEM_POPUP',
    'dispItemPopup'
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
    var $dbeUser;
    var $dbeTeam;
    private $user;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        if (!$this->isRunningFromCommandLine() && isset($GLOBALS ['auth'])) {
            $this->userID = $GLOBALS ['auth']->is_authenticated();
        } else {
            $this->userID = CONFIG_SCHEDULED_TASK_USER_ID;
        }

        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            'userID',
            $this->userID
        );
        $dbeUser->getRow();

        $this->user = new BUUser($this);

        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg,
            "",
            "",
            "",
            ""
        );
    }

    protected function isSdManager()
    {
        return $this->dbeUser->getValue(DBEJUser::receiveSdManagerEmailFlag) == 'Y';
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

    function getDbeUser()
    {
        if (!$this->dbeUser) {
            $this->dbeUser = new DBEUser ($this);
        }
        return $this->dbeUser;
    }

    function getUser()
    {

    }

    function getDbeTeam()
    {
        if (!$this->dbeTeam) {
            $this->dbeTeam = new DBETeam ($this);
        }
        return $this->dbeTeam;
    }

    /**
     * Is the request from the command line (or scheduled task)
     *
     * @return unknown
     */
    function isRunningFromCommandLine()
    {
        return ($GLOBALS['isRunningFromCommandLine']);

    }

    /**
     * Check a date in dd/mm/yyyy format
     * @access private
     */
    function isValidDate($dateDMY)
    {
        $dateArray = explode(
            '/',
            $dateDMY
        );
        return @checkdate(
            $dateArray [1],
            $dateArray [0],
            $dateArray [2]
        );
    }

    function logout()
    {
        $GLOBALS ['sess']->delete();
        header("Location: index.php");
        exit;
    }

    function parsePage()
    {
        global $userName;


        $urlLogout = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_LOGOUT)
        );
        // if new session then username not set yet
        if ($userName == '') {
            $dbeUser = new DBEUser ($this);
            $dbeUser->setValue(
                'userID',
                $this->userID
            );
            $dbeUser->getRow();
            $userName = $dbeUser->getValue('name');
        }

        $screenSalesTemplate = 'ScreenSales.inc';
        $screenAccountsTemplate = 'ScreenAccounts.inc';
        $screenTechnicalTemplate = 'ScreenTechnical.inc';
        $screenRenewalsTemplate = 'ScreenRenewals.inc';
        $screenMaintenanceTemplate = 'ScreenMaintenance.inc';
        $screenReportsTemplate = 'ScreenReports.inc';
        $screenCustomerTemplate = 'ScreenCustomer.inc';


        if (isset($_REQUEST['oldMenu'])) {
            $screenSalesTemplate = 'ScreenSalesOld.inc';
            $screenAccountsTemplate = 'ScreenAccountsOld.inc';
            $screenTechnicalTemplate = 'ScreenTechnicalOld.inc';
            $screenRenewalsTemplate = 'ScreenRenewalsOld.inc';
            $screenMaintenanceTemplate = 'ScreenMaintenanceOld.inc';
            $screenReportsTemplate = 'ScreenReportsOld.inc';
            $screenCustomerTemplate = 'ScreenCustomerOld.inc';
        }

        $this->template->set_var(array('userName' => $userName, 'fromDate' => '', 'urlLogout' => $urlLogout));
        // display correct menus despending upon permission levels for this user
        if ($this->hasPermissions(PHPLIB_PERM_SALES)) {

            $this->setTemplateFiles(array('ScreenSales' => $screenSalesTemplate));
            $this->template->parse(
                'screenSales',
                'ScreenSales',
                true
            );
        }
        if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
            $this->setTemplateFiles(array('ScreenAccounts' => $screenAccountsTemplate));
            $this->template->parse(
                'screenAccounts',
                'ScreenAccounts',
                true
            );
        }
        if ($this->hasPermissions(PHPLIB_PERM_TECHNICAL)) {
            $this->setTemplateFiles(array('ScreenTechnical' => $screenTechnicalTemplate));
            $this->template->parse(
                'screenTechnical',
                'ScreenTechnical',
                true
            );

        }
        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $this->setTemplateFiles(array('ScreenRenewals' => $screenRenewalsTemplate));
            $this->template->parse(
                'screenRenewals',
                'ScreenRenewals',
                true
            );
        }
        if ($this->hasPermissions(PHPLIB_PERM_MAINTENANCE)) {
            $this->setTemplateFiles(array('ScreenMaintenance' => $screenMaintenanceTemplate));
            $this->template->parse(
                'screenMaintenance',
                'ScreenMaintenance',
                true
            );
        }
        if ($this->hasPermissions(PHPLIB_PERM_REPORTS)) {
            $this->setTemplateFiles(array('ScreenReports' => $screenReportsTemplate));
            $this->template->parse(
                'screenReports',
                'ScreenReports',
                true
            );
        }
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $this->setTemplateFiles(array('ScreenCustomer' => $screenCustomerTemplate));
            $this->template->parse(
                'screenCustomer',
                'ScreenCustomer',
                true
            );
        }

        parent::parsePage();
    }

    function initialProcesses()
    {
        if ($_REQUEST ['htmlFmt'] != '') {
            $this->setHTMLFmt($_REQUEST ['htmlFmt']);
        }

        $user = self::getDbeUser();

        switch ($_REQUEST ['action']) {
            case CTCNC_ACT_LOGOUT :
                $this->logout();
                break;
        }
    }

    function checkPermissions($levels)
    {
        if (!$this->hasPermissions($levels)) {
            $this->displayFatalError('You do not have the permissions required for the requested operation');
        }
    }


    function hasPermissions($levels)
    {

        if ($this->isRunningFromCommandLine()) {

            return true;
        }

        $permissions = explode(
            ",",
            self::getDbeUser()->getValue('perms')
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

    function teamLevelIs($level)
    {
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            'userID',
            $this->userID
        );
        $dbeUser->getRow();

        $dbeTeam = $this->getDbeTeam();
        $dbeTeam->setValue(
            'teamID',
            $dbeUser->getValue('teamID')
        );
        $dbeTeam->getRow();

        if ($dbeTeam->getValue('level') >= $level) {
            $ret = true;
        } else {
            $ret = false;
        }

        return $ret;
    }

    function canChangeSrPriority()
    {
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            'userID',
            $this->userID
        );
        $dbeUser->getRow();

        if ($dbeUser->getValue('changePriorityFlag') == 'Y') {
            $ret = true;
        } else {
            $ret = false;
        }

        return $ret;
    }

    function getChecked($flag)
    {
        return ($flag == 'N' ? '' : CT_CHECKED);
    }
} // end of class
?>