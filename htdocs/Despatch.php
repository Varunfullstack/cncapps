<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTDespatch.inc.php");
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
$ctDespatch= new CTDespatch(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctDespatch->execute();
page_close();
?>