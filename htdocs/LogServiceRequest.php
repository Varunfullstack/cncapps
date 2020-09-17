<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTLogServiceRequest.inc.php");
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
$ctLogServiceRequest= new CTLogServiceRequest(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctLogServiceRequest->execute();
page_close();
?>

<link rel="stylesheet" href="components/style.css?version=<?= time() ?>">
<link rel="stylesheet" href="css/table.css?version=<?= time() ?>">
<link rel="stylesheet" href="components/LogServiceRequest/style.css?version=<?= time() ?>">

 <script src="js/react.development.js" crossorigin></script>
<script src="js/react-dom.development.js" crossorigin></script>

<!-- <script src="components/npm/node_modules/@ckeditor/ckeditor5-react/dist/ckeditor.js"></script> -->
<!-- <script src="ckeditor/ckeditor.js"></script> --> 

<script type="module" src='components/LogServiceRequest/CMPLogServiceRequest.js?version=<?= time() ?>'></script> 