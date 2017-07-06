<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTCustomerReviewMeeting.inc.php");
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
$ctCustomerReviewMeeting= new CTCustomerReviewMeeting(
  $_SERVER['REQUEST_METHOD'],
  $_POST,
  $_GET,
  $_COOKIE,
  $cfg
);
$ctCustomerReviewMeeting->execute();
page_close();
?>