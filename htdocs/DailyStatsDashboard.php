<?php

use CNCLTD\Controller\CTDailyStatsDashboard;

require_once("config.inc.php");
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
$ctStandardText = new CTDailyStatsDashboard(
    $_SERVER['REQUEST_METHOD'], $_POST, $_GET, $_COOKIE, $cfg
);
$ctStandardText->execute();
page_close();