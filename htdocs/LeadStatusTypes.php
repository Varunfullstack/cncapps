<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_ct"]."/CTLeadStatusTypes.php");
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
$controller = new CTLeadStatusTypes(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$controller->execute();
page_close();
?>