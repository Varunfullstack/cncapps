<?php

use CNCLTD\Business\BUActivity;
use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
$logName = 'CreateMissingUserTimeLogs';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts  = [
    "date:"
];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
$date       = @$options['date'];
$thing      = null;
$buActivity = new BUActivity($thing);
$buActivity->createUserTimeLogsForMissingUsers($date);
$buActivity->updateAllHistoricUserLoggedHours(new DateTime('-15 days'));