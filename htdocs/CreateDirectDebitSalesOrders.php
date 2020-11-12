<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/08/2018
 * Time: 10:47
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg['path_bu'] . '/BUDirectDebitContracts.php');

$toEmail = "CreateRenewalSalesOrders@" . CONFIG_PUBLIC_DOMAIN;
$thing = null;
$buDirectDebitContracts = new BUDirectDebitContracts($thing);

$buDirectDebitContracts->emailRenewalsSalesOrdersDue($toEmail);

$buDirectDebitContracts->createRenewalsSalesOrders();
echo "PROCESS COMPLETED";
?>