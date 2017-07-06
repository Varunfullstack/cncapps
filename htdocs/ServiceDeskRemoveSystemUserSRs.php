<?php
/**
* Clear down SRs assigned to system user
* 
* Run once per day
* 
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
$buActivity = new buActivity($this);
$buActivity->clearSystemSrQueue();
echo "System User SRs removed";
?>