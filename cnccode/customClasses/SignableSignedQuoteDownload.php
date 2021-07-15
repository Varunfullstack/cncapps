<?php

namespace CNCLTD;
global $cfg;
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");

use BUMail;
use DateTimeImmutable;
use DBECustomer;
use DBEOrdhead;
use DBEQuotation;
use DBESalesOrderDocument;
use DBEUser;
use Psr\Log\LoggerInterface;

class SignableSignedQuoteDownload implements SignableProcess
{

    public function process($signableResponseEnvelope, LoggerInterface $logger)
    {
        // we want to check if we are getting informed about a signed and complete envelope
        if ($signableResponseEnvelope['action'] !== 'signed-envelope-complete') {
            $logger->info('The envelope is not completed, ignore');
            return;
        }
        // we have to find a quotation with the given code
        $dbeQuotation = new  DBEQuotation($this);
        $dbeQuotation->setValue(DBEQuotation::signableEnvelopeID, $signableResponseEnvelope['envelope_fingerprint']);
        if (!$dbeQuotation->getRowByColumn(DBEQuotation::signableEnvelopeID)) {
            $logger->warning('Quotation not found for this envelope - stop processing');
            return;
        }
        if ($dbeQuotation->getValue(DBEQuotation::isDownloaded)) {
            $logger->warning('We have already downloaded this PDF - stop processing');
            return;
        }
        $pdfData    = Utilities::getRemoteData($signableResponseEnvelope['envelope_download']);
        $dbeOrdHead = new DBEOrdhead($this);
        $dbeOrdHead->getRow($dbeQuotation->getValue(DBEQuotation::ordheadID));
        $dbeSalesDocument = new DBESalesOrderDocument($this);
        $dbeSalesDocument->setValue(DBESalesOrderDocument::ordheadID, $dbeOrdHead->getValue(DBEOrdhead::ordheadID));
        $dbeSalesDocument->setValue(
            DBESalesOrderDocument::description,
            "Customer Order"
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
        $userToNotify = $dbeQuotation->getValue(DBEQuotation::userID);
        $dbeUser      = new DBEUser($this);
        $dbeUser->getRow($userToNotify);
        $customerID  = $dbeOrdHead->getValue(DBEOrdhead::customerID);
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $this->sendNotificationEmail($dbeUser, $dbeCustomer, $dbeQuotation);
        // we have to check if anybody else is monitoring the Sales Order this document is attached to
        global $db;
        $result = $db->preparedQuery(
            "select userId from salesOrderMonitor where salesOrderId = ?",
            [["type" => "i", "value" => $dbeOrdHead->getValue(DBEOrdhead::ordheadID)]]
        );
        while ($row = $result->fetch_assoc()) {
            if ($row['userId'] == $userToNotify) {
                continue;
            }
            $dbeUser->getRow($row['userId']);
            $this->sendNotificationEmail($dbeUser, $dbeCustomer, $dbeQuotation);
        }
        $dbeQuotation->setValue(DBEQuotation::isDownloaded, true);
        $dbeQuotation->updateRow();
    }

    function sendNotificationEmail($dbeUser, $dbeCustomer, $dbeQuotation)
    {
        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $buMail      = new BUMail($this);
        $toEmail     = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;
        // we have to check if anybody else is monitoring the Sales Order this document is attached to
        $subject = "Quote {$dbeQuotation->getValue(DBEQuotation::ordheadID)} for {$dbeCustomer->getValue(DBECustomer::name)} has been signed";
        $hdrs    = array(
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
                "url"     => SITE_URL . "/SalesOrder.php?action=displaySalesOrder&ordheadID={$dbeQuotation->getValue(DBEQuotation::ordheadID)}",
                "orderId" => $dbeQuotation->getValue(DBEQuotation::ordheadID)
            ]
        );
        $buMail->mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $thisBody    = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $thisBody
        );
    }
}