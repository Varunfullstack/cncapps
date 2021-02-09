<?php
use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'DailySalesRequestEmail';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [
    "toScreen"
];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUItemsNotYetReceived.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
$outputToScreen = isset($options['toScreen']);
$thing = null;
$buItemsNotYetReceived = new BUItemsNotYetReceived($thing);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";


$result = $buItemsNotYetReceived->getItemsNotYetReceived();

usort(
    $result,
    function (\CNCLTD\ItemNotYetReceived $a,
              \CNCLTD\ItemNotYetReceived $b
    ) {

        if ($a->getCustomerName() > $b->getCustomerName()) {
            return 1;
        }

        if ($a->getCustomerName() < $b->getCustomerName()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() > $b->getPurchaseOrderRequiredBy()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() < $b->getPurchaseOrderRequiredBy()) {
            return 1;
        }

        return 0;
    }
);

global $twig;
$body = $twig->render('@internal/itemsNotYetReceivedEmail.html.twig', ["itemsNotYetReceived" => $result, "pageDomain" => SITE_URL,"salesOrdersWithoutSRs" => $buItemsNotYetReceived->getOrdersWithoutSR()]);

if (!$outputToScreen) {

    $buMail = new BUMail($thing);

    $toEmail = 'unreceivedpo@' . CONFIG_PUBLIC_DOMAIN;
    $subject = 'Purchase Order Status Report';
    $fromEmail = CONFIG_SALES_EMAIL;

    $buMail->sendSimpleEmail($body, $subject, $toEmail, $fromEmail);
} else {
    echo $body;
}
?>