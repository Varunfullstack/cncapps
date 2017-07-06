<?php
echo 'yay';
return;
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTHome.inc.php");
/*
if ( $_REQUEST[ 'action' ] == 'logout' ){
  $sessionClass = PHPLIB_CLASSNAME_SESSION;
  $sess = new $sessionClass;
  $sess->delete();
  header( 'Location:');
  exit;
}
*/
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
$ctPage= new CTHome(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctPage->execute();
page_close();
?>