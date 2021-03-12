<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTPurchaseOrder.inc.php");
session_start();
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
header("Cache-control: private");

$ctPurchaseOrder = new CTPurchaseOrder(
    $_SERVER['REQUEST_METHOD'], $_POST, $_GET, $_COOKIE, $cfg
);
$ctPurchaseOrder->execute();
page_close();
?>