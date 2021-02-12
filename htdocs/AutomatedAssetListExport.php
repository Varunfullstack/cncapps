<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */
require_once("config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require __DIR__ . '/../vendor/autoload.php';
global $db;
$dbeCustomer           = new DBECustomer($thing);
$generateSummary       = isset($_REQUEST['generateSummary']);
$customerID            = isset($_REQUEST['customerID']) ? $_REQUEST['customerID'] : null;
$generateWithMonthYear = isset($_REQUEST['generateWithMonthYear']);
$runOnce               = false;
$exporter              = new \CNCLTD\AssetListExport\AssetListExporter();
if ($customerID) {
    $exporter->exportForCustomer($customerID, $generateWithMonthYear);
    return;
}
if ($generateSummary) {
    $exporter->exportForActiveCustomersWithSummary();
    return;
}
$exporter->exportForActiveCustomers($generateWithMonthYear);
