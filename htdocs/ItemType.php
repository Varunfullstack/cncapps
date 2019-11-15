<?php

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTItemType.php");
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
$blankArray = array();
$controller = new CTItemType(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$controller->execute();
page_close();
