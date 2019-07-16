<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

require_once("config.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require './../vendor/autoload.php';
global $db;

//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn,
    LABTECH_DB_USERNAME,
    LABTECH_DB_PASSWORD,
    $options
);
/** @lang MySQL */
$query = "SELECT ExternalID as customerID, e.`localpccncadmin Password` as password FROM clients LEFT JOIN v_extradataclients e ON e.`clientid` = clients.`ClientID` WHERE e.`localpccncadmin Password` <> ''";

$statement = $labtechDB->query($query);
$test = $statement->execute();
if (!$test) {
    echo '<div>Something went wrong...' . implode(
            ',',
            $statement->errorInfo()
        );
    var_dump($query);
    echo ' </div>';
}
$data = $statement->fetchAll(PDO::FETCH_ASSOC);

var_dump($data);