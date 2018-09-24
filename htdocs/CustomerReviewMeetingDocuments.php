<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/09/2018
 * Time: 11:55
 */


require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTCustomerReviewMeetingDocuments.php");
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
$ctCustomerReviewMeetingDocuments = new CTCustomerReviewMeetingDocuments(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctCustomerReviewMeetingDocuments->execute();
page_close();
?>