<?php
/**
* Action Alert Email controller
* CNC Ltd
*
*	Sends emails to internal email addresses when future actions on the 
* future_actions table become due.
*
* The rows are then deleted.
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
$buProblemSLA = new BUProblemSLA($this);
$buProblemSLA->updateFixDurations();
echo "Update Service Fix Durations Finished";
?>