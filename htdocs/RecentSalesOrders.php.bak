<?php
require_once("config.inc.php");
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_CNC_CLASSES . 'order.php');

page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);

header("Cache-control: private");

class CTRecentSalesOrders extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
        $business = &new CNC_Order();
        $_REQUEST['show_edit'] = 1;
        $_REQUEST['show_fields'] = 0;
        $_REQUEST['show_filters'] = 0;
        $_REQUEST['show_page_views'] = 0;
        $_REQUEST['page_view.page_view_id'] = 2;
        $_REQUEST['edit_url'] = 'SalesOrder.php?action=displaySalesOrder&ordheadID=';
        $this->setMethodName('defaultAction');
// Parameters
        $this->setPageTitle("Recent Sales Orders");
        ob_start();
        $_REQUEST['where_statement'] = 'ordhead.odh_type IN (\'I\',\'P\',\'C\')';
        require(CONFIG_PATH_SC_HTML . 'page_list.php');

        $contents = ob_get_contents();

        ob_end_clean();
        $this->setTemplateFiles('');
        $this->template->set_var('CONTENTS', $contents);
        $this->parsePage();
    }
}

GLOBAL $cfg;
$ctRecentSalesOrders = new CTRecentSalesOrders(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);

$ctRecentSalesOrders->execute();

page_close();
?>