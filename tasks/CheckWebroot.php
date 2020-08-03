<?php

use CNCLTD\LoggerCLI;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
global $db;
$logName = 'CheckWebroot';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
$user = "adrianc@cnc-ltd.co.uk";
$password = '$pv29gTMDJ#x!!9';
$client_Id = 'client_e2maZ8d5@cnc-ltd.co.uk';
$client_secret = '{1!XM^QJcqvM8qj';
$gsmKey = "2FB2-LTSW-E06B-3F49-43DC";

// we are going to ask for a new access token
$webrootAPI = new \CNCLTD\WebrootAPI\WebrootAPI($user, $password, $client_Id, $client_secret, $gsmKey);

$sitesResponse = $webrootAPI->getSites();
foreach ($sitesResponse->sites as $site) {
    // we have to find each client based on the site name
//    $dbeCustomer = new DBECustomer();
//    $dbeCustomer->setValue(DBECustomer::name, $site->siteName);
//    $dbeCustomer->getcu
    var_dump($site->siteName, $site->totalEndpoints);
}