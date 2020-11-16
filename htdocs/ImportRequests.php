<?php
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_bu"] . "/BUImportRequests.inc.php");
global $cfg;
error_reporting(E_ALL);
$thing = null;
$buImportRequests = new BUImportRequests($thing);
$buImportRequests->createServiceRequests();
?>
