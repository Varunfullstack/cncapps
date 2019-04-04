<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUEmailRequest.inc.php");
GLOBAL $cfg;
$thing = null;
header("Cache-control: private");
$buEmailRequest = new BUEmailRequest($thing);
$buEmailRequest->createServiceRequestsFromEmails();
?>
