<?php
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUPDF.inc.php");
require_once($cfg["path_bu"] . "/BUItem.inc.php");

class BUPDFSalesQuote extends Business
{

    var $buSalesOrder = '';

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
     * @param mixed $dsSelectedOrderLine
     * @return bool
     * @throws Exception
     */
    function generate(
        $ordheadID,
        $salutation = false,
        $introduction = false,
        $emailSubject = false,
        $dsSelectedOrderLine = false
    )
    {
        $dsOrdline = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
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

        $quoteFile = 'quotes/' . $ordheadID . '_' . $versionNo . '.pdf';


        $buItem = new BUItem($this);

        $userID = $GLOBALS ['auth']->is_authenticated();
        $dsUser = new DataSet($this);
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );

        $buPDF = new BUPDF(
            $this,
            $quoteFile,
            $dsUser->getValue(DBEUser::name),
            $ordheadID . '/' . $versionNo,
            'CNC Ltd',
            'Quotation',
            'A4'
        );
        // First page is quote
        $buPDF->startPage();

        $buPDF->placeImageAt(
            $GLOBALS['cfg']['cnclogo_path'],
            'PNG',
            142,
            38
        );
        //$buPDF->placeImageAt( $GLOBALS['cfg']['btlogo_path'], 'JPEG', 15, 40);
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
        $buPDF->printString('Quotation: ' . $ordheadID . '/' . $versionNo);
        $buPDF->setFontSize(10);
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $buPDF->printString(
            $dsDeliveryContact->getValue(DBEContact::title) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue(
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
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
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
        while ($dsOrdline->fetchNext()) {
            if (
                $dsSelectedOrderLine &&
                $dsSelectedOrderLine->search(
                    'sequenceNo',
                    $dsOrdline->getValue(DBEJOrdline::sequenceNo)
                )
            ) {
                if ($dsOrdline->getValue(DBEJOrdline::lineType) == "I") {
                    if ($dsOrdline->getValue(DBEJOrdline::itemDescription) != '') {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEJOrdline::itemDescription)
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEJOrdline::description)
                        );
                    }
                    $buPDF->printStringRJAt(
                        30,
                        Controller::formatNumber(
                            $dsOrdline->getValue(DBEJOrdline::qtyOrdered),
                            2
                        )
                    );
                    /*
                    Do not print zero sale values
                    */
                    if ($dsOrdline->getValue(DBEJOrdline::curUnitSale) != 0) {
                        $buPDF->printStringRJAt(
                            150,
                            Controller::formatNumberCur($dsOrdline->getValue(DBEJOrdline::curUnitSale))
                        );
                        $total = ($dsOrdline->getValue(DBEJOrdline::curUnitSale) * $dsOrdline->getValue(
                                DBEJOrdline::qtyOrdered
                            ));
                        $buPDF->printStringRJAt(
                            170,
                            Controller::formatNumberCur($total)
                        );
                        $grandTotal += $total;
                    }
                    if ($dsOrdline->getValue(
                            DBEJOrdline::itemID
                        ) != 0) {            // some item lines in old system did not have a related item record
                        $dsItem = new DataSet($this);
                        $buItem->getItemByID(
                            $dsOrdline->getValue(DBEJOrdline::itemID),
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
                        $dsOrdline->getValue(DBEJOrdline::description)
                    ); // comment line
                }
                $buPDF->CR();
            }
        }
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(
            150,
            'Grand Total'
        );
        $buPDF->printStringRJAt(
            170,
            Controller::formatNumberCur($grandTotal)
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            'If you would like to proceed with this quotation then please forward your written (fax or e-mail) order to us at your earliest convenience.'
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
            $filePath = IMAGES_DIR . '/' . $dsUser->getValue(DBEUser::signatureFilename);
            if (!file_exists($filePath)) {
                throw new Exception('Could not find the signature file for the user in: ' . $filePath);
            }
            $buPDF->placeImageAt(
                $filePath,
                'PNG',
                10,
                35
            );
        }
        $buPDF->CR();
        $buPDF->CR();
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
        $buPDF->placeImageAt(
            $GLOBALS['cfg']['cncaddress_path'],
            'PNG',
            6,
            200
        );
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
            QTY_LEFT +
            QTY_WIDTH
        );
        define(
            'UNIT_LEFT',
            QTY_LEFT +
            QTY_WIDTH +
            DETAILS_WIDTH
        );
        define(
            'TOTAL_LEFT',
            QTY_LEFT +
            QTY_WIDTH +
            DETAILS_WIDTH +
            UNIT_WIDTH
        );
        define(
            'ALL_WIDTH',
            QTY_WIDTH +
            DETAILS_WIDTH +
            UNIT_WIDTH +
            TOTAL_WIDTH
        );

        // Second page is Order Form
        $buPDF->startPage();
        $buPDF->setBoldOff();
        $buPDF->setFontSize(10);
        $buPDF->setFontFamily(BUPDF_FONT_ARIAL);
        $buPDF->setFont();
        $buPDF->printStringAt(
            110,
            'From:'
        );
        $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $buPDF->printStringAt(
            130,
            $dsDeliveryContact->getValue(
                DBEContact::title
            ) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue(DBEContact::lastName)
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEJOrdhead::customerName)
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEJOrdhead::delAdd1)
        );
        if ($dsOrdhead->getValue(DBEJOrdhead::delAdd2) != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEJOrdhead::delAdd2)
            );
        }
        if ($dsOrdhead->getValue(DBEJOrdhead::delAdd3) != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEJOrdhead::delAdd3)
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEJOrdhead::delTown)
        );
        if ($dsOrdhead->getValue(DBEJOrdhead::delCounty) != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEJOrdhead::delCounty)
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEJOrdhead::delPostcode)
        );
        $buPDF->CR();
        $buPDF->printString($dsUser->getValue(DBEUser::firstName) . ' ' . $dsUser->getValue(DBEUser::lastName));
        $buPDF->CR();
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $buPDF->printString($dsHeader->getValue(DBEHeader::name));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add1));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add2));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add3));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::town));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::county));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::postcode));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Dear ' . $dsUser->getValue(DBEUser::firstName) . ',');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            'With reference to your recent quotation ' . $ordheadID . '/' . $versionNo .
            ', please accept this as confirmation that we wish to proceed and order the following goods/services:'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $boxTop = $buPDF->getYPos();
        $buPDF->printStringRJAt(
            28,
            'Qty'
        );
        $buPDF->box(
            QTY_LEFT,
            $boxTop,
            ALL_WIDTH,
            $buPDF->getFontSize() / 2
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
            173,
            'Total'
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $dsOrdline->initialise();
        while ($dsOrdline->fetchNext()) {
            if (
                $dsSelectedOrderLine &&
                $dsSelectedOrderLine->search(
                    'sequenceNo',
                    $dsOrdline->getValue(DBEJOrdline::sequenceNo)
                )
            ) {
                if ($dsOrdline->getValue(DBEJOrdline::lineType) == "I") {
                    if ($dsOrdline->getValue(DBEJOrdline::itemDescription) != '') {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEJOrdline::itemDescription)
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEJOrdline::description)
                        );
                    }
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue(DBEJOrdline::curUnitSale))
                    );
                    if ($dsOrdline->getValue(
                            DBEJOrdline::itemID
                        ) != 0) {            // some item lines in old system did not have a related item record
                        $buItem->getItemByID(
                            $dsOrdline->getValue(DBEJOrdline::itemID),
                            $dsItem
                        );
                    }
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue(DBEJOrdline::description)
                    ); // comment line
                }
                $buPDF->box(
                    QTY_LEFT,
                    $buPDF->getYPos(),
                    ALL_WIDTH,
                    $buPDF->getFontSize() / 2
                );
                $buPDF->CR();
            }
        }
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringAt(
            UNIT_LEFT,
            'Grand total'
        ); // comment line

        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Our official order no:'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Name:'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Signed:'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Date:'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Position:'
        );
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->printString('All prices are subject to VAT at the standard rate.');
        $buPDF->setBoldOff();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Please return a signed copy to sales@cnc-ltd.co.uk');
        $buPDF->endPage();
        // End of second page
        $buPDF->close();

        // Insert into database
        $dsQuotation = new DataSet($this);
        $dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $dsQuotation->setUpdateModeInsert();
        $dsQuotation->setValue(
            DBEQuotation::versionNo,
            $versionNo
        );
        $dsQuotation->setValue(
            DBEQuotation::ordheadID,
            $dsOrdhead->getValue(DBEJOrdhead::ordheadID)
        );
        $dsQuotation->setValue(
            DBEQuotation::userID,
            $userID
        );
        $dsQuotation->setValue(
            DBEQuotation::sentDateTime,
            null
        );
        $dsQuotation->setValue(
            DBEQuotation::salutation,
            $salutation
        );
        $dsQuotation->setValue(
            DBEQuotation::emailSubject,
            $emailSubject
        );
        $dsQuotation->setValue(
            DBEQuotation::fileExtension,
            'pdf'
        );
        $dsQuotation->setValue(
            DBEQuotation::documentType,
            'quotation'
        );
        $dsQuotation->post();
        return $this->buSalesOrder->insertQuotation($dsQuotation);

    } // end function


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
        $buMail = new BUMail($this);

        $dbeQuotation = new DBEQuotation($this);

        if (!$dbeQuotation->getRow($quotationID)) {
            throw new Exception('Quotation Not Found');

        }
        $dsOrdhead = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $dsOrdline = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue(DBEQuotation::ordheadID),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
            throw new Exception('Sales Order Not Found');
        }

        $userID = $GLOBALS ['auth']->is_authenticated();

        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );

        $quoteFile = 'quotes/' . $dbeQuotation->getValue(DBEOrdhead::ordheadID) . '_' . $dbeQuotation->getValue(
                DBEQuotation::versionNo
            ) . '.pdf';

        $subject = $emailSubject;

        $template = new Template (
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'QuoteReminderEmail.html'
        );

        $DBEJRenQuotation = new DBEJRenQuotation($this);
        $DBEJRenQuotation->setShowSQLOn();
        $DBEJRenQuotation->getRowsBySalesOrderID($dsOrdhead->getValue(DBEOrdhead::ordheadID));

        if (!$DBEJRenQuotation->rowCount()) {
            return false;
        }

        $DBEJRenQuotation->fetchNext();

        $sentDateValue = $dbeQuotation->getValue(DBEQuotation::sentDateTime);
        $sentDate = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $sentDateValue
        );

        $template->set_var(
            [
                'contactFirstName' => $dsDeliveryContact->getValue(DBEContact::firstName),
                'renewalType'      => $DBEJRenQuotation->getValue(DBEJRenQuotation::type),
                'sentDate'         => $sentDate->format('d/m/Y')
            ]
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');
        $toEmail = $dsOrdhead->getValue(DBEOrdhead::delContactEmail);
        $senderEmail = "sales@cnc-ltd.co.uk";

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

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
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $toEmail .= ',' . CONFIG_SALES_EMAIL;

        return $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
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
        $buMail = new BUMail($this);

        $dbeQuotation = new DBEQuotation($this);

        if (!$dbeQuotation->getRow($quotationID)) {
            throw new Exception('Quotation Not Found');

        }
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue(DBEQuotation::ordheadID),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
            throw new Exception('Sales Order Not Found');
        }

        $userID = $GLOBALS ['auth']->is_authenticated();
        $dsUser = new DataSet($this);
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );

        $quoteFile = 'quotes/' . $dbeQuotation->getValue(DBEQuotation::ordheadID) . '_' . $dbeQuotation->getValue(
                DBEQuotation::versionNo
            ) . '.pdf';

        $body = $dbeQuotation->getValue(DBEQuotation::documentType) . ' ' . $dsOrdhead->getValue(
                DBEJOrdhead::ordheadID
            ) . '/' . $dbeQuotation->getValue(DBEQuotation::versionNo) . ' for ' . $dbeQuotation->getValue(
                DBEQuotation::emailSubject
            );

        $subject = ucwords($body);

        $senderEmail = $dsUser->getValue(DBEUser::username) . '@cnc-ltd.co.uk';
        $senderName = $dsUser->getValue(DBEUser::firstName) . ' ' . $dsUser->getValue(DBEUser::lastName);

        $message =
            '<html lang="en">
        <head >
        <title>Quote</title>
        <style type="text/css">
        <!--
        BODY, P, TD, TH {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 10pt;
        }
        .singleBorder {
          border: #e1e1f0 2px solid;
        }
        TABLE {
          border-spacing: 1px;
        }
        -->
        </style>
        </head>
        <body>
      ';
        // Send email with attachment
        $message .= '<P>' . $dbeQuotation->getValue(DBEQuotation::salutation) . '</P>';
        $message .= '<P>Please find attached a quotation for your attention.</P>';
        $message .= '<P>If you have any questions please do not hesitate to contact us.</P>';

        if ($dbeQuotation->getValue(DBEQuotation::documentType) == 'order form') {
            $message .= ' To allow us to process your order please complete, sign and return at your earliest convenience';
        }

        $message .= '<P>Regards,</P>';

        $message .=
            '</body>
        </html>';

        ini_set(
            "sendmail_from",
            $senderEmail
        );    // the envelope from address

        $toEmail = $dsOrdhead->getValue(DBEJOrdhead::delContactEmail);

        $hdrs = array(
            'From'         => $senderName . '<' . $senderEmail . '>',
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($message);

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
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $toEmail .= ',' . CONFIG_SALES_EMAIL;

        return $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

} // end class  
