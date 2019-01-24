<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/01/2019
 * Time: 12:32
 */

require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTDailyReport.inc.php");
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
$ctStandardText= new CTAbout(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctStandardText->execute();
page_close();
?>