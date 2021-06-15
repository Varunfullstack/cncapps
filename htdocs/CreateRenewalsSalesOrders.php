<?php
/**
 * Renewals sales order creation front end controller
 * CNC Ltd
 *
 * Generates renewals
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BURenContract;

require_once("config.inc.php");
global $cfg;
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/Burencontract.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
$thing = null;
$buRenBroadband = new BURenBroadband($thing);
$buRenContract = new BURenContract($thing);
$buRenQuotation = new BURenQuotation($thing);
$buRenDomain = new BURenDomain($thing);
$buRenHosting = new BURenHosting($thing);
$itemBillingCategory = null;
if (isset($_REQUEST['itemBillingCategory'])) {
    $itemBillingCategory = $_REQUEST['itemBillingCategory'];
}
$toEmail = "CreateRenewalSalesOrders@" . CONFIG_PUBLIC_DOMAIN;

if (!$itemBillingCategory) {
    $buRenBroadband->emailRenewalsSalesOrdersDue($toEmail);
    $buRenHosting->emailRenewalsSalesOrdersDue($toEmail);
    $buRenQuotation->emailRenewalsQuotationsDue($toEmail);
    $buRenQuotation->emailRecentlyGeneratedQuotes($toEmail);
    $buRenDomain->emailRenewalsSalesOrdersDue($toEmail);
}
$buRenContract->emailRenewalsSalesOrdersDue($toEmail, $itemBillingCategory);

if (!$itemBillingCategory) {
    $buRenBroadband->createRenewalsSalesOrders();
    $buRenHosting->createRenewalsSalesOrders();
    $buRenQuotation->createRenewalsQuotations();
    $buRenDomain->createRenewalsSalesOrders();
}
$buRenContract->createRenewalsSalesOrders($itemBillingCategory);
echo "PROCESS COMPLETED";
?>