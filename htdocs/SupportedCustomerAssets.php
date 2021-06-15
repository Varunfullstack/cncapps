<?php

use CNCLTD\SupportedCustomerAssets\SupportedCustomerAssetsActiveCustomersHTMLGenerator;

require_once("config.inc.php");
$customerId = @$_GET['customerId'];

$generator = new SupportedCustomerAssetsActiveCustomersHTMLGenerator($customerId);
$generator->printHTML();
