<?php
use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'LocalPCCNCAdminPasswordUpdate';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');

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
    $logger->error('Something went wrong...' . implode(',', $labtechDB->errorInfo()) . ", Query: $query");
    exit;
}
$result = $statement->execute();
if (!$result) {
    $logger->error('Something went wrong...' . implode(',', $statement->errorInfo()) . ", Query: $query");
    exit;
}
$data = $statement->fetchAll(PDO::FETCH_ASSOC);
$buPassword = new BUPassword($thing);

$thing = null;
$systemUser = new DBEUser($thing);
$systemUser->getRow(67);
foreach ($data as $datum) {
    $logger->notice('Pulling CNC password for customer: ' . $datum['customerID']);
    $dbePassword = new DBEPassword($thing);
    try {
        $dbePassword->getLocalPCCNCAdminPasswordByCustomerID($datum['customerID']);
        if (!$dbePassword->rowCount()) {
            $logger->notice('The Local PC CNC Admin Password Item does not exist, create it!');
            $dbePassword->setValue(DBEPassword::customerID, $datum['customerID']);
            $dbePassword->setValue(DBEPassword::serviceID, 24);
            $dbePassword->setValue(DBEPassword::level, 1);
            $dbePassword->setValue(DBEPassword::username, $buPassword->encrypt('localpccncadmin'));
            $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($datum['password']));
            $dbePassword->insertRow();
            $logger->info('Inserted new Local PC CNC Admin Password Item!');
            continue;
        }
        $logger->notice('The Local PC CNC Admin Password Item does exist, compare it!');
        $currentPassword = $dbePassword->getValue(DBEPassword::password);
        if (!$currentPassword || $buPassword->decrypt($currentPassword) != $datum['password']) {
            $logger->notice('The passwords are different, update it!');

            $buPassword->archive($dbePassword->getValue(DBEPassword::passwordID), $systemUser);
            $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($datum['password']));
            $dbePassword->setValue(DBEPassword::passwordID, null);
            $dbePassword->insertRow();
            $logger->info('The password has been updated');
            continue;
        }
        $logger->notice('The passwords match, do nothing');
    } catch (Exception $exception) {
        $logger->warning('Failed to pull CNC password for customer');
    }
}
