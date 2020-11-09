<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 15/02/2019
 * Time: 10:36
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTBookSalesVisit.php");
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
//header("Cache-control: private");
$ctAwaitingCompletion = new CTBookSalesVisit(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctAwaitingCompletion->execute();
page_close();