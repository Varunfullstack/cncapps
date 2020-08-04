<?php

use CNCLTD\LoggerCLI;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
//require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
//require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");
//require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
//require_once($cfg['path_bu'] . '/BUHeader.inc.php');
//require_once($cfg['path_bu'] . '/BUExpense.inc.php');
global $db;
$logName = 'CheckDUO';
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

$integrationKey = "DI6FY9277NHNHTD7ZXN1";
$secret = "zAOdK7JTpE0xVLzVrVjkVd0LukEe4RyhsmU5Kq64";
$apiHostname = "api-8f3a2990.duosecurity.com";

$duoAPI = new \CNCLTD\DUOApi\DUOApi($secret, $integrationKey, $apiHostname);

foreach ($duoAPI->getAccountsList() as $account) {
    var_dump($account->name);
}