<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/08/2018
 * Time: 10:47
 */

require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenContract.inc.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');

$buRenBroadband = new BURenBroadband($this);
$buRenContract = new BURenContract($this);
$buRenHosting = new BURenHosting($this);

$toEmail = "CreateRenewalSalesOrders@cnc-ltd.co.uk";

$buRenBroadband->emailRenewalsSalesOrdersDue($toEmail, true);
$buRenContract->emailRenewalsSalesOrdersDue($toEmail, true);
$buRenHosting->emailRenewalsSalesOrdersDue($toEmail, true);

$buRenBroadband->createRenewalsSalesOrders(true);
$buRenContract->createRenewalsSalesOrders(true);
$buRenHosting->createRenewalsSalesOrders(true);
echo "PROCESS COMPLETED";
?>