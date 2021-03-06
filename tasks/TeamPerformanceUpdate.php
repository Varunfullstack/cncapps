<?php

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
$logName = 'TeamPerformanceUpdate';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts  = ["updateSince:"];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
require_once($cfg["path_bu"] . "/BUTeamPerformance.inc.php");
$thing             = null;
$buTeamPerformance = new BUTeamPerformance($thing);
if (isset($options['updateSince'])) {
    $dateTimeString = $options['updateSince'];
    $dateTime       = DateTime::createFromFormat(DATE_MYSQL_DATE, $dateTimeString);
    if (!$dateTime) {
        echo 'updateSince must have the format YYYY-MM-DD';
        exit;
    }
    $today = new DateTime();
    while ($dateTime->format('Y-m') <= $today->format('Y-m')) {
        $year  = $dateTime->format('Y');
        $month = $dateTime->format('m');
        $buTeamPerformance->update($year, $month);
        $dateTime->add(new DateInterval('P1M'));
    }
    return;
}
$buTeamPerformance->update(
    date('Y'),
    date('m')
);
$buTeamPerformance->update(
    date(
        'Y',
        strtotime("-1 months")
    ),
    date(
        'm',
        strtotime("-1 months")
    )
)
?>