<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 23/01/2019
 * Time: 11:04
 */
require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTPasswordAudit.php");
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
$ctPassword = new CTPasswordAudit(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctPassword->execute();
page_close();
?>