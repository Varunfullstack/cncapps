<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUImportRequests.inc.php");
GLOBAL $cfg;
error_reporting(E_ALL);
$buImportRequests = new BUImportRequests($this);
$buImportRequests->createServiceRequests();
?>
