<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 29/05/2018
 * Time: 11:51
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg ['path_bu'] . '/BUDailyReport.inc.php');
$nothing = null;
$buDailyReport = new BUDailyReport($nothing);

$daysAgo = $_REQUEST['daysAgo'];
$onScreen = isset($_GET['onScreen']);

switch ($_REQUEST ['action']) {

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
        $buDailyReport->outstandingIncidents($daysAgo, null, $onScreen);
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
        break;
    default :
        break;
}