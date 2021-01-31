<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTProjectOptions.php");
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
$ctProjectOptions = new CTProjectOptions(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctProjectOptions->execute();
page_close();

?>