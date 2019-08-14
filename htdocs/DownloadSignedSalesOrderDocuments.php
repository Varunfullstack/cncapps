<?php


require_once("config.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
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
    // we have to check if there are any quotations where there's a signed document
    $logger->info('Processing order ' . $dbeOrdHead->getValue(DBEOrdhead::ordheadID));

    $dbeQuote = new DBEQuotation($thing);
    $dbeQuote->getQuotesWithSignableDocumentForSalesOrder($dbeOrdHead->getValue(DBEOrdhead::ordheadID));
    $counter = 1;
    while ($dbeQuote->fetchNext()) {
        $logger->info('Processing quote ' . $dbeQuote->getValue(DBEQuotation::quotationID));
        $dbeSignableEnvelope = new DBESignableEnvelope($thing);
        $dbeSignableEnvelope->getRow($dbeQuote->getValue(DBEQuotation::signableEnvelopeID));
        if ($dbeSignableEnvelope->getValue(DBESignableEnvelope::status) === "signed-envelope") {
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
            $counter++;
        }

    }
    $updateOrdHead = new DBEOrdhead($thing);
    $updateOrdHead->getRow($dbeOrdHead->getValue(DBEOrdhead::ordheadID));
    $updateOrdHead->setValue(DBEOrdhead::signableProcessed, 1);
    $updateOrdHead->updateRow();
    $logger->info('Completed Processing order ' . $dbeOrdHead->getValue(DBEOrdhead::ordheadID));
}

