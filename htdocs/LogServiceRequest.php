<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTLogServiceRequest.inc.php");
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

$ctLogServiceRequest = new CTLogServiceRequest(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctLogServiceRequest->execute();
page_close();
?>