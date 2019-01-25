<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/01/2019
 * Time: 11:04
 */

require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
global $db;

$thing = null;

$buCustomer = new BUCustomer($thing);

$dsCustomers = new DataSet($thing);

$buCustomer->getActiveCustomers($dsCustomers);


while ($dsCustomers->fetchNext()) {
    $buCustomer->createCustomerFolder($dsCustomers->getValue(DBECustomer::customerID));
}


