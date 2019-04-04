<?php
/**
 * Unchecked actvity Email controller
 * CNC Ltd
 *
 *    Sends email to roger showing unchecked activities
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
$thing = null;
$buActivity = new BUActivity($thing);
$buActivity->sendUncheckedActivityEmail();
?>