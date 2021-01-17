<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTRenewalsDashboard.php");
session_start();
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
global $cfg;
header("Cache-control: private");
$obj = new CTRenewalsDashboard(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$obj->execute();
page_close();

?>