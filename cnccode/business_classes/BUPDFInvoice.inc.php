<?php /**
 * PDF Invoice Generation business class
 *
 * Generates a PDF file of invoices for given date range or invoice number range.
 *
 * Each invoice starts on a new page
 * Long invoices continue on to new pages.
 * Last page of invoice shows sub total, VAT value, grand total and payment terms.
 * Credit notes are catered for with Credit Note printed at top of page instead of Invoice and
 * without payment terms or *** thank you for your order *** message on last line.
 *
 * DISCLAIMER: I realise there are probably some slick PDF table functions I could have used
 *                            instead of my manual table layout method but it works for me!
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUPDF.inc.php');
require_once($cfg['path_dbe'] . '/DBEPaymentTerms.inc.php');
define(
    'BUPDFINV_NUMBER_OF_LINES',
    30
);
// print column positions
define(
    'BUPDFINV_QTY_COL',
    23
);
define(
    'BUPDFINV_DETAILS_COL',
    32
);
define(
    'BUPDFINV_UNIT_PRICE_COL',
    159
);
define(
    'BUPDFINV_COST_COL',
    194
);
// box dimensions
define(
    'BUPDFINV_QTY_BOX_WIDTH',
    19
);
define(
    'BUPDFINV_DETAILS_BOX_WIDTH',
    97.5
);
define(
    'BUPDFINV_UNIT_PRICE_BOX_WIDTH',
    35
);    // used for cost box too
define(
    'BUPDFINV_QTY_BOX_LEFT_EDGE',
    11
);
define(
    'BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE',
    // relative to other boxes
    BUPDFINV_QTY_BOX_LEFT_EDGE +
    BUPDFINV_QTY_BOX_WIDTH +
    BUPDFINV_DETAILS_BOX_WIDTH
);
define(
    'BUPDFINV_COST_BOX_LEFT_EDGE',
    BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE +
    BUPDFINV_UNIT_PRICE_BOX_WIDTH
);
// notpad key
define(
    'BUPDFINV_NOTEPAD_ITEM',
    'INV'
);

class BUPDFInvoice extends BaseObject
{
    /** @var BUPDF */
    public $_buPDF;
    /** @var BUInvoice */
    public $_buInvoice;
    /** @var DataSet|DBEInvhead */
    public $_dsInvhead;
    public $_customerID;
    public $_startDate;
    public $_endDate;
    public $_dateToUse;
    public $_startInvheadID;
    public $_endInvheadID;
    public $_titleLine;

    /**
     * Constructor
     *
     * Requires a reference to a _buInvoice business class for access to invoice data
     * @param $owner
     * @param $buInvoice
     */
    function __construct(&$owner,
                         &$buInvoice
    )
    {
        BaseObject::__construct($owner);
        if (is_a(
            $buInvoice,
            'buInvoice'
        )) {
            $this->_buInvoice = $buInvoice;
        } else {
            $this->raiseError('_buInvoice object not passed');
        }
    }

    function reprintInvoicesByRange($customerID,
                                    $startDate,
                                    $endDate,
                                    $startInvheadID,
                                    $endInvheadID
    )
    {
        $this->_customerID = $customerID;
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->_startInvheadID = $startInvheadID;
        $this->_endInvheadID = $endInvheadID;

        $this->_buInvoice->getPrintedInvoicesByRange(
            $this->_customerID,
            $this->_startDate,
            $this->_endDate,
            $this->_startInvheadID,
            $this->_endInvheadID,
            $this->_dsInvhead
        );

        return ($this->generateBatchFile($this->_dsInvhead));
    }

    /**
     * Generate one invoice and return file path
     *
     * @access private
     * @param $dsInvhead
     * @return String PDF disk file name or FALSE
     */
    function generateFile($dsInvhead
    )
    {

        $this->_dsInvhead = $dsInvhead;

        $tempFile = @tempnam(
            '/tmp',
            'INV'
        );            // temporary disk file

        $this->_buPDF = new BUPDF(
            $this, $tempFile, 'CNC accounts', date('d/m/Y'), 'CNC Ltd', 'Sales Invoice'
        );

        $this->produceInvoice();

        $this->_buPDF->close();

        unset($this->_buPDF);

        return $tempFile;

    }

    /**
     * Generate all invoices as one pdf file and return file path
     *
     * @access private
     * @param $dsInvhead
     * @return String PDF disk file name or FALSE
     */
    function generateBatchFile($dsInvhead)
    {

        $this->_dsInvhead = $dsInvhead;

        if ($this->_dsInvhead->fetchNext()) {
            // initialisation
            $tempFile = @tempnam(
                '/tmp',
                'INV'
            );      // temporary disk file

            $this->_buPDF = new BUPDF(
                $this, $tempFile, 'CNC accounts', date('d/m/Y'), 'CNC Ltd', 'Sales Invoices'
            );
            do {
                // Generate each invoice
                $this->produceInvoice();
            } while ($this->_dsInvhead->fetchNext());
            // Finalisation
            $this->_buPDF->close();

            return $tempFile;

        } else {
            return FALSE;    // no invoices found
        }
    }

    function produceInvoice()
    {
        $this->invoiceHead();
        $dsInvline = new DataSet($this);
        $this->_buInvoice->getInvoiceLines(
            $this->_dsInvhead->getValue(DBEInvhead::invheadID),
            $dsInvline
        );
        $this->_buPDF->CR();
        $lineCount = 0;
        $linesForLastPage = 5;
        $linesForLogo = 10;
        $grandTotal = 0;
        $dbePaymentTerms = null;
        while ($dsInvline->fetchNext()) {
            $lineCount++;
            if ($lineCount > BUPDFINV_NUMBER_OF_LINES - $linesForLastPage) {
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    'Continued on next page...'
                );
                $this->invoiceHead();
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    '... continued from previous page'
                );
                $this->_buPDF->CR();
                $lineCount = 2;
            }
            if ($dsInvline->getValue(DBEInvline::lineType) == "I") {
                if (
                    ($dsInvline->getValue(DBEJInvline::itemDescription) != '') AND
                    ($dsInvline->getValue(DBEInvline::stockcat) != 'G')
                ) {
                    $this->_buPDF->printStringAt(
                        BUPDFINV_DETAILS_COL,
                        $dsInvline->getValue(DBEJInvline::itemDescription)
                    );
                } else {
                    $this->_buPDF->printStringAt(
                        BUPDFINV_DETAILS_COL,
                        $dsInvline->getValue(DBEInvline::description)
                    );
                }
                $this->_buPDF->printStringRJAt(
                    BUPDFINV_QTY_COL,
                    $dsInvline->getValue(DBEInvline::qty)
                );
                $this->_buPDF->printStringRJAt(
                    BUPDFINV_UNIT_PRICE_COL,
                    POUND_CHAR . number_format(
                        $dsInvline->getValue(DBEInvline::curUnitSale),
                        2,
                        '.',
                        ','
                    )
                );
                $total = ($dsInvline->getValue(DBEInvline::curUnitSale) * $dsInvline->getValue(DBEInvline::qty));
                $this->_buPDF->printStringRJAt(
                    BUPDFINV_COST_COL,
                    POUND_CHAR . number_format(
                        $total,
                        2,
                        '.',
                        ','
                    )
                );
                $grandTotal += $total;
            } else {
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    $dsInvline->getValue(DBEInvline::description)
                ); // comment line
            }

            $this->_buPDF->CR();
        }


        if ($this->_dsInvhead->getValue(DBEInvhead::directDebitFlag) == "Y") {

            if ($lineCount > BUPDFINV_NUMBER_OF_LINES - $linesForLastPage - $linesForLogo) {
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    'Continued on next page...'
                );
                $this->invoiceHead();
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    '... continued from previous page'
                );
                $this->_buPDF->CR();
            }

            $this->_buPDF->moveYTo((BUPDFINV_NUMBER_OF_LINES - 13.0) * $this->_buPDF->getFontSize());
            $this->_buPDF->placeImageAt(
                IMAGES_DIR . '/PAID.gif',
                'gif',
                BUPDFINV_DETAILS_COL,
                90
            );
        }

        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();

        if ($this->_dsInvhead->getValue(DBEInvhead::type) == 'I') {
            $this->_buPDF->moveYTo((BUPDFINV_NUMBER_OF_LINES - 7.5) * $this->_buPDF->getFontSize());
            $this->_buPDF->printStringAt(
                BUPDFINV_DETAILS_COL,
                '***** Thank you for your business *****'
            );

            if ($this->_dsInvhead->getValue(DBEInvhead::type) == 'I') {
                $this->_buPDF->CR();
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    'Goods remain the property of Computer & Network'
                );
                $this->_buPDF->CR();
                $this->_buPDF->printStringAt(
                    BUPDFINV_DETAILS_COL,
                    'Consultants Ltd until paid for in full'
                );
            }

            if (!$dbePaymentTerms) {
                $dbePaymentTerms = new DBEPaymentTerms($this);
            }
            $dbePaymentTerms->getRow($this->_dsInvhead->getValue(DBEInvhead::paymentTermsID));
            $this->_buPDF->moveYTo($this->_titleLine + (BUPDFINV_NUMBER_OF_LINES * $this->_buPDF->getFontSize() / 2));
        } else {
            $this->_buPDF->moveYTo($this->_titleLine + (BUPDFINV_NUMBER_OF_LINES * $this->_buPDF->getFontSize() / 2));

        }
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'Sub Total'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_COST_COL,
            POUND_CHAR . number_format(
                $grandTotal,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->CR();
        if ($this->_dsInvhead->getValue(DBEInvhead::type) == 'I') {
            $this->_buPDF->printString('Payment terms: ' . $dbePaymentTerms->getValue(DBEPaymentTerms::description));
        }
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'VAT @ ' . number_format(
                $this->_dsInvhead->getValue(DBEInvhead::vatRate),
                1
            ) . '%'
        );
        $vatValue = $grandTotal * ($this->_dsInvhead->getValue(DBEInvhead::vatRate) / 100);

        // for some reason number_format insists on truncating the VAT value so I round it first!
        $vatValue = $this->myFormattedRoundedNumber($vatValue);
