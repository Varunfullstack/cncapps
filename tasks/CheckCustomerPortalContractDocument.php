<?php


use CNCLTD\LoggerCLI;
use Twig\Environment;

global $cfg;
require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
global $db;
$logName = 'CheckAutoApproveExpensesOvertime';
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
$dsCustomers = new DataSet($thing);
$buCustomer = new BUCustomer($thing);
$buCustomer->getActiveCustomers($dsCustomers);
$failedCustomers = [];

class PortalContractDocumentCustomer
{
    public $link;
    public $name;

    /**
     * PortalContractDocumentCustomer constructor.
     * @param $link
     * @param $name
     */
    public function __construct($link, $name)
    {
        $this->link = $link;
        $this->name = $name;
    }

}

$buPortalDocument = new BUPortalCustomerDocument($thing);
while ($dsCustomers->fetchNext()) {
    if (!$buPortalDocument->hasContractDocumentByCustomerId($dsCustomers->getValue(DBECustomer::customerID))) {
        $link = SITE_URL . "/Customer.php?action=dispEdit&customerID=" . $dsCustomers->getValue(
                DBECustomer::customerID
            );
        $failedCustomers[] = new PortalContractDocumentCustomer(
            $link,
            $dsCustomers->getValue(DBECustomer::name)
        );
    }
}

if (count($failedCustomers)) {
    $buMail = new BUMail($thing);
    $senderEmail = CONFIG_SALES_EMAIL;

    /** @var $twig Environment */
    global $twig;
    $html = $twig->render(
        '@internal/customerWithoutContractDocumentEmail.html.twig',
        ["customers" => $failedCustomers]
    );

    $hdrs =
        array(
            'From'         => $senderEmail,
            'To'           => $senderEmail,
            'Subject'      => 'Customers without signed contracts',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

    $buMail->mime->setHTMLBody($html);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $body = $buMail->mime->get($mime_params);

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->putInQueue(
        $senderEmail,
        $senderEmail,
        $hdrs,
        $body

    );
}