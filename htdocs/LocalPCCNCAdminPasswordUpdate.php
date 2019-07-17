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
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require './../vendor/autoload.php';
global $db;

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}


// Script example.php
$shortopts = "c:";  // Required value
$longopts = array(
    "customer::",     // Required value
);
$options = getopt($shortopts, $longopts);
$customerID = null;
if (isset($options['c'])) {
    $customerID = $options['c'];
}
if (isset($options['customer'])) {
    $customerID = $options['customer'];
}

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
$query = "SELECT ExternalID as customerID,  e.`localpccncadmin Password` as password FROM clients LEFT JOIN v_extradataclients e ON e.`clientid` = clients.`ClientID` WHERE e.`localpccncadmin Password` <> ''";

$statement = $labtechDB->query($query);
if (!$statement) {
    cli_echo('Something went wrong...' . implode(',', $labtechDB->errorInfo()) . ", Query: $query", 'error');
    exit;
}
$result = $statement->execute();
if (!$result) {
    cli_echo('Something went wrong...' . implode(',', $statement->errorInfo()) . ", Query: $query", 'error');
    exit;
}
$data = $statement->fetchAll(PDO::FETCH_ASSOC);
$buPassword = new BUPassword($thing);

$thing = null;
$systemUser = new DBEUser($thing);
$systemUser->getRow(67);
foreach ($data as $datum) {
    cli_echo('Pulling CNC password for customer: ' . $datum['customerID'], 'info');
    $dbePassword = new DBEPassword($thing);
    try {
        $dbePassword->getLocalPCCNCAdminPasswordByCustomerID($datum['customerID']);
        if (!$dbePassword->rowCount()) {
            cli_echo('The Local PC CNC Admin Password Item does not exist, create it!', 'info');
            $dbePassword->setValue(DBEPassword::customerID, $datum['customerID']);
            $dbePassword->setValue(DBEPassword::serviceID, 24);
            $dbePassword->setValue(DBEPassword::level, 1);
            $dbePassword->setValue(DBEPassword::username, $buPassword->encrypt('cncadmin'));
            $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($datum['password']));
            $dbePassword->insertRow();
            cli_echo('Inserted new Local PC CNC Admin Password Item!', 'success');
            continue;
        }
        cli_echo('The Local PC CNC Admin Password Item does exist, compare it!', 'info');
        $currentPassword = $dbePassword->getValue(DBEPassword::password);
        if (!$currentPassword || $buPassword->decrypt($currentPassword) != $datum['password']) {
            cli_echo('The passwords are different, update it!', 'info');

            $buPassword->archive($dbePassword->getValue(DBEPassword::passwordID), $systemUser);
            $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($datum['password']));
            $dbePassword->setValue(DBEPassword::passwordID, null);
            $dbePassword->insertRow();
            cli_echo('The password has been updated', 'success');
            continue;
        }
        cli_echo('The passwords match, do nothing', 'success');
    } catch (\Exception $exception) {
        cli_echo('Failed to pull CNC password for customer', 'warning');
    }
}
