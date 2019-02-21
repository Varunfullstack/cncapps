<?php
require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTUtilityEmail.inc.php");

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
$ctUtilityEmail = new CTUtilityEmail(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctUtilityEmail->execute();
page_close();
?>