<?php
/**
 * Weekly Emeil to customers listing incomplete activities
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
$thing = null;
$buActivity = new BUActivity($thing);
$buActivity->sendWeeklyOSActivityEmails();
?>