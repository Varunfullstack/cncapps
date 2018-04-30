<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 23/04/2018
 * Time: 10:13
 */
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTUser.inc.php");
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
$ctUser= new CTUser(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctUser->execute();
page_close();
?>