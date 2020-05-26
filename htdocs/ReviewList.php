<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"] . "/CTReviewList.inc.php");
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

$ctReviewList = new CTReviewList(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);

$ctReviewList->execute();

page_close();
?>