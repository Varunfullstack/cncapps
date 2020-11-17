<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 27/09/2018
 * Time: 12:06
 */

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'UpdateHomeData';
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
require_once ($cfg['path_bu']) . '/BUHome.php';
$buHome = new BUHome();

$buHome->updateAll();
?>