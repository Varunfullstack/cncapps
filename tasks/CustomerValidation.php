<?php
global $cfg;

use CNCLTD\CustomerValidation\CustomerValidation;
use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");

require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$logName = 'CustomerValidation';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;

require_once($cfg['path_ct'] . '/CTContact.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
global $twig;
$thing = null;
// find all the active customers

$dsCustomers = new DataSet($thing);
$buCustomer = new BUCustomer($thing);
$buCustomer->getActiveCustomers($dsCustomers, true);
$dbeContact = new DBEContact($thing);
/** @var CustomerValidation[] $customersFailingValidation */
$customersFailingValidation = [];

while ($dsCustomers->fetchNext()) {
    $customerID = $dsCustomers->getValue(DBECustomer::customerID);
    $customerValidation = new CustomerValidation(
        $dsCustomers->getValue(DBECustomer::name),
        $customerID
    );
    if ($customerValidation->hasErrors()) {
        $customersFailingValidation[] = $customerValidation;
    }
}

if (!count($customersFailingValidation)) {
    $logger->info('No Errors were found');
    return;
}

$buMail = new BUMail($thing);
$body = $twig->render('@internal/contactValidationFailedEmail.html.twig', ["customers" => $customersFailingValidation]);
$logger->notice('We found errors, sending email');
$senderEmail = "sales@" . CONFIG_PUBLIC_DOMAIN;
$toEmail = "contactvalidation@" . CONFIG_PUBLIC_DOMAIN;
$subject = "Customers with invalid contact configurations";
$buMail->sendSimpleEmail($body, $subject, $toEmail, $senderEmail);