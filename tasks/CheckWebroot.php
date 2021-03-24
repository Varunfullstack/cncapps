<?php

use CNCLTD\LoggerCLI;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
global $db;
$logName = 'CheckWebroot';
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
$thing         = null;
$user          = "cncappsapi@" . CONFIG_PUBLIC_DOMAIN;
$password      = '3uGhNEBW6dsAHd6q';
$client_Id     = 'client_e2maZ8d5@' . CONFIG_PUBLIC_DOMAIN;
$client_secret = '{1!XM^QJcqvM8qj';
$gsmKey        = "2FB2-LTSW-E06B-3F49-43DC";
// we are going to ask for a new access token
$webrootAPI = new \CNCLTD\WebrootAPI\WebrootAPI($user, $password, $client_Id, $client_secret, $gsmKey, $logger);
$sitesResponse = $webrootAPI->getSites();
$buActivity    = new BUActivity($thing);
foreach ($sitesResponse->sites as $site) {
    // we have to find each client based on the site name
    $dbeCustomer = new DBECustomer($thing);
    $dbeCustomer->getCustomerByName($site->siteName);
    if (!$dbeCustomer->rowCount()) {
        $buActivity->raiseWebrootCustomerNotMatchedSR($site);
        $logger->warning("No match found for site with name '{$site->siteName}'");
        continue;
    }
    $dbeCustomer->fetchNext();
    $dbeCustomerItem = new DBECustomerItem($thing);
    $dbeCustomerItem->getRowsByCustomerAndItemID(
        $dbeCustomer->getValue(DBECustomer::customerID),
        CONFIG_WEBROOT_ITEMTYPEID,
        true
    );
    if (!$dbeCustomerItem->fetchNext()) {
        try {
            $buActivity->raiseWebrootContractNotFound($site, $dbeCustomer);
            $logger->warning("No Contract found for customer: {$dbeCustomer->getValue(DBECustomer::name)}");
        } catch (Exception $exception) {
            $logger->error($exception);
        }
        continue;
    }
    $contractId = $dbeCustomerItem->getValue(DBECustomerItem::customerItemID);
    $dbeCustomerItem->getRow($contractId);
    $dbeCustomerItem->setValue(DBECustomerItem::users, $site->totalEndpoints);
    $dbeCustomerItem->setValue(
        DBECustomerItem::curUnitSale,
        $site->totalEndpoints * 12 * $dbeCustomerItem->getValue(
            DBECustomerItem::salePricePerMonth
        )
    );
    $dbeCustomerItem->setValue(
        DBECustomerItem::curUnitCost,
        $site->totalEndpoints * 12 * $dbeCustomerItem->getValue(
            DBECustomerItem::costPricePerMonth
        )
    );
    $dbeCustomerItem->updateRow();
    $logger->info(
        "Customer {$dbeCustomer->getValue(DBECustomer::name)} contract {$dbeCustomerItem->getValue(DBECustomerItem::customerItemID)} updated!"
    );
}