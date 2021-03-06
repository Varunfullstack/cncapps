<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTSRActivity.inc.php");
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
header("Cache-control: private");
$ctActivity = new CTSRActivity(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctActivity->execute();
page_close();
?>