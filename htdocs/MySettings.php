
<?php
require_once("config.inc.php");
require_once($cfg["path_ct"]."/CTMySettings.inc.php");
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
$ctMySettings= new CTMySettings(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);
$ctMySettings->execute();
 
page_close();
?>
<link rel="stylesheet" href="components/style.css">
 

<script src="js/react.development.js" crossorigin></script>
<script src="js/react-dom.development.js" crossorigin></script>
 <script type="module" src='components/CMPMySettings.js?version=<?= time() ?>'></script>

 