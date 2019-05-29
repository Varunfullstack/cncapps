<?php
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUActivity.inc.php");

$date = new DateTime('today');
if (isset($_REQUEST['date'])) {
    $possibleDate = DateTime::createFromFormat('Y-m-d', $_REQUEST['date']);
    if ($possibleDate) {
        $date = $possibleDate;
    }
}
$thing = null;
$buActivity = new BUActivity($thing);
$buActivity->updateAllHistoricUserLoggedHours($date);
?>