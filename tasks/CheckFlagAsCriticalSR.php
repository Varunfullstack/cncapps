<?php


/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;

require_once(__DIR__."/../htdocs/config.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
global $db;
$logName = 'CheckFlagAsCritical';
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
$dbeProblem = new DBEProblem($hing);
$dbeProblem->getToCheckCriticalFlagSRs();

$buHeader = new BUHeader($thing);
$dsHeader = new DataSet($thing);
$buHeader->getHeader($dsHeader);
$prioritiesHours = [
    1 => $dsHeader->getValue(DBEHeader::autoCriticalP1Hours),
    2 => $dsHeader->getValue(DBEHeader::autoCriticalP2Hours),
    3 => $dsHeader->getValue(DBEHeader::autoCriticalP3Hours),
];


while ($dbeProblem->fetchNext()) {
    $logger->info('Checking SR: ' . $dbeProblem->getValue(DBEProblem::problemID));

    if ($dbeProblem->getValue(DBEProblem::chargeableActivityDurationHours) >= $prioritiesHours[$dbeProblem->getValue(
            DBEProblem::priority
        )]) {
        $logger->info(
            'This SR is going to be flagged as critical - Priority: ' . $dbeProblem->getValue(
                DBEProblem::priority
            ) . " ChargeableHours: " . $dbeProblem->getValue(
                DBEProblem::chargeableActivityDurationHours
            ) . " Threshold: " . $prioritiesHours[$dbeProblem->getValue(
                DBEProblem::priority
            )]
        );
        $updateProblem = new DBEProblem($thing);
        $updateProblem->getRow($dbeProblem->getValue(DBEProblem::problemID));
        $updateProblem->setValue(DBEProblem::criticalFlag, 'Y');
        $updateProblem->updateRow();
    }
}
