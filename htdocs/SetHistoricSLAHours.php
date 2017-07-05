<?php
/**
* Populate SLAHours field on historic Requests
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUSetHistoricSLAHours.inc.php');
$buSetHistoricSLAHours = new BUSetHistoricSLAHours($this);
$buSetHistoricSLAHours->update();
echo "Finished setting hoistoric SLA hours";
?>