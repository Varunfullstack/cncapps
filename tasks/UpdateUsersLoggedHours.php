<?php

use CNCLTD\Business\BUActivity;
use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
$logName = 'UpdateUsersLoggedHours';
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
$date = new DateTime('today');
if (isset($options['date'])) {
    $possibleDate = DateTime::createFromFormat('Y-m-d', $options['date']);
    if ($possibleDate) {
        $date = $possibleDate;
    }
}
$thing      = null;
$buActivity = new BUActivity($thing);
$buActivity->updateAllHistoricUserLoggedHours($date);