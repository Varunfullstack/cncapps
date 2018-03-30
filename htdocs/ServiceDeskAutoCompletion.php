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
$buProblemSLA = new BUProblemSLA($this);

$deleted = $buProblemSLA->deleteNonHumanServiceRequests();

echo "Deleted SRs: " . $deleted . "<BR/>";

$buProblemSLA->autoCompletion();

echo "Service Desk Auto Complete Routine Finished";
?>