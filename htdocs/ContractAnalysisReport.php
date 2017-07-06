<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTContractAnalysisReport.inc.php");
session_start();
page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
    false,
    false,
		false
	)
);
GLOBAL $cfg;
header("Cache-control: private");
$ctContractAnalysisReport= new CTContractAnalysisReport(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctContractAnalysisReport->execute();
page_close();
?>