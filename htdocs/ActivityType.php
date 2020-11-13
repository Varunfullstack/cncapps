<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTActivityType.inc.php");
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
$ctActivityType= new CTActivityType(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctActivityType->execute();
page_close();
?>
<!-- For React -->
<link rel="stylesheet" href="components/style.css?version=<?= time() ?>">
<link rel="stylesheet" href="css/table.css?version=<?= time() ?>">

<script src="js/react.production.min.js" crossorigin></script>
<script src="js/react-dom.production.min.js" crossorigin></script>
<script type="module" src='components/ActivityTypeComponent/ActivityTypeComponent.js?version=<?= time() ?>'></script>