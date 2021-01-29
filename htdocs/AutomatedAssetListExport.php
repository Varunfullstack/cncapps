<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use PhpOffice\PhpSpreadsheet\Style\Fill;

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
$dbeCustomer     = new DBECustomer($thing);
$generateSummary = isset($_REQUEST['generateSummary']);
$customerID      = isset($_REQUEST['customerID']) ? $_REQUEST['customerID'] : null;
$runOnce         = false;
$exporter        = new \CNCLTD\AssetListExport\AssetListExporter();
if ($customerID) {
    $exporter->exportForCustomer($customerID);
    return;
}
if ($generateSummary) {
    $exporter->exportForActiveCustomersWithSummary();
    return;
}
$exporter->exportForActiveCustomers();

//
//
//
//
//$buCustomer    = new BUCustomer($thing);
//$thresholdDate = new DateTime();
//$thresholdDate->add(new DateInterval('P' . $thresholdDays . 'D'));
//$today             = new DateTime();
//$currentSummaryRow = 1;
//if ($generateSummary) {
//    $summarySpreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
//    $summarySpreadSheet->getDefaultStyle()->getFont()->setName('Arial');
//    $summarySpreadSheet->getDefaultStyle()->getFont()->setSize(10);
//    $summarySheet = $summarySpreadSheet->getActiveSheet();
//    $isHeaderSet  = false;
//}
//
//
//
//while ($runOnce || $dbeCustomer->fetchNext()) {
//    $runOnce = false;
//    $customerID   = $dbeCustomer->getValue(DBECustomer::customerID);
//    $customerName = $dbeCustomer->getValue(DBECustomer::name);
//    echo '<div>Getting Labtech Data for Customer: ' . $customerID . ' - ' . $customerName . '</div>';
//
//
//




