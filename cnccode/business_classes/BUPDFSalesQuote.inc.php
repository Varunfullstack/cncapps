<?php
global $cfg;

use CNCLTD\Data\DBEItem;
use CNCLTD\Email\AttachmentCollection;

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUPDF.inc.php");
require_once($cfg["path_bu"] . "/BUItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotationLine.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");

class BUPDFSalesQuote extends Business
{
    /** @var BUSalesOrder */
    public  $buSalesOrder;
    private $footerImage;
    /**
     * @var float|int
     */
    private $footerImageRatio;
    private $footerImageWidth;
    /**
     * @var float|int
     */
    private $footerHeight;
    /**
     * @var float|int
     */
    private $footerPosition;
    /**
     * @var bool
     */
    private $shouldShowFooter;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->buSalesOrder = new BUSalesOrder($this);
    }

    /**
     * generates a PDF file and creates a database record.
     *
     * @param mixed $ordheadID
     * @param bool $salutation
     * @param bool $introduction
     * @param bool $emailSubject
     * @param DBEOrdline|DataSet $dsSelectedOrderLine
     * @return bool
     * @throws Exception
     */
    function generate($ordheadID,
                      $salutation = false,
                      $introduction = false,
                      $emailSubject = false,
                      $dsSelectedOrderLine = false
    )
    {
        $dsOrdline         = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $dsOrdhead         = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            throw new Exception('Order not found');
        }
        if (!$salutation) {
            $salutation = 'Dear ' . $dsDeliveryContact->getValue(DBEContact::firstName);
        }
        if (!$introduction) {
            $introduction = $dsOrdhead->getValue(DBEOrdhead::quotationIntroduction);
        }
        if (!$emailSubject) {
            $emailSubject = $dsOrdhead->getValue(DBEOrdhead::quotationSubject);
        }
        $versionNo = $this->buSalesOrder->getNextQuoteVersion($ordheadID);
        $quoteFile = QUOTES_DIR . $ordheadID . '_' . $versionNo . '.pdf';
        $userID    = $GLOBALS ['auth']->is_authenticated();
        $dsUser    = new DataSet($this);
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );
        $buPDF             = new BUPDF(
            $this, $quoteFile, $dsUser->getValue(DBEUser::name), $ordheadID . '/' . $versionNo, 'CNC Ltd', 'Quotation'
        );
        $this->footerImage = $GLOBALS['cfg']['cncaddress_path'];
        list($originalWidth, $originalHeight) = getimagesize($this->footerImage);
        $this->footerImageRatio = $originalHeight / $originalWidth;
        $this->footerImageWidth = $buPDF->pdf->GetPageWidth() - 10 - 10;
        $this->footerHeight     = ($this->footerImageWidth * $this->footerImageRatio) + 10;
        $this->footerPosition   = $buPDF->pdf->GetPageHeight() - $this->footerHeight;
        $this->shouldShowFooter = true;
        $buPDF->footerCallback(
            function (FPDF_Protection $pdf) {
                if ($this->shouldShowFooter) {
                    $pdf->Image(
                        $GLOBALS['cfg']['cncaddress_path'],
                        0,
                        $pdf->GetPageHeight() - ($this->footerImageWidth * $this->footerImageRatio),
                        $this->footerImageWidth
                    );
                    $this->shouldShowFooter = false;
                }
            }
        );
        // First page is quote
        $buPDF->startPage();
        $buPDF->placeImageAt(
            $GLOBALS['cfg']['cnclogo_path'],
            'PNG',
            142,
            38
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setFontFamily(BUPDF_FONT_ARIAL);
        $buPDF->setBoldItalicOn();
        $buPDF->setFontSize(8);
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFontSize(14);
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Quote: ' . $ordheadID . '/' . $versionNo);
        $buPDF->setFontSize(10);
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $buPDF->printString(
            $dsDeliveryContact->getValue(
                DBEContact::title
            ) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue(
                DBEContact::lastName
            )
        );
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::customerName));
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delAdd1));
        if ($dsOrdhead->getValue(DBEJOrdhead::delAdd2) != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delAdd2));
        }
        if ($dsOrdhead->getValue(DBEJOrdhead::delAdd3) != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delAdd3));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delTown));
        if ($dsOrdhead->getValue(DBEJOrdhead::delCounty) != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delCounty));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::delPostcode));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString($salutation . ',');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString($introduction);
        // Insert into database
        $dbeQuotation = new DBEQuotation($this);
        $dbeQuotation->setValue(DBEQuotation::versionNo, $versionNo);
        $dbeQuotation->setValue(DBEQuotation::ordheadID, $dsOrdhead->getValue(DBEJOrdhead::ordheadID));
        $dbeQuotation->setValue(DBEQuotation::userID, $userID);
        $dbeQuotation->setValue(DBEQuotation::sentDateTime, null);
        $dbeQuotation->setValue(DBEQuotation::salutation, $salutation);
        $dbeQuotation->setValue(DBEQuotation::emailSubject, $emailSubject);
        $dbeQuotation->setValue(DBEQuotation::fileExtension, 'pdf');
        $dbeQuotation->setValue(DBEQuotation::documentType, 'quote');
        $dbeQuotation->setValue(DBEQuotation::deliveryContactID, $dsOrdhead->getValue(DBEOrdhead::delContactID));
        $dbeQuotation->setValue(DBEQuotation::deliverySiteAdd1, $dsOrdhead->getValue(DBEOrdhead::delAdd1));
        $dbeQuotation->setValue(DBEQuotation::deliverySiteAdd2, $dsOrdhead->getValue(DBEOrdhead::delAdd2));
        $dbeQuotation->setValue(DBEQuotation::deliverySiteAdd3, $dsOrdhead->getValue(DBEOrdhead::delAdd3));
        $dbeQuotation->setValue(DBEQuotation::deliverySiteTown, $dsOrdhead->getValue(DBEOrdhead::delTown));
        $dbeQuotation->setValue(DBEQuotation::deliverySiteCounty, $dsOrdhead->getValue(DBEOrdhead::delCounty));
        $dbeQuotation->setValue(DBEQuotation::deliverySitePostCode, $dsOrdhead->getValue(DBEOrdhead::delPostcode));
        $confirmationCode = uniqid(null, true);
        $dbeQuotation->setValue(DBEQuotation::confirmCode, $confirmationCode);
        $dbeQuotation->insertRow();
        $quotationNextId = $dbeQuotation->getValue(DBEQuotation::quotationID);
        $oneOffLines     = [];
        $recurringLines  = [];
        while ($dsOrdline->fetchNext()) {
            if (!$dsSelectedOrderLine || !$dsSelectedOrderLine->search(
                    DBEOrdline::id,
                    $dsOrdline->getValue(DBEJOrdline::id)
                )) {
                continue;
            }
            $row = [
                DBEJOrdline::lineType              => $dsOrdline->getValue(DBEJOrdline::lineType),
                DBEJOrdline::ordheadID             => $dsOrdline->getValue(DBEJOrdline::ordheadID),
                DBEJOrdline::customerID            => $dsOrdline->getValue(DBEJOrdline::customerID),
                DBEJOrdline::itemID                => $dsOrdline->getValue(DBEJOrdline::itemID),
                DBEJOrdline::stockcat              => $dsOrdline->getValue(DBEJOrdline::stockcat),
                DBEJOrdline::description           => $dsOrdline->getValue(DBEJOrdline::description),
                DBEJOrdline::qtyOrdered            => $dsOrdline->getValue(DBEJOrdline::qtyOrdered),
                DBEJOrdline::qtyDespatched         => $dsOrdline->getValue(DBEJOrdline::qtyDespatched),
                DBEJOrdline::qtyLastDespatched     => $dsOrdline->getValue(DBEJOrdline::qtyLastDespatched),
                DBEJOrdline::supplierID            => $dsOrdline->getValue(DBEJOrdline::supplierID),
                DBEJOrdline::curUnitCost           => $dsOrdline->getValue(DBEJOrdline::curUnitCost),
                DBEJOrdline::curTotalCost          => $dsOrdline->getValue(DBEJOrdline::curTotalCost),
                DBEJOrdline::curUnitSale           => $dsOrdline->getValue(DBEJOrdline::curUnitSale),
                DBEJOrdline::curTotalSale          => $dsOrdline->getValue(DBEJOrdline::curTotalSale),
                DBEJOrdline::renewalCustomerItemID => $dsOrdline->getValue(DBEJOrdline::renewalCustomerItemID),
                DBEOrdline::isRecurring            => $dsOrdline->getValue(DBEOrdline::isRecurring),
                DBEJOrdline::itemDescription       => $dsOrdline->getValue(DBEJOrdline::itemDescription),
                DBEOrdline::sequenceNo             => $dsOrdline->getValue(DBEOrdline::sequenceNo)
            ];
            if ($dsOrdline->getValue(DBEOrdline::isRecurring)) {
                $recurringLines[] = $row;
            } else {
                $oneOffLines[] = $row;
            }
        }
        $this->renderAndSaveQuotationLines($buPDF, 'One Off', $oneOffLines, $quotationNextId);
        $this->renderAndSaveQuotationLines($buPDF, 'Ongoing', $recurringLines, $quotationNextId);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('This quotation is subject to our terms and conditions which are available ');
        $buPDF->printString('here', "https://www.cnc-ltd.co.uk/terms-and-conditions");
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('If you would like to proceed with this quote, then please click on ');
        $buPDF->printString('this link', API_URL . "/acceptQuotation?code=$confirmationCode");
        $buPDF->printString(' which will automatically email you an e-signable order form document to sign.');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Once this is received by us we will be able to process your order.');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('If you need to vary the quote in any way, please email the changes to ');
        $buPDF->printString(
            'sales@' . CONFIG_PUBLIC_DOMAIN,
            'mailto:sales@' . CONFIG_PUBLIC_DOMAIN . '?Subject=Quote%20' . $ordheadID . '/' . $versionNo
        );
        $buPDF->printString(
            ', quoting ' . $ordheadID . '/' . $versionNo . ' and we will send a revised order form to you.'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            'All prices are subject to VAT at the standard rate and are valid for 7 days from the date shown above.'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('If I can be of any further assistance please do not hesitate to contact me.');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Yours sincerely,');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('For and on behalf of');
        $buPDF->CR();
        $buPDF->printString('COMPUTER & NETWORK CONSULTANTS LTD');
        $buPDF->CR();
        $buPDF->CR();
        if ($dsUser->getValue(DBEUser::signatureFilename)) {
            $signatureHeight = 20;
            $filePath        = IMAGES_DIR . '/' . $dsUser->getValue(DBEUser::signatureFilename);
            if (!file_exists($filePath)) {
                throw new Exception('Could not find the signature file for the user in: ' . $filePath);
            }
            $buPDF->pdf->Image(
                $filePath,
                null,
                null,
                0,
                $signatureHeight
            );
        }
        $buPDF->CR();
        $buPDF->printString($dsUser->getValue(DBEUser::firstName) . ' ' . $dsUser->getValue(DBEUser::lastName));
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printString($dsUser->getValue(DBEUser::jobTitle));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('E. & O. E.');
        $buPDF->CR();
        $buPDF->endPage();
        // End of First page
        define(
            'QTY_LEFT',
            11
        );
        define(
            'QTY_WIDTH',
            28
        );
        define(
            'DETAILS_WIDTH',
            90
        );
        define(
            'UNIT_WIDTH',
            28
        );
        define(
            'TOTAL_WIDTH',
            28
        );
        define(
            'DETAILS_LEFT',
            QTY_LEFT + QTY_WIDTH
        );
        define(
            'UNIT_LEFT',
            QTY_LEFT + QTY_WIDTH + DETAILS_WIDTH
        );
        define(
            'TOTAL_LEFT',
            QTY_LEFT + QTY_WIDTH + DETAILS_WIDTH + UNIT_WIDTH
        );
        define(
            'ALL_WIDTH',
            QTY_WIDTH + DETAILS_WIDTH + UNIT_WIDTH + TOTAL_WIDTH
        );
        $buPDF->close();
        return true;
    } // end function

    private function renderAndSaveQuotationLines(BUPDF $buPDF,
                                                 string $title,
                                                 array $lines,
                                                 int $quotationNextId
    )
    {
        if (empty($lines)) {
            return;
        }
        $buItem = new BUItem($this);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printString($title . " Costs");
        $buPDF->CR();
        $buPDF->printStringRJAt(
            30,
            'Qty'
        );
        $buPDF->printStringAt(
            40,
            'Details'
        );
        $buPDF->printStringRJAt(
            150,
            'Unit'
        );
        $buPDF->printStringRJAt(
            170,
            'Total'
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $grandTotal = 0;
        foreach ($lines as $line) {

            // we have to copy the line to the quotation table
            $dbeQuotationLine = new DBEQuotationLine($this);
            $dbeQuotationLine->setValue(DBEQuotationLine::id, null);
            $dbeQuotationLine->setValue(DBEQuotationLine::quotationID, $quotationNextId);
            $dbeQuotationLine->setValue(
                DBEQuotationLine::sequenceNo,
                $line[DBEOrdline::sequenceNo]
            );
            $dbeQuotationLine->setValue(DBEQuotationLine::lineType, $line[DBEJOrdline::lineType]);
            $dbeQuotationLine->setValue(DBEQuotationLine::ordheadID, $line[DBEJOrdline::ordheadID]);
            $dbeQuotationLine->setValue(
                DBEQuotationLine::customerID,
                $line[DBEJOrdline::customerID]
            );
            $dbeQuotationLine->setValue(DBEQuotationLine::itemID, $line[DBEJOrdline::itemID]);
            $dbeQuotationLine->setValue(DBEQuotationLine::stockcat, $line[DBEJOrdline::stockcat]);
            $dbeQuotationLine->setValue(
                DBEQuotationLine::description,
                $line[DBEJOrdline::description]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::qtyOrdered,
                $line[DBEJOrdline::qtyOrdered]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::qtyDespatched,
                $line[DBEJOrdline::qtyDespatched]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::qtyLastDespatched,
                $line[DBEJOrdline::qtyLastDespatched]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::supplierID,
                $line[DBEJOrdline::supplierID]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::curUnitCost,
                $line[DBEJOrdline::curUnitCost]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::curTotalCost,
                $line[DBEJOrdline::curTotalCost]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::curUnitSale,
                $line[DBEJOrdline::curUnitSale]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::curTotalSale,
                $line[DBEJOrdline::curTotalSale]
            );
            $dbeQuotationLine->setValue(
                DBEQuotationLine::renewalCustomerItemID,
                $line[DBEJOrdline::renewalCustomerItemID]
            );
            $dbeQuotationLine->setValue(DBEQuotationLine::isRecurring, $line[DBEOrdline::isRecurring]);
            $dbeQuotationLine->insertRow();
            if ($line[DBEJOrdline::lineType] == "I") {
                if ($line[DBEJOrdline::itemDescription] != '') {
                    $buPDF->printStringAt(
                        40,
                        $line[DBEJOrdline::itemDescription]
                    );
                } else {
                    $buPDF->printStringAt(
                        40,
                        $line[DBEJOrdline::description]
                    );
                }
                if ($line[DBEJOrdline::qtyOrdered]) {
                    $buPDF->printStringRJAt(
                        30,
                        Controller::formatNumber(
                            $line[DBEJOrdline::qtyOrdered],
                            2
                        )
                    );
                }
                /*
                Do not print zero sale values
                */
                if ($line[DBEJOrdline::curUnitSale] != 0) {
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($line[DBEJOrdline::curUnitSale])
                    );
                    $total = $line[DBEJOrdline::curUnitSale] * $line[DBEJOrdline::qtyOrdered];
                    $buPDF->printStringRJAt(
                        170,
                        Controller::formatNumberCur($total)
                    );
                    $grandTotal += $total;
                }
                if ($line[DBEJOrdline::itemID]) {
                    // some item lines in old system did not have a related item record
                    $dsItem = new DataSet($this);
                    $buItem->getItemByID(
                        $line[DBEJOrdline::itemID],
                        $dsItem
                    );
                    /*
                    now that the notes are in a text field we need to split the lines up for the PDF printing
                    */
                    if ($dsItem->getValue(DBEItem::notes) != '') {
                        $buPDF->setFontSize(8);
                        $buPDF->setFont();
                        $notesArray = explode(
                            chr(13) . chr(10),
                            $dsItem->getValue(DBEItem::notes)
                        );
                        foreach ($notesArray as $noteLine) {
                            if (trim($noteLine) != '') {          // ignore blank lines
                                $buPDF->CR();
                                $buPDF->printStringAt(
                                    40,
                                    $noteLine
                                );
                            }
                        }
                        $buPDF->setFontSize(10);
                        $buPDF->setFont();
                    }
                }
            } else {
                $buPDF->printStringAt(
                    40,
                    $line[DBEJOrdline::description]
                ); // comment line
            }
            $buPDF->CR();
        }
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(
            150,
            $title . ' Total'
        );
        $buPDF->printStringRJAt(
            170,
            Controller::formatNumberCur($grandTotal)
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
    }

    /**
     * Send a reminder email about pdf quote
     * @access private
     * @param $quotationID
     * @param $emailSubject
     * @return mixed
     * @throws Exception
     */
    function sendReminderPDFEmailQuote($quotationID,
                                       $emailSubject
    )
    {
        $buMail       = new BUMail($this);
        $dbeQuotation = new DBEQuotation($this);
        if (!$dbeQuotation->getRow($quotationID)) {
            throw new Exception('Quotation Not Found');

        }
        $dsOrdhead         = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $dsOrdline         = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue(DBEQuotation::ordheadID),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            throw new Exception('Sales Order Not Found');
        }
        $userID = $GLOBALS ['auth']->is_authenticated();
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );
        $quoteFileName = "{$dbeQuotation->getValue(DBEOrdhead::ordheadID)}_{$dbeQuotation->getValue(DBEQuotation::versionNo)}.pdf";
        $quoteFile     = QUOTES_DIR . $quoteFileName;
        global $twig;
        $DBEJRenQuotation = new DBEJRenQuotation($this);
        $DBEJRenQuotation->getRowsBySalesOrderID($dsOrdhead->getValue(DBEOrdhead::ordheadID));
        if (!$DBEJRenQuotation->rowCount()) {
            return false;
        }
        $DBEJRenQuotation->fetchNext();
        $contactFirstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $renewalType      = $DBEJRenQuotation->getValue(DBEJRenQuotation::type);
        $body             = $twig->render(
            '@customerFacing/QuoteReminder/QuoteReminder.html.twig',
            [
                "contactFirstName" => $contactFirstName,
                "renewalType"      => $renewalType
            ]
        );
        $recipientsArray  = [
            $dsOrdhead->getValue(DBEOrdhead::delContactEmail),
            CONFIG_SALES_EMAIL
        ];
        $attachments      = new AttachmentCollection();
        $attachments->add(
            $quoteFile,
            'application/pdf',
            $quoteFileName,
            true
        );
        $buMail->sendEmailWithAttachments(
            $body,
            $emailSubject,
            implode(',', $recipientsArray),
            $attachments,
            CONFIG_SALES_EMAIL
        );
    }

    /**
     * send a PDF quote.
     * @access private
     * @param $quotationID
     * @return mixed
     * @throws Exception
     */
    function sendPDFEmailQuote($quotationID)
    {
        $buMail       = new BUMail($this);
        $dbeQuotation = new DBEQuotation($this);
        if (!$dbeQuotation->getRow($quotationID)) {
            throw new Exception('Quotation Not Found');

        }
        $dsOrdhead         = new DataSet($this);
        $dsOrdline         = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue(DBEQuotation::ordheadID),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            throw new Exception('Sales Order Not Found');
        }
        $userID = $GLOBALS ['auth']->is_authenticated();
        $dsUser = new DataSet($this);
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );
        $quoteFile   = QUOTES_DIR . $dbeQuotation->getValue(DBEQuotation::ordheadID) . '_' . $dbeQuotation->getValue(
                DBEQuotation::versionNo
            ) . '.pdf';
        $body        = $dbeQuotation->getValue(DBEQuotation::documentType) . ' ' . $dsOrdhead->getValue(
                DBEJOrdhead::ordheadID
            ) . '/' . $dbeQuotation->getValue(DBEQuotation::versionNo) . ' for ' . $dbeQuotation->getValue(
                DBEQuotation::emailSubject
            );
        $subject     = ucwords($body);
        $senderEmail = $dsUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;
        $senderName  = $dsUser->getValue(DBEUser::firstName) . ' ' . $dsUser->getValue(DBEUser::lastName);
        global $twig;
        $apiURL  = API_URL . "/acceptQuotation?code={$dbeQuotation->getValue(DBEQuotation::confirmCode)}";
        $toEmail = $dsOrdhead->getValue(DBEJOrdhead::delContactEmail);
        $hdrs    = array(
            'From'         => $senderName . '<' . $senderEmail . '>',
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $buMail->mime->setHTMLBody(
            $twig->render(
                '@customerFacing/Quote/Quote.html.twig',
                [
                    "isOrderForm"     => $dbeQuotation->getValue(DBEQuotation::documentType) == 'order form',
                    "apiURL"          => $apiURL,
                    "salutation"      => $dbeQuotation->getValue(DBEQuotation::salutation),
                    "senderFirstName" => $dsUser->getValue(DBEUser::firstName),
                    "senderLastName"  => $dsUser->getValue(DBEUser::lastName),
                    "introduction"    => $dbeQuotation->getValue(DBEQuotation::salutation)
                ]
            )
        );
        $buMail->mime->addAttachment(
            $quoteFile,
            'application/pdf'
        );
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $toEmail     .= ',' . CONFIG_SALES_EMAIL;
        return $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

}