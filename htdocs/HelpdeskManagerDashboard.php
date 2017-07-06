<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTHelpdeskManagerDashboard.inc.php");
session_start();
page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'perm' => PHPLIB_CLASSNAME_PERM,
		''
	)
);
header("Cache-control: private");
GLOBAL $cfg;
$ctHelpdeskManagerDashboard= new CTHelpdeskManagerDashboard(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctHelpdeskManagerDashboard->execute();
page_close();
?>