<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:32
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTOffice365BackupAudit.php");
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
$ctContact = new CTOffice365BackupAudit(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctContact->execute();
page_close();
?>