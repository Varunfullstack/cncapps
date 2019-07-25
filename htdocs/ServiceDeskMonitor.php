<?php
/**
 * Action Alert Email controller
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
$thing = null;

$dryRun = false;
if (isset($_REQUEST['dryRun'])) {
    $dryRun = true;
}

$problemID = null;
if (isset($_REQUEST['problemID'])) {
    $problemID = $_REQUEST['problemID'];
}

$debug = null;
if (isset($_REQUEST['debug'])) {
    $debug = $_REQUEST['debug'];
}

$buProblemSLA = new BUProblemSLA($thing);
$buProblemSLA->monitor($dryRun, $problemID, $debug);
echo "Service Desk Monitor Routine Finished";
?>