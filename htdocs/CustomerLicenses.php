<?php
require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTSCustomerLicenses.inc.php");
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
$ctSCustomerLicenses = new CTSCustomerLicenses(
    $_SERVER['REQUEST_METHOD'],
    $_POST,
    $_GET,
    $_COOKIE,
    $cfg
);
$ctSCustomerLicenses->execute();
page_close();
?>

<script src='components/dist/CustomerLicensesComponent.js'></script>
<link rel="stylesheet"
      href='components/dist/CustomerLicensesComponent.css'
/>