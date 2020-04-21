<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTContractMatrix.php");
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        false,
        false,
        false
    )
);
header("Cache-control: private");
$controller = new CTContractMatrix(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$controller->execute();
page_close();
