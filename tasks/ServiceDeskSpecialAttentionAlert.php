<?php
/**
 * Action Alert Email controller
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'ServiceDeskSpecialAttentionAlert';
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
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
$thing = null;
$buProblemSLA = new BUProblemSLA($thing);
$buProblemSLA->specialAttentionEmailAlert();
$buProblemSLA->clearBreachedSpecialAttentionCustomers();
echo "Special Attention Customer Email Alert Routine Finished";
?>