<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;
use CNCLTD\Office365LicensesExportPowerShellCommand;

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEOffice365License.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
global $db;
$logName = 'office365LicenseExport';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts  = "c:drag";
$longopts   = array(
    "customer:",
);
$options    = getopt($shortopts, $longopts);
$customerID = null;
if (isset($options['c'])) {
    $customerID = $options['c'];
    unset($options['c']);
}
if (isset($options['customer'])) {
    $customerID = $options['customer'];
    unset($options['customer']);
}
$reuseData = false;
if (isset($options['r'])) {
    $reuseData = true;
}
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$alertMode = false;
if (isset($options['a'])) {
    $alertMode = true;
}
$generateMonthYear = false;
if (isset($options['g'])) {
    $generateMonthYear = true;
}
$dbeCustomer = new DBECustomer($thing);
if (isset($customerID)) {
    $dbeCustomer->getRow($customerID);
    if (!$dbeCustomer->rowCount) {
        $logger->error("Customer not found");
        exit;
    }
} else {
    $dbeCustomer->getActiveCustomers(true);
    if (!$dbeCustomer->getNumRows()) {
        $logger->warning('There are no active customers');
        exit;
    }
    $dbeCustomer->fetchNext();
}
$BUHeader  = new BUHeader($thing);
$dbeHeader = new DataSet($thing);
$BUHeader->getHeader($dbeHeader);
$yellowThreshold = $dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold);
$redThreshold    = $dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold);
if (!$yellowThreshold || !$redThreshold) {
    throw new Exception('Yellow and Red Threshold values are required');
}
$buCustomer           = new BUCustomer($thing);
$buPassword           = new BUPassword($thing);
$dbeOffice365Licenses = new DBEOffice365License($thing);
do {
    try {
        $commandRunner = new Office365LicensesExportPowerShellCommand(
            $dbeCustomer, $logger, $debugMode, $alertMode, $reuseData, $generateMonthYear
        );
    } catch (Exception $exception) {
        continue;
    }
} while ($dbeCustomer->fetchNext());

