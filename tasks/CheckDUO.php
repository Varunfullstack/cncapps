<?php

use CNCLTD\Business\BUActivity;
use CNCLTD\DUOApi\DUOApi;
use CNCLTD\DUOUsersReportGenerator\DUOClientReportGenerator;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\LoggerCLI;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
global $db;
$logName = 'CheckDUO';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing               = null;
$integrationKey      = "DI6FY9277NHNHTD7ZXN1";
$secret              = "zAOdK7JTpE0xVLzVrVjkVd0LukEe4RyhsmU5Kq64";
$apiHostname         = "api-8f3a2990.duosecurity.com";
$duoAPI              = new DUOApi($secret, $integrationKey, $apiHostname);
$buActivity          = new BUActivity($thing);
$adminIntegrationKey = "DIOIQ82CWQOP0RC76Q0Z";
$adminSecret         = "Y2gc5HU5sKSuutTub7xqXwx4EelOJqzzQMmOuAtY";
$adminHostName       = "api-8f3a2990.duosecurity.com";
/**
 * @param DUOApi $clientDUO
 * @param DBECustomer $dbeCustomer
 * @throws ColumnOutOfRangeException
 */
function updateClientReport(DUOApi $clientDUO, DBECustomer $dbeCustomer, $accountId)
{

    $minTime = new DateTime();
    $minTime->sub(new DateInterval("P60D"));
    $duoReportGenerator       = new DUOClientReportGenerator();
    $spreadsheet              = $duoReportGenerator->getReportData(
        $clientDUO->getUsers($accountId),
        $clientDUO->getAuthenticationLogs($accountId, $minTime)
    );
    $buPortalCustomerDocument = new BUPortalCustomerDocument($thing);
    $buPortalCustomerDocument->addOrUpdateDUOClientReportDocument(
        $dbeCustomer->getValue(DBECustomer::customerID),
        $spreadsheet
    );
}

foreach ($duoAPI->getAccountsList() as $account) {
    $dbeCustomer = new DBECustomer($thing);
    $dbeCustomer->getCustomerByName($account->name);
    if (!$dbeCustomer->rowCount()) {
        $buActivity->raiseDuoCustomerNotMatchedSR($account);
        $logger->warning("Could not match a customer for account with name {$account->name}, raising SR!");
        continue;
    }
    $dbeCustomer->fetchNext();
    try {
        updateClientReport($duoAPI, $dbeCustomer, $account->accountId);
    } catch (Exception $exception) {
        $logger->error('Could not update client DUO Excel Document');
    }
    $dbeCustomerItem = new DBECustomerItem($thing);
    $dbeCustomerItem->getRowsByCustomerAndItemID(
        $dbeCustomer->getValue(DBECustomer::customerID),
        CONFIG_DUO_ITEMID,
        true
    );
    if (!$dbeCustomerItem->fetchNext()) {
        try {
            $buActivity->raiseDuoContractNotFound($account, $dbeCustomer);
            $logger->warning("Could not find a contract for customer {$account->name}, raising SR!");
        } catch (Exception $exception) {
            $logger->error($exception);
        }
        continue;
    }
    $contractId = $dbeCustomerItem->getValue(DBECustomerItem::customerItemID);
    $dbeCustomerItem->getRow($contractId);
    $accountInfo = $duoAPI->getAccountInfo($account->accountId);
    $dbeCustomerItem->setValue(DBECustomerItem::users, $accountInfo->userCount);
    $dbeCustomerItem->setValue(
        DBECustomerItem::curUnitSale,
        $accountInfo->userCount * 12 * $dbeCustomerItem->getValue(
            DBECustomerItem::salePricePerMonth
        )
    );
    $dbeCustomerItem->setValue(
        DBECustomerItem::curUnitCost,
        $accountInfo->userCount * 12 * $dbeCustomerItem->getValue(
            DBECustomerItem::costPricePerMonth
        )
    );
    $dbeCustomerItem->updateRow();
    $logger->info(
        "Customer {$dbeCustomer->getValue(DBECustomer::name)} contract {$dbeCustomerItem->getValue(DBECustomerItem::customerItemID)} updated!"
    );

}