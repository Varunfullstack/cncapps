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
$thing = null;
$buActivity = new buActivity($thing);
$buActivity->clearSystemSrQueue();
echo "System User SRs removed";
?>