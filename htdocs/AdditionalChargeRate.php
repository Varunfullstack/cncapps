<?php

use CNCLTD\Controller\CTAdditionalChargeRate;

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
global $inMemorySymfonyBus;
header("Cache-control: private");
$ctActivityType = new CTAdditionalChargeRate(
    $_SERVER['REQUEST_METHOD'], $_POST, $_GET, $_COOKIE, $cfg, $inMemorySymfonyBus
);
$ctActivityType->execute();
page_close();
