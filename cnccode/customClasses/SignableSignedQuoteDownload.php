<?php


namespace CNCLTD;

global $cfg;
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
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

        $pdfData = \CNCLTD\Utilities::getRemoteData($signableResponseEnvelope['envelope_download']);

        // we have to find a quotation with the given code
        $dbeQuotation = new  \DBEQuotation($this);
        $dbeQuotation->setValue(\DBEQuotation::signableEnvelopeID, $signableResponseEnvelope['envelope_fingerprint']);

        if (!$dbeQuotation->getRowByColumn(\DBEQuotation::signableEnvelopeID)) {
            $logger->warning('Quotation not found for this envelope - stop processing');
            return;
        }

        $dbeOrdHead = new \DBEOrdhead($this);
        $dbeOrdHead->getRow($dbeQuotation->getValue(\DBEQuotation::ordheadID));

        $dbeSalesDocument = new \DBESalesOrderDocument($this);
        $dbeSalesDocument->setValue(\DBESalesOrderDocument::ordheadID, $dbeOrdHead->getValue(\DBEOrdhead::ordheadID));
        $dbeSalesDocument->setValue(
            \DBESalesOrderDocument::description,
            "Customer Order"
        );
        $dbeSalesDocument->setValue(
            \DBESalesOrderDocument::createdDate,
            (new \DateTimeImmutable())->format(DATE_MYSQL_DATETIME)
        );
        $dbeSalesDocument->setValue(\DBESalesOrderDocument::createdUserID, USER_SYSTEM);
        $dbeSalesDocument->setValue(\DBESalesOrderDocument::file, $pdfData);
        $dbeSalesDocument->setValue(\DBESalesOrderDocument::fileMimeType, "application/pdf");
        $dbeSalesDocument->setValue(
            \DBESalesOrderDocument::filename,
            $dbeOrdHead->getValue(\DBEOrdhead::ordheadID) . "_CustomerOrder.pdf"
        );

        $dbeSalesDocument->insertRow();

        //we have to send a notification to the contact that created the document
        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $userToNotify = $dbeQuotation->getValue(\DBEQuotation::userID);
        $dbeUser = new \DBEUser($this);
        $dbeUser->getRow($userToNotify);
        $buMail = new \BUMail($this);
        $toEmail = $dbeUser->getValue(\DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $customerID = $dbeOrdHead->getValue(\DBEOrdhead::customerID);
        $dbeCustomer = new \DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $subject = "Quote {$dbeQuotation->getValue(\DBEQuotation::ordheadID)} for {$dbeCustomer->getValue(\DBECustomer::name)} has been signed";

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
                "url"     => SITE_URL . "/SalesOrder.php?action=displaySalesOrder&ordheadID={$dbeQuotation->getValue(\DBEQuotation::ordheadID)}",
                "orderId" => $dbeQuotation->getValue(\DBEQuotation::ordheadID)
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
    }
}