<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTCustomerProfitabilityReport.inc.php");
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
$ctCustomerProfitabilityReport= new CTCustomerProfitabilityReport(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctCustomerProfitabilityReport->execute();
page_close();
?>