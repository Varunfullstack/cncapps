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

use CNCLTD\Business\BUActivity;

require_once("config.inc.php");
global $cfg;
$thing      = null;
$buActivity = new BUActivity($thing);
$buActivity->sendUncheckedActivityEmail();
?>