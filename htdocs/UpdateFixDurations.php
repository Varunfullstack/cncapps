<?php
/**
 * Action Alert Email controller
 * CNC Ltd
 *
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