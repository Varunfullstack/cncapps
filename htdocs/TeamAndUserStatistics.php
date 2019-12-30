<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTTeamAndUserStatistics.inc.php");
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
$ctEscalationReport = new CTTeamAndUserStatistics(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctEscalationReport->execute();
page_close();
?>