<?php

use CNCLTD\SupportedCustomerAssets\UnsupportedCustomerAssetService;

require_once __DIR__ . '/../htdocs/config.inc.php';
global $cfg;
$test = new UnsupportedCustomerAssetService();
var_dump($test->checkAssetUnsupported(0, ""));