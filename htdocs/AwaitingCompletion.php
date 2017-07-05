<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTAwaitingCompletion.inc.php");
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
//header("Cache-control: private");
$ctAwaitingCompletion= new CTAwaitingCompletion(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctAwaitingCompletion->execute();
page_close();
?>