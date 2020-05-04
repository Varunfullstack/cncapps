<?php
require_once("config.inc.php");
global $cfg;
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

$customersFailingValidation = [];

while ($dsCustomers->fetchNext()) {
    $customerID = $dsCustomers->getValue(DBECustomer::customerID);
    $customerValidation = new \CNCLTD\CustomerValidation\CustomerValidation(
        $dsCustomers->getValue(DBECustomer::name),
        $customerID
    );
    if ($customerValidation->hasErrors()) {
        $customersFailingValidation[] = $customerValidation;
    }
}

if (!count($customersFailingValidation)) {
    echo 'No Errors were found';
    return;
}

$buMail = new BUMail($thing);
$body = $twig->render('@internal/contactValidationFailedEmail.html.twig', ["customers" => $customersFailingValidation]);
echo $body;
$senderEmail = "sales@cnc-ltd.co.uk";
$toEmail = "contactvalidation@cnc-ltd.co.uk";
$subject = "Customers with invalid contact configurations";
$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => $subject,
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);

$buMail->mime->setHTMLBody($body);

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
    $toEmail,
    $hdrs,
    $body
);