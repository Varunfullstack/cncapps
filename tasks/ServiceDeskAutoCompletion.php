<?php

use CNCLTD\LoggerCLI;
use Monolog\Logger;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
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
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
$logName      = 'ServiceDeskAutoCompletion';
$logger       = new LoggerCLI($logName, $debugMode ? Logger::DEBUG : Logger::INFO);
$thing        = null;
$buProblemSLA = new BUProblemSLA($thing);
$deleted = $buProblemSLA->deleteNonHumanServiceRequests();
echo "Deleted SRs: " . $deleted . "<BR/>";
$buProblemSLA->autoCompletion($logger);
echo "Service Desk Auto Complete Routine Finished";
?>