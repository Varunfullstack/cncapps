<?php
require_once("config.inc.php");
require_once($cfg["path_bu"]."/BUDailySalesRequestEmail.inc.php");

$buDailySalesRequestEmail= new BUDailySalesRequestEmail( $this );
$buDailySalesRequestEmail->sendEmail();
?>