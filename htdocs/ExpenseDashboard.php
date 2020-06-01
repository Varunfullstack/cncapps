<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */


require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTExpenseDashboard.php");
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
$ctContact = new CTExpenseDashboard(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctContact->execute();
page_close();
?>