<?php

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
$logName = 'SupportedCustomerAssets';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
$dbeCustomer = new DBECustomer($thing);
$dbeCustomer->getActiveCustomers(true);
while ($dbeCustomer->fetchNext()){

}
$test = new \CNCLTD\SupportedCustomerAssets\SupportedCustomerAssets();