//		$vatValue = round($vatValue,2);
        $this->_buPDF->printStringRJAt(
            BUPDFINV_COST_COL,
            POUND_CHAR . number_format(
                $vatValue,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'Grand Total'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_COST_COL,
            POUND_CHAR . number_format(
                $grandTotal + $vatValue,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->printString('BACS Details: Computer & Network Consultants Ltd');
        $this->_buPDF->CR();
        $this->_buPDF->printString('Bank Sort Code: 20-23-97');
        $this->_buPDF->printStringAt(
            60,
            'Bank Account: 30551090'
        );
        $this->_buPDF->printStringAt(
            110,
            'Bank Name: Barclays Bank plc.'
        );
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();

        $this->_buPDF->placeImageAt(
            $GLOBALS['cfg']['cncaddress_path'],
            'JPEG',
            0,
            220
        );

        $this->_buPDF->endPage();
    }

    /**
     *    Output the invoice header.
     * This gets called once at the start of each page.
     * Where an invoice spans pages it gets called many times for the same invoice.
     *
     * @access private
     */
    function invoiceHead()
    {
        $this->_buPDF->startPage();
        $this->_buPDF->placeImageAt(
            $GLOBALS['cfg']['cnclogo_path'],
            'PNG',
            150,
            38
        );

        $this->_buPDF->setFontSize(6);
        $this->_buPDF->setFontFamily(BUPDF_FONT_ARIAL);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFontSize(20);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        if ($this->_dsInvhead->getValue(DBEInvhead::type) == 'I') {
            $this->_buPDF->printString('Invoice');
        } else {
            $this->_buPDF->printString('Credit Note');
        }
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $firstAddLine = $this->_buPDF->getYPos();    // remember this line no
        $this->_buPDF->printString($this->_dsInvhead->getValue(DBEJInvhead::customerName));
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::add1));
        if ($this->_dsInvhead->getValue(DBEInvhead::add2) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::add2));
        }
        if ($this->_dsInvhead->getValue(DBEInvhead::add3) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::add3));
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::town));
        if ($this->_dsInvhead->getValue(DBEInvhead::county) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::county));
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($this->_dsInvhead->getValue(DBEInvhead::postcode));
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->printString(
            'F.A.O. ' .
            $this->_dsInvhead->getValue(DBEJInvhead::title) . ' ' .
            $this->_dsInvhead->getValue(DBEJInvhead::firstName) . ' ' .
            $this->_dsInvhead->getValue(DBEJInvhead::lastName)
        );
        $this->_buPDF->getYPos();
        $this->_buPDF->moveYTo($firstAddLine);    //move back up the page
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        if ($this->_dsInvhead->getValue(DBEInvhead::type) == 'I') {
            $this->_buPDF->printStringRJAt(
                BUPDFINV_UNIT_PRICE_COL,
                'Invoice No'
            );
        } else {
            $this->_buPDF->printStringRJAt(
                BUPDFINV_UNIT_PRICE_COL,
                'Credit Note No'
            );
        }
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_dsInvhead->getValue(DBEInvhead::invheadID)
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'Date'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        if ($this->_dateToUse != '') {
            $this->_buPDF->printStringAt(
                BUPDFINV_COST_BOX_LEFT_EDGE,
                Controller::dateYMDtoDMY($this->_dateToUse)
            );
        } else {
            $this->_buPDF->printStringAt(
                BUPDFINV_COST_BOX_LEFT_EDGE,
                Controller::dateYMDtoDMY($this->_dsInvhead->getValue(DBEInvhead::datePrinted))
            );
        }
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'CNC Order No'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_dsInvhead->getValue(DBEInvhead::customerID) . '/' . $this->_dsInvhead->getValue(
                DBEInvhead::ordheadID
            )
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'V.A.T. Reg No'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            'GB673838003'
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL,
            'Customer Order'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            substr(
                $this->_dsInvhead->getValue(DBEInvhead::custPORef),
                0,
                15
            )
        );
        $this->_buPDF->CR();
        // empty box
        $this->_buPDF->box(
            BUPDFINV_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFINV_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->CR();
        $this->_titleLine = $this->_buPDF->getYPos();
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        // box around all detail headings
        $this->_buPDF->box(
            BUPDFINV_QTY_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_QTY_BOX_WIDTH + BUPDFINV_DETAILS_BOX_WIDTH + (BUPDFINV_UNIT_PRICE_BOX_WIDTH * 2),
            $this->_buPDF->getFontSize() / 2
        );
        // Around Qty column
        $this->_buPDF->box(
            BUPDFINV_QTY_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFINV_QTY_BOX_WIDTH,
            (BUPDFINV_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        // Around details
        $this->_buPDF->box(
            BUPDFINV_QTY_BOX_LEFT_EDGE + BUPDFINV_QTY_BOX_WIDTH,
            $this->_buPDF->getYPos(),
            BUPDFINV_DETAILS_BOX_WIDTH,
            (BUPDFINV_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        // Box around the Unit Price
        $this->_buPDF->box(
            BUPDFINV_QTY_BOX_LEFT_EDGE + BUPDFINV_QTY_BOX_WIDTH + BUPDFINV_DETAILS_BOX_WIDTH,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            (BUPDFINV_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        // Box around the Cost
        $this->_buPDF->box(
            BUPDFINV_QTY_BOX_LEFT_EDGE + BUPDFINV_QTY_BOX_WIDTH + BUPDFINV_DETAILS_BOX_WIDTH + BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getYPos(),
            BUPDFINV_UNIT_PRICE_BOX_WIDTH,
            (BUPDFINV_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_QTY_COL,
            'Qty'
        );
        $this->_buPDF->printStringAt(
            BUPDFINV_DETAILS_COL,
            'Details'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_UNIT_PRICE_COL - 5,
            'Unit Price'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFINV_COST_COL - 11,
            'Cost'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
    }

    function getLastDateOfMonth($date)
    {
        $year = substr(
            $date,
            0,
            4
        );
        $month = substr(
            $date,
            5,
            2
        );
        $day = date(
            "t",
            mktime(
                0,
                0,
                0,
                $month,
                1,
                $year
            )
        );
        return $day . '/' . $month . '/' . $year;
    }

    function myFormattedRoundedNumber($number,
                                      $fuzz = 0.00000000001
    )
    {
        return sprintf(
            "%.2f",
            (($number >= 0) ? ($number + $fuzz) : ($number - $fuzz))
        );
    }
}
