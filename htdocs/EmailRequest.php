<?php
require_once("config.inc.php");
require_once($cfg["path_bu"]."/BUEmailRequest.inc.php");
GLOBAL $cfg;
header("Cache-control: private");
$buEmailRequest = new BUEmailRequest( $this );
$buEmailRequest->createServiceRequestsFromEmails();
?>
