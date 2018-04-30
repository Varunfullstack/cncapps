<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/04/2018
 * Time: 9:17
 */

require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTNotAllowed.php");
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
$ctPassword= new CTNotAllowed(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctPassword->execute();
page_close();
?>