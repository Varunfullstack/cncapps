<?php
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUActivity.inc.php");

$date = @$_REQUEST['date'];
$thing = null;
$buActivity = new BUActivity($thing);
$buActivity->updateAllHistoricUserLoggedHours(new DateTime('today'));
?>