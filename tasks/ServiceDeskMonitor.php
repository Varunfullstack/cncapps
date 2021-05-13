<?php

use CNCLTD\Data\DBEJProblem;
use CNCLTD\LoggerCLI;
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'DailySalesRequestEmail';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [
    "dryRun",
    "serviceRequestId:"
];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
$thing = null;

$dryRun = false;
if (isset($options['dryRun'])) {
    $dryRun = true;
}
$serviceRequestId = null;
if (isset($options['serviceRequestId'])) {
    $serviceRequestId = $options['serviceRequestId'];
}

$buProblemSLA = new BUProblemSLA($thing);
$buProblemSLA->monitor($dryRun, $serviceRequestId, $debugMode);
echo "Service Desk Monitor Routine Finished";
//
echo 'Start processing future SR\n';
$buActivity = new BUActivity($thing);
$dsProblems = $buActivity->getAlarmReachedProblems();
while ($dsProblems->fetchNext()) {
    $update = new DBEProblem($thing);
    $update->getRow($dsProblems->getValue(DBEJProblem::problemID));
    echo "<br>{$dsProblems->getValue(DBEJProblem::problemID)} is in breach resetting it to Awaiting CNC <br>";
    $buActivity->logOperationalActivity(
        $dsProblems->getValue(DBEJProblem::problemID),
        "Future alarm has been reached, resetting to Awaiting CNC",
        true
    );
    $update->setValue(DBEProblem::alarmDate, null);
    $update->setValue(DBEProblem::alarmTime, null);
    $update->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');

    $update->updateRow();
}
echo 'Finished processing future breached SR\'s';
?>