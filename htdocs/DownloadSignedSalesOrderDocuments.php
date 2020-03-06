<?php


require_once("config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
$logName = 'DownloadSignedSalesOrderDocuments';
$logger = new \CNCLTD\LoggerCLI($logName);
global $db;

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}


$thing = null;
$dbeOrdHead = new DBEOrdhead($thing);

$dbeOrdHead->getSignableNotProcessedOrders();

while ($dbeOrdHead->fetchNext()) {
    echo 'here';
    // we have to check if there are any quotations where there's a signed document
    $logger->info('Processing order ' . $dbeOrdHead->getValue(DBEOrdhead::ordheadID));

    $dbeQuote = new DBEQuotation($thing);
    $dbeQuote->getQuotesWithSignableDocumentForSalesOrder($dbeOrdHead->getValue(DBEOrdhead::ordheadID));
    $counter = 1;
    while ($dbeQuote->fetchNext()) {
        $logger->info('Processing quote ' . $dbeQuote->getValue(DBEQuotation::quotationID));
        $dbeSignableEnvelope = new DBESignableEnvelope($thing);
        $dbeSignableEnvelope->getRow($dbeQuote->getValue(DBEQuotation::signableEnvelopeID));
        if (in_array(
            $dbeSignableEnvelope->getValue(DBESignableEnvelope::status),
            ["signed-envelope", "signed-envelope-complete"]
        )) {
            // we have a signed document, so we need to download it
            $logger->info('Processing envelope ' . $dbeSignableEnvelope->getValue(DBESignableEnvelope::id));
            $pdfData = \CNCLTD\Utilities::getRemoteData(
                $dbeSignableEnvelope->getValue(DBESignableEnvelope::downloadLink)
            );

            $dbeSalesDocument = new DBESalesOrderDocument($thing);
            $dbeSalesDocument->setValue(DBESalesOrderDocument::ordheadID, $dbeOrdHead->getValue(DBEOrdhead::ordheadID));
            $dbeSalesDocument->setValue(
                DBESalesOrderDocument::description,
                "Customer Order" . ($counter > 1 ? $counter : '')
            );
            $dbeSalesDocument->setValue(
                DBESalesOrderDocument::createdDate,
                (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME)
            );
            $dbeSalesDocument->setValue(DBESalesOrderDocument::createdUserID, USER_SYSTEM);
            $dbeSalesDocument->setValue(DBESalesOrderDocument::file, $pdfData);
            $dbeSalesDocument->setValue(DBESalesOrderDocument::fileMimeType, "application/pdf");
            $dbeSalesDocument->setValue(
                DBESalesOrderDocument::filename,
                $dbeOrdHead->getValue(DBEOrdhead::ordheadID) . "_CustomerOrder.pdf"
            );

            $dbeSalesDocument->insertRow();

            //we have to send a notification to the contact that created the document
            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $userToNotify = $dbeQuote->getValue(DBEQuotation::userID);
            $dbeUser = new DBEUser($thing);
            $dbeUser->getRow($userToNotify);
            $buMail = new BUMail($thing);
            $toEmail = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

            $customerID = $dbeOrdHead->getValue(DBEOrdhead::customerID);
            $dbeCustomer = new DBECustomer($thing);
            $dbeCustomer->getRow($customerID);
            $subject = "Quote {$dbeQuote->getValue(DBEQuotation::ordheadID)} for {$dbeCustomer->getValue(DBECustomer::name)} has been signed";

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            global $twig;
            $body = $twig->render(
                '@internal/quotationSignedEmail.html.twig',
                [
                    "url"     => SITE_URL . "/SalesOrder.php?action=displaySalesOrder&ordheadID={$dbeQuote->getValue(DBEQuotation::ordheadID)}",
                    "orderId" => $dbeQuote->getValue(DBEQuotation::ordheadID)
                ]
            );
            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $thisBody = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $thisBody
            );

            $counter++;
        }

    }
    $updateOrdHead = new DBEOrdhead($thing);
    $updateOrdHead->getRow($dbeOrdHead->getValue(DBEOrdhead::ordheadID));
    $updateOrdHead->setValue(DBEOrdhead::signableProcessed, 1);
    $updateOrdHead->updateRow();
    $logger->info('Completed Processing order ' . $dbeOrdHead->getValue(DBEOrdhead::ordheadID));
}

