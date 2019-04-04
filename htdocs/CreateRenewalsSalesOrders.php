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
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenContract.inc.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
$thing = null;
$buRenBroadband = new BURenBroadband($thing);
$buRenContract = new BURenContract($thing);
$buRenQuotation = new BURenQuotation($thing);
$buRenDomain = new BURenDomain($thing);
$buRenHosting = new BURenHosting($thing);

$toEmail = "CreateRenewalSalesOrders@cnc-ltd.co.uk";

$buRenBroadband->emailRenewalsSalesOrdersDue($toEmail);
$buRenContract->emailRenewalsSalesOrdersDue($toEmail);
$buRenHosting->emailRenewalsSalesOrdersDue($toEmail);
$buRenQuotation->emailRenewalsQuotationsDue($toEmail);
$buRenQuotation->emailRecentlyGeneratedQuotes($toEmail);
$buRenDomain->emailRenewalsSalesOrdersDue($toEmail);

$buRenBroadband->createRenewalsSalesOrders();
$buRenContract->createRenewalsSalesOrders();
$buRenHosting->createRenewalsSalesOrders();
$buRenQuotation->createRenewalsQuotations();
//$buRenDomain->createRenewalsSalesOrders();
echo "PROCESS COMPLETED";
?>