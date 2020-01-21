<?php

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTOffice365StorageReports.php");

session_start();
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        'perm' => PHPLIB_CLASSNAME_PERM,
        ''
    )
);
GLOBAL $cfg;
header("Cache-control: private");
$ctOffice365Licenses = new CTOffice365StorageReports(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctOffice365Licenses->execute();
page_close();
