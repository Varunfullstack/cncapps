<?php
require_once("../config.inc.php");
require_once($cfg["path_bu"]."/BUTeamPerformance.inc.php");
$buTeamPerformance = new BUTeamPerformance( $this );

$buTeamPerformance->setHistoricStartedByUsers();
?>