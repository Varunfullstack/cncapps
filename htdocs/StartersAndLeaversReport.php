<?php

/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/01/2019
 * Time: 11:26
 */

require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTStarterAndLeaverReport.php");
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
$ctStandardText = new CTStarterAndLeaverReport(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctStandardText->execute();
page_close();
