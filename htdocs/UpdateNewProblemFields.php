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
require_once($cfg['path_bu'] . '/BUUpdateNewProblemFields.inc.php');
$buUpdateNewProblemFields = new BUUpdateNewProblemFields($this);
$buUpdateNewProblemFields->update();
echo "Finished setting historic problem fields";
?>