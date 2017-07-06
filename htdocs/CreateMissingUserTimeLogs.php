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

require_once($cfg["path_bu"]."/BUActivity.inc.php");


$buActivity= new BUActivity( $this );
$buActivity->createUserTimeLogsForMissingUsers();
?>