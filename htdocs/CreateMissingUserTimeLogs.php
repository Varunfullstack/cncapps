<?php
/**
 * Check that MySQL backup has worked OK
 *
 * called as scheduled task at given time every day
 *
 * Check the date on the file and the size of it.
 * Email Gary, Karim and Roger on failure
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_bu"] . "/BUActivity.inc.php");

$date = @$_REQUEST['date'];
$thing = null;
$buActivity = new BUActivity($thing);
$buActivity->createUserTimeLogsForMissingUsers($date);
$buActivity->updateAllHistoricUserLoggedHours(new DateTime('-15 days'));
?>