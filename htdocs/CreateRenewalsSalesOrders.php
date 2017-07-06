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

$buRenBroadband = new BURenBroadband($this);
$buRenContract = new BURenContract($this);
$buRenQuotation = new BURenQuotation($this);
$buRenDomain = new BURenDomain($this);
$buRenHosting = new BURenHosting($this);

$buRenBroadband->emailRenewalsSalesOrdersDue();
$buRenContract->emailRenewalsSalesOrdersDue();
$buRenHosting->emailRenewalsSalesOrdersDue();
$buRenQuotation->emailRenewalsQuotationsDue();
$buRenQuotation->emailRecentlyGeneratedQuotes();
$buRenDomain->emailRenewalsSalesOrdersDue();

$buRenBroadband->createRenewalsSalesOrders();
$buRenContract->createRenewalsSalesOrders();
$buRenHosting->createRenewalsSalesOrders();
$buRenQuotation->createRenewalsQuotations();
$buRenDomain->createRenewalsSalesOrders();
echo "PROCESS COMPLETED";
?>