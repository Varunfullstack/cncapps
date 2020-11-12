<?php
require_once("config.inc.php"); 
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
page_close();

?>


<html>
<head>
<link href="/screen.css?cache=7" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="fonts/style.css">
<!-- For React -->
<link rel="stylesheet" href="components/style.css?version=<?= time() ?>">
<link rel="stylesheet" href="css/table.css?version=<?= time() ?>">
<link rel="stylesheet" href="components/SDManagerDashboard/style.css?version=<?= time() ?>">

<script src="js/react.production.min.js" crossorigin></script>
<script src="js/react-dom.production.min.js" crossorigin></script>
<script type="module" src='components/popup/CMPPopup.js?version=<?= time() ?>'></script> 
</head>
<body >
<div >
    <div id="reactMainPopup" name="reactMainPopup"></div>
</div>
</body>
</html>