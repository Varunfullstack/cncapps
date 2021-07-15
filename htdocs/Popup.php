<?php

use CNCLTD\Utils;

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
global $cfg;
header("Cache-control: private");
page_close();

$currentVersion = Utils::getCurrentChangelogVersion();
?>


<html>
<head>
<link href="/screen.css?cache=7"
          rel="stylesheet"
          type="text/css"
    >
    
    <link rel='stylesheet'
          href='components/dist/PopUpComponent.css?<?= $currentVersion ?>'
    >
    <link rel="stylesheet"
          href="screen.css"
    >
    <script src='components/dist/PopUpComponent.js?<?= $currentVersion ?>'></script>
</head>
<body>
<div>
    <div id="reactMainPopup"
         name="reactMainPopup"
    ></div>
</div>
</body>
</html>