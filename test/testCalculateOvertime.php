<?php

require_once __DIR__.'/../htdocs/config.inc.php';


global $cfg;
require_once($cfg["path_dbe"] . "/DBConnect.php");
$test = new \CNCLTD\SupportedCustomerAssets\UnsupportedCustomerAssetService();

var_dump($test->checkAssetUnsupported(0,""));