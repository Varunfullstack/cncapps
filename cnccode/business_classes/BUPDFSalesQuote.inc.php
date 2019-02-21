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
     * @param mixed $dsSelectedOrderLine
     * @return bool
     */
    function generate(
        $ordheadID,
        $salutation = false,
        $introduction = false,
        $emailSubject = false,
        $dsSelectedOrderLine = false
    )
    {

        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
            $this->displayFatalError('Order ' . $ordheadID . 'Not Found');
        }

        if (!$salutation) {
            $salutation = 'Dear ' . $dsDeliveryContact->getValue('firstName');
        }

        if (!$introduction) {
            $introduction = $dsOrdhead->getValue('quotationIntroduction');
        }

        if (!$emailSubject) {
            $emailSubject = $dsOrdhead->getValue('quotationSubject');
        }

        $versionNo = $this->buSalesOrder->getNextQuoteVersion($ordheadID);

        $quoteFile = 'quotes/' . $ordheadID . '_' . $versionNo . '.pdf';


        $buItem = new BUItem($this);

        $userID = $GLOBALS ['auth']->is_authenticated();
        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );

        $buPDF = new BUPDF(
            $this,
            $quoteFile,
            $dsUser->getValue('name'),
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
        $firstName = $dsDeliveryContact->getValue('firstName');
        $buPDF->printString(
            $dsDeliveryContact->getValue('title') . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue('lastName')
        );
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('customerName'));
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delAdd1'));
        if ($dsOrdhead->getValue('delAdd2') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delAdd2'));
        }
        if ($dsOrdhead->getValue('delAdd3') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delAdd3'));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delTown'));
        if ($dsOrdhead->getValue('delCounty') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delCounty'));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delPostcode'));
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
                    $dsOrdline->getValue("sequenceNo")
                )
            ) {
                if ($dsOrdline->getValue('lineType') == "I") {
                    if ($dsOrdline->getValue('itemDescription') != '') {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('itemDescription')
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('description')
                        );
                    }
                    $buPDF->printStringRJAt(
                        30,
                        Controller::formatNumber(
                            $dsOrdline->getValue('qtyOrdered'),
                            2
                        )
                    );
                    /*
                    Do not print zero sale values
                    */
                    if ($dsOrdline->getValue('curUnitSale') != 0) {
                        $buPDF->printStringRJAt(
                            150,
                            Controller::formatNumberCur($dsOrdline->getValue('curUnitSale'))
                        );
                        $total = ($dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered'));
                        $buPDF->printStringRJAt(
                            170,
                            Controller::formatNumberCur($total)
                        );
                        $grandTotal += $total;
                    }
                    if ($dsOrdline->getValue(
                            'itemID'
                        ) != 0) {            // some item lines in old system did not have a related item record
                        $buItem->getItemByID(
                            $dsOrdline->getValue('itemID'),
                            $dsItem
                        );
                        /*
                        now that the notes are in a text field we need to split the lines up for the PDF printing
                        */
                        if ($dsItem->getValue('notes') != '') {
                            $buPDF->setFontSize(8);
                            $buPDF->setFont();
                            $notesArray = explode(
                                chr(13) . chr(10),
                                $dsItem->getValue('notes')
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
                        $dsOrdline->getValue('description')
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
        if ($dsUser->getValue('signatureFilename')) {
            $filePath = IMAGES_DIR . '/' . $dsUser->getValue('signatureFilename');
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
        $buPDF->printString($dsUser->getValue('firstName') . ' ' . $dsUser->getValue('lastName'));
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printString($dsUser->getValue('jobTitle'));
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
        $firstName = $dsDeliveryContact->getValue('firstName');
        $buPDF->printStringAt(
            130,
            $dsDeliveryContact->getValue(
                'title'
            ) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue('lastName')
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('customerName')
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delAdd1')
        );
        if ($dsOrdhead->getValue('delAdd2') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delAdd2')
            );
        }
        if ($dsOrdhead->getValue('delAdd3') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delAdd3')
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delTown')
        );
        if ($dsOrdhead->getValue('delCounty') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delCounty')
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delPostcode')
        );
        $buPDF->CR();
        $buPDF->printString($dsUser->getValue('firstName') . ' ' . $dsUser->getValue('lastName'));
        $buPDF->CR();
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $buPDF->printString($dsHeader->getValue('name'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add1'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add2'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add3'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('town'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('county'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('postcode'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Dear ' . $dsUser->getValue('firstName') . ',');
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
                    $dsOrdline->getValue("sequenceNo")
                )
            ) {
                if ($dsOrdline->getValue('lineType') == "I") {
                    if ($dsOrdline->getValue('itemDescription') != '') {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('itemDescription')
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('description')
                        );
                    }
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue('curUnitSale'))
                    );
                    $total = ($dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered'));
                    if ($dsOrdline->getValue(
                            'itemID'
                        ) != 0) {            // some item lines in old system did not have a related item record
                        $buItem->getItemByID(
                            $dsOrdline->getValue('itemID'),
                            $dsItem
                        );
                    }
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue('description')
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
        $buPDF->printString('Please fax to 0845 0700 584 or email a scanned copy to sales@cnc-ltd.co.uk');
        $buPDF->endPage();
        // End of second page
        $buPDF->close();

        // Insert into database
        $dsQuotation = new DataSet($this);
        $dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $dsQuotation->setUpdateModeInsert();
        $dsQuotation->setValue(
            'versionNo',
            $versionNo
        );
        $dsQuotation->setValue(
            'ordheadID',
            $dsOrdhead->getValue('ordheadID')
        );
        $dsQuotation->setValue(
            'userID',
            $userID
        );
        $dsQuotation->setValue(
            'sentDateTime',
            date('0000-00-00 00:00:00')
        );
        $dsQuotation->setValue(
            'salutation',
            $salutation
        );
        $dsQuotation->setValue(
            'emailSubject',
            $emailSubject
        );
        $dsQuotation->setValue(
            'fileExtension',
            'pdf'
        );
        $dsQuotation->setValue(
            'documentType',
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
     */
    function sendReminderPDFEmailQuote($quotationID,
                                       $emailSubject
    )
    {
        $buMail = new BUMail($this);

        $dbeQuotation = new DBEQuotation($this);

        if (!$dbeQuotation->getRow($quotationID)) {
            $this->displayFatalError('Quotation Not Found');

        }

        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue('ordheadID'),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
            $this->displayFatalError('Sales Order Not Found');
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

        $DBEJRenQuotation->setValue(
            DBEJRenQuotation::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $DBEJRenQuotation->getRowsByColumn(DBEJRenQuotation::ordheadID);

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
                'renewalType'      => $DBEJRenQuotation->getValue($DBEJRenQuotation::type),
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
        $senderEmail = "sales@cnc-ltd-co.uk";

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
     */
    function sendPDFEmailQuote($quotationID)
    {
        $buMail = new BUMail($this);

        $dbeQuotation = new DBEQuotation($this);

        if (!$dbeQuotation->getRow($quotationID)) {
            $this->displayFatalError('Quotation Not Found');

        }

        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $dbeQuotation->getValue('ordheadID'),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )
        ) {
            $this->displayFatalError('Sales Order Not Found');
        }

        $userID = $GLOBALS ['auth']->is_authenticated();

        $this->buSalesOrder->getUserByID(
            $userID,
            $dsUser
        );

        $quoteFile = 'quotes/' . $dbeQuotation->getValue('ordheadID') . '_' . $dbeQuotation->getValue(
                'versionNo'
            ) . '.pdf';

        $body = $dbeQuotation->getValue('documentType') . ' ' . $dsOrdhead->getValue(
                'ordheadID'
            ) . '/' . $dbeQuotation->getValue('versionNo') . ' for ' . $dbeQuotation->getValue('emailSubject');

        $subject = ucwords($body);

        $senderEmail = $dsUser->getValue('username') . '@cnc-ltd.co.uk';
        $senderName = $dsUser->getValue('firstName') . ' ' . $dsUser->getValue('lastName');

        $message =
            '<html>
        <head>
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
        $message .= '<P>' . $dbeQuotation->getValue('salutation') . '</P>';
        $message .= '<P>Please find attached a quotation for your attention.</P>';
        $message .= '<P>If you have any questions please do not hesitate to contact us.</P>';

        if ($dbeQuotation->getValue('documentType') == 'order form') {
            $message .= ' To allow us to process your order please complete, sign and return at your earliest convenience';
        }

        $message .= '<P>Regards,</P>';

        $message .=
            '</body>
        </html>';

        $filename = $dsOrdhead->getValue('ordheadID') . '_' . $dbeQuotation->getValue('versionNo') . '.pdf';

        ini_set(
            "sendmail_from",
            $senderEmail
        );    // the envelope from address

        $toEmail = $dsOrdhead->getValue('delContactEmail');

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
?>
