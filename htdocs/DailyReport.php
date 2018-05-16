<?php
require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUDailyReport.inc.php");

$buDailyReport = new BUDailyReport($this);

if ($_REQUEST['daysAgo']) {
    $daysAgo = $_REQUEST['daysAgo'];
} else {
    $daysAgo = 1;
}

switch ($_REQUEST['action']) {

    case 'fixedIncidents' :
        $buDailyReport->fixedIncidents($daysAgo);
        break;
    case 'focActivities' :
        $buDailyReport->focActivities($daysAgo);
        break;
    case 'prepayOverValue' :
        $buDailyReport->prepayOverValue($daysAgo);
        break;
    case 'outstandingIncidents' :
        $buDailyReport->outstandingIncidents($daysAgo);
        break;
    case 'outstandingPriorityFiveIncidents' :
        $buDailyReport->outstandingIncidents($daysAgo, true);
        break;
    case 'p5SRWithoutSalesOrders':
        $buDailyReport->p5IncidentsWithoutSalesOrders();
        break;
    case 'p5SRWithSalesOrdersAndContract':
        $buDailyReport->p5WithSalesOrderAndContractAssigned();
        break;
    case 'contactOpenSRReport':
        $buDailyReport->contactOpenSRReport();
}
?>