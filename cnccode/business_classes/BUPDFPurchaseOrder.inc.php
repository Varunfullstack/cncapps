<?php /**
 * PDF PurchaseOrder Generation business class
 *
 * Generates a PDF file of invoices for given date range or invoice number range.
 *
 * Each invoice starts on a new page
 * Long invoices continue on to new pages.
 * Last page of invoice shows sub total, VAT value, grand total and payment terms.
 * Credit notes are catered for with Credit Note printed at top of page instead of PurchaseOrder and
 * without payment terms or *** thank you for your order *** message on last line.
 *
 * DISCLAIMER: I realise there are probably some slick PDF table functions I could have used
 *                            instead of my manual table layout method but it works for me!
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\Supplier;
use CNCLTD\Supplier\SupplierId;

global $cfg;
require_once($cfg['path_bu'] . '/BUPDF.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_dbe'] . '/DBEPayMethod.inc.php'); // this is a bit of a cheat
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
define(
    'BUPDFPOR_NUMBER_OF_LINES',
    20
);
// print column positions
define(
    'BUPDFPOR_QTY_COL',
    21
);
define(
    'BUPDFPOR_DETAILS_COL',
    23
);
define(
    'BUPDFPOR_PART_COL',
    117
);
define(
    'BUPDFPOR_UNIT_PRICE_COL',
    163
);
define(
    'BUPDFPOR_COST_COL',
    197
);
// box dimensions
define(
    'BUPDFPOR_QTY_BOX_WIDTH',
    12
);
define(
    'BUPDFPOR_DETAILS_BOX_WIDTH',
    85
);
define(
    'BUPDFPOR_PART_BOX_WIDTH',
    25
);
define(
    'BUPDFPOR_UNIT_PRICE_BOX_WIDTH',
    33
);    // used for cost box too
define(
    'BUPDFPOR_QTY_BOX_LEFT_EDGE',
    11
);
define(
    'BUPDFPOR_ADDRESS_BOX_WIDTH',
    60
);
define(
    'BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE',
    // relative to other boxes
    BUPDFPOR_QTY_BOX_LEFT_EDGE + BUPDFPOR_QTY_BOX_WIDTH + BUPDFPOR_DETAILS_BOX_WIDTH + BUPDFPOR_PART_BOX_WIDTH
);
define(
    'BUPDFPOR_COST_BOX_LEFT_EDGE',
    BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE + BUPDFPOR_UNIT_PRICE_BOX_WIDTH
);

class BUPDFPurchaseOrder extends BaseObject
{
    /** @var BUPDF */
    public $_buPDF;
    /** @var BUPurchaseOrder */
    public $_buPurchaseOrder;
    public $_buContact;
    public $_dbePayMethod;
    public $_buSalesOrder;
    /** @var DataSet|DBEJPorhead */
    public $_dsPorhead;
    /** @var DataSet|DBEJOrdhead */
    public $_dsOrdhead;
    /** @var DataSet|DBEJPorline */
    public $_dsPorline;
    /** @var DataSet|DBEContact */
    public $_dsCustomerContact;
    /** @var DataSet|DBEUser */
    public $_dsUser;
    public $_porheadID;
    public $_titleLine;

    /**
     * Constructor
     *
     * Requires a reference to a _buPurchaseOrder business class for access to invoice data
     * @param $owner
     * @param $buPurchaseOrder
     * @param $porheadID
     */
    function __construct(&$owner,
                         &$buPurchaseOrder,
                         $porheadID
    )
    {
        parent::__construct($owner);
        $this->_porheadID = $porheadID;
        if (is_a(
            $buPurchaseOrder,
            'buPurchaseOrder'
        )) {
            $this->_buPurchaseOrder = $buPurchaseOrder;
        } else {
            $this->raiseError('_buPurchaseOrder object not passed');
        }
        $this->_buContact    = new BUContact($this);
        $this->_buSalesOrder = new BUSalesOrder($this);
        $this->_dbePayMethod = new DBEPayMethod($this);
    }

    /**
     * Use the parameters passed in constructor to get list of invoices and generate a PDF file on
     * disk.
     * If no invoices are found then return FALSE
     * @return bool|resource PDF disk file name or FALSE
     */
    function generateFile()
    {
        $this->setMethodName('generateFile');
        $tempFile = tmpfile();            // temporary disk file
        if (!$this->_buPurchaseOrder->getOrderByID(
            $this->_porheadID,
            $this->_dsPorhead,
            $this->_dsPorline
        )) {
            $this->raiseError('Order not found');
        }
        $this->_dsPorhead->fetchNext();
        $supplierRepo    = new MySQLSupplierRepository();
        $supplier        = $supplierRepo->getById(
            new SupplierId($this->_dsPorhead->getValue(DBEPorhead::supplierID))
        );
        $supplierContact = $supplier->getContactById(
            new SupplierContactId(
                $this->_dsPorhead->getValue(DBEPorhead::supplierContactId)
            )
        );
        $this->_buSalesOrder->getUserByID(
            $this->_dsPorhead->getValue(DBEPorhead::userID),
            $this->_dsUser
        );
        if ($this->_dsPorhead->getValue(DBEPorhead::ordheadID) != 0) {
            $this->_buSalesOrder->getOrdheadByID(
                $this->_dsPorhead->getValue(DBEPorhead::ordheadID),
                $this->_dsOrdhead
            );
            $this->_buContact->getContactByID(
                $this->_dsOrdhead->getValue(DBEJOrdhead::delContactID),
                $this->_dsCustomerContact
            );
        }
        $this->_dbePayMethod->getRow($this->_dsPorhead->getValue(DBEPorhead::payMethodID));
        $path = stream_get_meta_data($tempFile)['uri']; // eg: /tmp/phpFx0513a
        // initialisation
        $this->_buPDF = new BUPDF(
            $this, $path, 'CNC accounts', date('d/m/Y'), 'CNC Ltd', 'Purchase Order'
        );
        $this->producePurchaseOrder($supplier, $supplierContact);
        $this->_buPDF->close();
        return $tempFile;
    }

    function producePurchaseOrder(Supplier $supplier, SupplierContact $supplierContact)
    {
        $this->orderHead($supplier, $supplierContact);
        $this->_buPDF->CR();
        $dsPorline = &$this->_dsPorline;
        $lineCount = 0;
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $grandTotal = 0;
        while ($dsPorline->fetchNext()) {
            $lineCount++;
            if ($lineCount > BUPDFPOR_NUMBER_OF_LINES - 4) { // can't be bothered to find out why -4 !
                $this->_buPDF->printStringAt(
                    BUPDFPOR_DETAILS_COL,
                    'Continued on next page...'
                );
                $this->orderHead($supplier, $supplierContact);
                $this->_buPDF->printStringAt(
                    BUPDFPOR_DETAILS_COL,
                    '... continued from previous page'
                );
                $this->_buPDF->CR();
                $lineCount = 2;
            }
            $this->_buPDF->printStringRJAt(
                BUPDFPOR_QTY_COL,
                $dsPorline->getValue(DBEJPorline::qtyOrdered)
            );
            $this->_buPDF->printStringAt(
                BUPDFPOR_DETAILS_COL,
                $dsPorline->getValue(DBEJPorline::itemDescription)
            );
            $this->_buPDF->printStringAt(
                BUPDFPOR_PART_COL - 5,
                $dsPorline->getValue(DBEJPorline::partNo)
            );
            $this->_buPDF->printStringRJAt(
                BUPDFPOR_UNIT_PRICE_COL,
                POUND_CHAR . number_format(
                    $dsPorline->getValue(DBEJPorline::curUnitCost),
                    2,
                    '.',
                    ','
                )
            );
            $total = ($dsPorline->getValue(DBEJPorline::curUnitCost) * $dsPorline->getValue(DBEJPorline::qtyOrdered));
            $this->_buPDF->printStringRJAt(
                BUPDFPOR_COST_COL,
                POUND_CHAR . number_format(
                    $total,
                    2,
                    '.',
                    ','
                )
            );
            $grandTotal += $total;
            $this->_buPDF->CR();
        }
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $savedYPos = $this->_buPDF->getYPos();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'Sub Total'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_COST_COL,
            POUND_CHAR . number_format(
                $grandTotal,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'VAT @ ' . number_format(
                $this->_dsPorhead->getValue(DBEJPorhead::vatRate),
                1
            ) . '%'
        );
        $vatValue = $grandTotal * ($this->_dsPorhead->getValue(DBEJPorhead::vatRate) / 100);
        // for some reason number_format insists on truncating the VAT value so I round it first!
        $vatValue = $this->myFormattedRoundedNumber($vatValue);
//		$vatValue = round($vatValue,2);
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_COST_COL,
            POUND_CHAR . number_format(
                $vatValue,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'Grand Total'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_COST_COL,
            POUND_CHAR . number_format(
                $grandTotal + $vatValue,
                2,
                '.',
                ','
            )
        );
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        /*
        Draw boxes around columns
        */
        // Around Qty column
        $this->_buPDF->box(
            BUPDFPOR_QTY_BOX_LEFT_EDGE,
            $this->_titleLine,
            BUPDFPOR_QTY_BOX_WIDTH,
            $savedYPos - $this->_titleLine
        );
        // Around details
        $this->_buPDF->box(
            BUPDFPOR_QTY_BOX_LEFT_EDGE + BUPDFPOR_QTY_BOX_WIDTH,
            $this->_titleLine,
            BUPDFPOR_DETAILS_BOX_WIDTH,
            $savedYPos - $this->_titleLine
        );
        // Box around the Part no
        $this->_buPDF->box(
            BUPDFPOR_QTY_BOX_LEFT_EDGE + BUPDFPOR_QTY_BOX_WIDTH + BUPDFPOR_DETAILS_BOX_WIDTH,
            $this->_titleLine,
            BUPDFPOR_PART_BOX_WIDTH,
            $savedYPos - $this->_titleLine
        );
        // Box around the Cost
        $this->_buPDF->box(
            BUPDFPOR_QTY_BOX_LEFT_EDGE + BUPDFPOR_QTY_BOX_WIDTH + BUPDFPOR_DETAILS_BOX_WIDTH + BUPDFPOR_PART_BOX_WIDTH + BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_titleLine,
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $savedYPos - $this->_titleLine
        );
        /*
        End of drawing boxes around columns
        */
        $this->_buPDF->moveYTo($savedYPos);
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        if (($this->_dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == 'N') or ($this->_dsPorhead->getValue(
                    DBEJPorhead::ordheadID
                ) == 0)) {
            $this->_buPDF->printString('Please deliver to CNC at the address shown below.');
            $this->_buPDF->CR();
        } else {
            $this->_buPDF->printString('*** PLEASE DELIVER TO THE FOLLOWING ADDRESS ***');
            $this->_buPDF->setFontSize(8);
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(
                15,
                $this->_dsCustomerContact->getValue(DBEContact::title) . ' ' . $this->_dsCustomerContact->getValue(
                    DBEContact::firstName
                ) . ' ' . $this->_dsCustomerContact->getValue(DBEContact::lastName) . ' '
            );
            $savedYPos = $this->_buPDF->getYPos();
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(
                15,
                $this->_dsOrdhead->getValue(DBEJOrdhead::customerName)
            );
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(
                15,
                $this->_dsOrdhead->getValue(DBEJOrdhead::delAdd1)
            );
            $this->_buPDF->CR();
            if ($this->_dsOrdhead->getValue(DBEJOrdhead::delAdd2)) {
                $this->_buPDF->printStringAt(
                    15,
                    $this->_dsOrdhead->getValue(DBEJOrdhead::delAdd2)
                );
                $this->_buPDF->CR();
            }
            if ($this->_dsOrdhead->getValue(DBEJOrdhead::delAdd3)) {
                $this->_buPDF->printStringAt(
                    15,
                    $this->_dsOrdhead->getValue(DBEJOrdhead::delAdd3)
                );
                $this->_buPDF->CR();
            }
            $this->_buPDF->printStringAt(
                15,
                $this->_dsOrdhead->getValue(DBEJOrdhead::delTown)
            );
            $this->_buPDF->CR();
            if ($this->_dsOrdhead->getValue(DBEJOrdhead::delCounty)) {
                $this->_buPDF->printStringAt(
                    15,
                    $this->_dsOrdhead->getValue(DBEJOrdhead::delCounty)
                );
                $this->_buPDF->CR();
            }
            $this->_buPDF->printStringAt(
                15,
                $this->_dsOrdhead->getValue(DBEJOrdhead::delPostcode)
            );
            $this->_buPDF->CR();
            // Box around the address
            $this->_buPDF->box(
                BUPDFPOR_QTY_BOX_LEFT_EDGE,
                $savedYPos,
                BUPDFPOR_ADDRESS_BOX_WIDTH,
                $this->_buPDF->getYPos() - $savedYPos
            );
        }
        $this->_buPDF->CR();
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->printString('Please part-ship if necessary.');
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->printString(
            'Please accept payment by ' . $this->_dbePayMethod->getValue(DBEPayMethod::description) . '.'
        );
        $this->_buPDF->CR();
        if ($this->_dbePayMethod->getValue(DBEPayMethod::cardFlag) == 'Y') {
            $dsCardholder = new DataSet($this);
            $this->_buSalesOrder->getUserByID(
                $this->_dbePayMethod->getValue(DBEPayMethod::userID),
                $dsCardholder
            );
            $dsCardholder->fetchNext();
            $this->_buPDF->printStringAt(
                15,
                'Card No: '
            );
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                40,
                $this->_dbePayMethod->getValue(DBEPayMethod::cardNumber)
            );
            $this->_buPDF->CR();
            $this->_buPDF->setBoldOn();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                15,
                'Expiry Date: '
            );
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                40,
                Controller::dateYMDtoDMY($this->_dbePayMethod->getValue(DBEPayMethod::expiryDate))
            );
            $this->_buPDF->CR();
            $this->_buPDF->setBoldOn();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                15,
                'Cardholder'
            );
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                40,
                $dsCardholder->getValue(DBEUser::name)
            );
            $this->_buPDF->CR();
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString('** ORDER MUST BE CONFIRMED WITHIN 48 HOURS OR IT IS CONSIDERED INVALID **');
        $this->_buPDF->CR();
        $this->_buPDF->CR();
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
     * @param Supplier $supplier
     * @param SupplierContact $supplierContact
     */
    function orderHead(Supplier $supplier, SupplierContact $supplierContact)
    {
        $this->_buPDF->startPage();
        $this->_buPDF->placeImageAt(
            $GLOBALS['cfg']['cnclogo_path'],
            'PNG',
            142,
            45
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
        $this->_buPDF->printString('Purchase Order');
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $firstAddLine = $this->_buPDF->getYPos();    // remember this line no
        $this->_buPDF->printString($supplier->name()->value());
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $this->_buPDF->printString($supplier->address1()->value());
        if ($supplier->address2()->value()) {
            $this->_buPDF->CR();
            $this->_buPDF->printString($supplier->address2()->value());
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($supplier->town()->value());
        if ($supplier->county()->value()) {
            $this->_buPDF->CR();
            $this->_buPDF->printString($supplier->county()->value());
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($supplier->postcode()->value());
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $contactString = "F.A.O. {$supplierContact->getTitle()->value()} {$supplierContact->fullName()} ";
        $this->_buPDF->printString($contactString);
        $this->_buPDF->moveYTo($firstAddLine);    //move back up the page
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'CNC Order No'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            'P0' . $this->_dsPorhead->getValue(DBEJPorhead::porheadID)
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'Date'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            date('d/m/Y')
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'CNC Contact'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            substr(
                $this->_dsUser->getValue(DBEUser::name),
                0,
                17
            )
        );
        $this->_buPDF->CR();
        // CNC account no
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'Account No'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            substr(
                $supplier->accountCode()->value(),
                0,
                17
            )
        );
        $this->_buPDF->CR();
        // customer order ref
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL,
            'Customer Ref'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            substr(
                $this->_dsOrdhead->getValue(DBEJOrdhead::custPORef),
                0,
                15
            )
        );
        $this->_buPDF->CR();
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        // empty box
        $this->_buPDF->box(
            BUPDFPOR_UNIT_PRICE_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->box(
            BUPDFPOR_COST_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_UNIT_PRICE_BOX_WIDTH,
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->CR();
        $this->_titleLine = $this->_buPDF->getYPos();
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        // box around all detail headings
        $this->_buPDF->box(
            BUPDFPOR_QTY_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFPOR_QTY_BOX_WIDTH + BUPDFPOR_DETAILS_BOX_WIDTH + BUPDFPOR_PART_BOX_WIDTH + (BUPDFPOR_UNIT_PRICE_BOX_WIDTH * 2),
            $this->_buPDF->getFontSize() / 2
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_QTY_COL,
            'Qty'
        );
        $this->_buPDF->printStringAt(
            BUPDFPOR_DETAILS_COL,
            'Details'
        );
        $this->_buPDF->printStringAt(
            BUPDFPOR_PART_COL,
            'Part No'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_UNIT_PRICE_COL - 5,
            'Unit Price'
        );
        $this->_buPDF->printStringRJAt(
            BUPDFPOR_COST_COL - 11,
            'Cost'
        );
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
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
