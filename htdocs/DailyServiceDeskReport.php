<?php
require_once("config.inc.php");
require_once($cfg["path_bu"]."/BUDailyServiceDeskReport.inc.php");

$buDailyServiceDeskReport= new BUDailyServiceDeskReport( $this );
if (
  $buDailyServiceDeskReport->slaLoggedToday > 0 OR
  $buDailyServiceDeskReport->nonSlaLoggedToday > 0
  
  ){
  $buDailyServiceDeskReport->produceReport();
}
?>