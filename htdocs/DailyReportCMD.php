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

$daysAgo = isset($_REQUEST['daysAgo']) ? $_REQUEST['daysAgo'] : null;
$onScreen = isset($_GET['onScreen']);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($action) {

    case 'fixedIncidents' :
        $buDailyReport->fixedIncidents($daysAgo, true);
        break;
    case 'focActivities' :
        $buDailyReport->focActivities($daysAgo);
        break;
    case 'prepayOverValue' :
        $buDailyReport->prepayOverValue($daysAgo);
        break;
    case 'outstandingIncidents' :
        $buDailyReport->outstandingIncidents($daysAgo, false, $onScreen, false, true);
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
        $buDailyReport->contactOpenSRReport($onScreen);
        break;
    default :
        break;
}