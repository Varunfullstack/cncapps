<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTScoTrans.inc.php");
GLOBAL $cfg;
$ctScoTrans= new CTScoTrans(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
while (TRUE){
	sleep(2);
	$ctScoTrans->execute();
}
?>