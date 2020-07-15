<?php


use CNCLTD\LoggerCLI;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg["path_bu"] . "/BUProblemSLA.inc.php");
$logName = 'CheckFixSLA';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
$thing = null;
$buProblemSLA = new BUProblemSLA($thing);
$buProblemSLA->checkFixSLATask($logger);