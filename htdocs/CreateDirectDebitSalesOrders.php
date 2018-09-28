<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/08/2018
 * Time: 10:47
 */

require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUDirectDebitContracts.php');

$toEmail = "CreateRenewalSalesOrders@cnc-ltd.co.uk";
$thing = null;
$buDirectDebitContracts = new BUDirectDebitContracts($this);

$buDirectDebitContracts->emailRenewalsSalesOrdersDue($toEmail);

$buDirectDebitContracts->createRenewalsSalesOrders();
echo "PROCESS COMPLETED";
?>