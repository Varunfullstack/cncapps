<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUDailySalesRequestEmail.inc.php");
$thing = null;
$buDailySalesRequestEmail = new BUDailySalesRequestEmail($thing);
$buDailySalesRequestEmail->sendEmail();
?>