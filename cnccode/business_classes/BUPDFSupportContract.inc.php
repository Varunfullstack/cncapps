<?php /**
 * PDF support contract Generation business class
 *
 * Generates a PDF support contract.
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUPDF.inc.php');
define('BUPDFSUPPORT_NUMBER_OF_LINES', 33);
// print column positions
define('BUPDFSUPPORT_DETAILS_COL', 12);
define('BUPDFSUPPORT_SERIAL_NO_COL', 138);
define('BUPDFSUPPORT_HEADING_DESC_COL', 148);
define('BUPDFSUPPORT_PURCHASE_DATE', 175);
// box dimensions
define('BUPDFSUPPORT_DETAILS_BOX_WIDTH', 90);
define('BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH', 50);    // used for cost box too
define('BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE', 11);
define('BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE',        // relative to other boxes
    BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE +
    BUPDFSUPPORT_DETAILS_BOX_WIDTH
);
define('BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE',
    BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE +
    BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH
);

class BUPDFSupportContract extends BaseObject
{
    var $_buPDF = '';                    // BUPDF object
    var $_dsContract = '';
    var $_dsCustomerItem = '';
    var $_dsCustomer = '';
    var $_buActivity = '';
    var $_dsSite = '';
    var $_titleLine = 0;
    var $_renewalTypeID = '';
    var $_customerHasServiceDeskContract;

    /**
     * Constructor
     * @param $owner
     * @param $dsContract
     * @param $dsCustomerItem
     * @param $dsSite
     * @param $dsCustomer
     * @param $buActivity
     * @param $customerHasServiceDeskContract
     */
    function __construct(&$owner, &$dsContract, &$dsCustomerItem, &$dsSite, &$dsCustomer, &$buActivity, $customerHasServiceDeskContract)
    {
        BaseObject::__construct($owner);
        $this->_dsContract = $dsContract;
        $this->_dsCustomer = $dsCustomer;
        $this->_dsCustomerItem = $dsCustomerItem;
        $this->_dsSite = $dsSite;
        $this->_buActivity = $buActivity;
        $this->_customerHasServiceDeskContract = $customerHasServiceDeskContract;
    }

    /**
     * Use the parameters passed in constructor to get list of invoices and generate a PDF file on
     * disk.
     * If no invoices are found then return FALSE
     * @return String PDF disk file name or FALSE
     */
    function generateFile($encrypted = true)
    {
        $this->_dsContract->initialise();
        $this->_dsContract->fetchNext();
        $pdfFile = DELIVERY_NOTES_DIR . '/' . $this->_dsContract->getValue('customerItemID') . '.pdf';
        $this->_buPDF = new BUPDF(
            $this,
            $pdfFile,
            'CNC',
            date('d/m/Y'),
            'CNC Ltd',
            'Contract Schedule',
            'A4',
            $encrypted
        );
        $this->produceContract();
        $this->_buPDF->close();
        return $pdfFile;
    }

    function produceContract()
    {
        // local refs
        $dsContract = &$this->_dsContract;
        $dsCustomerItem = &$this->_dsCustomerItem;
        $dsSite = &$this->_dsSite;
        $this->noteHead();
        $lineCount = 0;
        $dsCustomerItem->initialise();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, $dsContract->getValue('itemDescription'));

        // print item notes
        // we need to split the lines up for the PDF printing
        if ($dsContract->getValue('itemNotes') != '') {
            $this->_buPDF->setFontSize(6);
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $notesArray = explode(chr(13) . chr(10), $dsContract->getValue('itemNotes'));
            foreach ($notesArray as $noteLine) {
                $this->_buPDF->CR();
                $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, $noteLine);
            }
        }

        // print customer item notes
        // we need to split the lines up for the PDF printing
        if ($dsContract->getValue('customerItemNotes') != '') {
            $this->_buPDF->setFontSize(6);
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $notesArray = explode(chr(13) . chr(10), $dsContract->getValue('customerItemNotes'));
            foreach ($notesArray as $noteLine) {
                $this->_buPDF->CR();
                $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, $noteLine);
            }
        }

        if ($this->_renewalTypeID == CONFIG_BROADBAND_RENEWAL_TYPE_ID) {
            $this->_buPDF->CR();
            $this->_buPDF->setFontSize(8);
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Phone Number: ' . $dsContract->getValue('adslPhone'));
        }

        if ($this->_renewalTypeID == CONFIG_HOSTING_RENEWAL_TYPE_ID) {
            $this->_buPDF->CR();
            $this->_buPDF->setFontSize(8);
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Domain: ' . $dsContract->getValue('notes'));
        }

        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();

        if ($dsCustomerItem->fetchNext()) {
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Schedule of items');
            $this->_buPDF->CR();
            $lineCount++;
            $this->_buPDF->setFontSize(6);
            $this->_buPDF->setFont();
            do {
                $lineCount++;
                if ($lineCount > BUPDFSUPPORT_NUMBER_OF_LINES - 4) {
                    $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Continued on next page...');
                    $this->noteHead();
                    $this->_buPDF->setFontSize(8);
                    $this->_buPDF->setFont();
                    $this->_buPDF->CR();
                    $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, '... continued from previous page');
                    $this->_buPDF->CR();
                    $lineCount = 2;
                }
                if ($dsCustomerItem->getValue('serverName')) {
                    $itemDescription = $dsCustomerItem->getValue('itemDescription') . '(' . $dsCustomerItem->getValue('serverName') . ')';
                } else {
                    $itemDescription = $dsCustomerItem->getValue('itemDescription');
                }

                $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, $itemDescription);
                $this->_buPDF->printStringAt(BUPDFSUPPORT_SERIAL_NO_COL - 30, $dsCustomerItem->getValue('serialNo'));
                $this->_buPDF->printStringRJAt(BUPDFSUPPORT_PURCHASE_DATE, Controller::dateYMDtoDMY($dsCustomerItem->getValue('despatchDate')));
                $this->_buPDF->CR();
            } while ($dsCustomerItem->fetchNext());
        }


        if ($dsContract->getValue('users') > 0) {
            $this->_buPDF->CR();
            $this->_buPDF->setFontSize(10);
            $this->_buPDF->setFont();
            $this->_buPDF->CR();
            $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Number of supported users: ' . $dsContract->getValue('users'));
            $this->_buPDF->setFontSize(10);
            $this->_buPDF->setFont();
        }

        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Contracted Response Times');
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'SLA - ' . $this->_buActivity->priorityArray[1]);
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL + 60, $this->_dsCustomer->getValue('slaP1') . ' hour(s)');
        $this->_buPDF->CR();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'SLA - ' . $this->_buActivity->priorityArray[2]);
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL + 60, $this->_dsCustomer->getValue('slaP2') . ' hour(s)');
        $this->_buPDF->CR();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'SLA - ' . $this->_buActivity->priorityArray[3]);
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL + 60, $this->_dsCustomer->getValue('slaP3') . ' hour(s)');
        $this->_buPDF->CR();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'SLA - ' . $this->_buActivity->priorityArray[4]);
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL + 60, $this->_dsCustomer->getValue('slaP4') . ' hour(s)');
        $this->_buPDF->CR();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'SLA - ' . $this->_buActivity->priorityArray[5]);
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL + 60, 'N/A');
        $this->_buPDF->CR();

        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Hours of Support:');

        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();

        $hoursOfSupport = '';

        if ($this->_dsCustomer->getValue('support24HourFlag') == 'Y') {
            $hoursOfSupport = '24 x 7 for Severe Impact Incidents or ';
        }

        $hoursOfSupport .= 'Monday to Friday';

        if ($this->_customerHasServiceDeskContract) {
            $hoursOfSupport .= ' 7:30am to 8:00pm';
        } else {
            $hoursOfSupport .= ' 8:30am to 6:00pm';
        }

        if ($this->_dsCustomer->getValue('support24HourFlag') == 'Y') {
            $hoursOfSupport .= ' for all others.
';
        }
        $hoursOfSupport .= '.';

        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, $hoursOfSupport);
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();


        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->moveYTo($this->_titleLine + (BUPDFSUPPORT_NUMBER_OF_LINES * $this->_buPDF->getFontSize() / 2));
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->printStringAt(10, 'This Contract Schedule forms part of an Agreement between the Parties and both Parties agree to be bound by the Terms & Conditions attached or available on the CNC web site.');
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $this->_buPDF->placeImageAt($GLOBALS['cfg']['cncaddress_path'], 'PNG', 6, 200);

        $this->_buPDF->endPage();
    }

    /**
     *    Output the header.
     * This gets called once at the start of each page.
     * Where a statement spans pages it gets called many times for the same statement.
     *
     * @access private
     */
    function noteHead()
    {
        $dsContract = &$this->_dsContract;
        $dsSite = &$this->_dsSite;
        $this->_buPDF->startPage();
        $this->_buPDF->placeImageAt($GLOBALS['cfg']['cnclogo_path'], 'PNG', 142, 38);
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
        $this->_buPDF->printString('Contract Schedule');
        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $this->_buPDF->CR();
        $firstAddLine = $this->_buPDF->getYPos();    // remember this line no
        $this->_buPDF->printString($dsContract->getValue('customerName'));
        $this->_buPDF->CR();
        $this->_buPDF->setFontSize(8);
        $this->_buPDF->setFont();
        $this->_buPDF->printString($dsSite->getValue(DBESite::add1));
        if ($dsSite->getValue(DBESite::add2) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($dsSite->getValue(DBESite::add2));
        }
        if ($dsSite->getValue(DBESite::add3) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($dsSite->getValue(DBESite::add3));
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($dsSite->getValue(DBESite::town));
        if ($dsSite->getValue(DBESite::county) != '') {
            $this->_buPDF->CR();
            $this->_buPDF->printString($dsSite->getValue(DBESite::county));
        }
        $this->_buPDF->CR();
        $this->_buPDF->printString($dsSite->getValue(DBESite::postcode));
        $this->_buPDF->CR();
        $this->_buPDF->CR();

        $this->_buPDF->setFontSize(10);
        $this->_buPDF->setFont();
        $this->_buPDF->moveYTo($firstAddLine);    //move back up the page
        $this->_buPDF->CR();
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_HEADING_DESC_COL, 'ServiceDesk Number');
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, CONFIG_IT_SUPPORT_PHONE);
        $this->_buPDF->CR();
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),
            BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_HEADING_DESC_COL, 'Email');
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, CONFIG_SUPPORT_EMAIL);
        $this->_buPDF->CR();
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_HEADING_DESC_COL, 'Start Date');
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, Controller::dateYMDtoDMY($dsContract->getValue('installationDate')));
        $this->_buPDF->CR();
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();

        if ($dsContract->getValue('itemID') != CONFIG_DEF_PREPAY_ITEMID) {
            $this->_buPDF->printStringRJAt(BUPDFSUPPORT_HEADING_DESC_COL, 'Billing Period');
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $dsContract->getValue('invoicePeriodMonths') . ' month(s)');
        }

        $this->_buPDF->CR();
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);

        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_HEADING_DESC_COL, 'Contract Reference');
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->printStringAt(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $dsContract->getValue('customerItemID'));
        $this->_buPDF->CR();

        // show contract price if not pre-pay otherwise empty box
        $this->_buPDF->box(BUPDFSUPPORT_SERIAL_NO_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);

        $itemID = $dsContract->getValue('itemID');
        $this->_renewalTypeID = $dsContract->getValue('renewalTypeID');

        if ($itemID != CONFIG_DEF_PREPAY_ITEMID) {
            /*
            Calculate annual price depending upon type
            */
            switch ($this->_renewalTypeID) {

                case(CONFIG_BROADBAND_RENEWAL_TYPE_ID):
                    $annualPrice = $dsContract->getValue('salePricePerMonth') * 12;
                    break;

                case(CONFIG_DOMAIN_RENEWAL_TYPE_ID):
                    $dbeItem = new DBEItem($this);
                    $dbeItem->getRow($itemID);
                    $annualPrice = $dbeItem->getValue('curUnitSale') * 12;
                    break;

                case(CONFIG_CONTRACT_RENEWAL_TYPE_ID):
                    $annualPrice = $dsContract->getValue('curUnitSale');
                    break;

                case(CONFIG_HOSTING_RENEWAL_TYPE_ID):
                    $annualPrice = $dsContract->getValue('curUnitSale');
                    break;
            }

            $this->_buPDF->setBoldOn();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringRJAt(
                BUPDFSUPPORT_HEADING_DESC_COL,
                'Annual Price'
            );
            $this->_buPDF->setBoldOff();
            $this->_buPDF->setFont();
            $this->_buPDF->printStringAt(
                BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE,
                POUND_CHAR . $annualPrice . ' + VAT'


            );
        }
        $this->_buPDF->box(BUPDFSUPPORT_PURCHASE_DATE_BOX_LEFT_EDGE, $this->_buPDF->getYPos(), BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH, $this->_buPDF->getFontSize() / 2);
        $this->_buPDF->CR();

        $this->_titleLine = $this->_buPDF->getYPos();
        $this->_buPDF->setBoldOn();
        $this->_buPDF->setFont();
        // box around all detail headings
        $this->_buPDF->box(
            BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFSUPPORT_DETAILS_BOX_WIDTH + (BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH * 2),
            $this->_buPDF->getFontSize() / 2
        );
        // Around details
        $this->_buPDF->box(
            BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE,
            $this->_buPDF->getYPos(),
            BUPDFSUPPORT_DETAILS_BOX_WIDTH,
            (BUPDFSUPPORT_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        // Box around the serial no
        $this->_buPDF->box(
            BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE + BUPDFSUPPORT_DETAILS_BOX_WIDTH,
            $this->_buPDF->getYPos(),
            BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH,
            (BUPDFSUPPORT_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        // Box around the purchase date
        $this->_buPDF->box(
            BUPDFSUPPORT_DETAILS_BOX_LEFT_EDGE + BUPDFSUPPORT_DETAILS_BOX_WIDTH + BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH,
            $this->_buPDF->getYPos(),
            BUPDFSUPPORT_SERIAL_NO_BOX_WIDTH,
            (BUPDFSUPPORT_NUMBER_OF_LINES) * ($this->_buPDF->getFontSize() / 2)
        );
        $this->_buPDF->printStringAt(BUPDFSUPPORT_DETAILS_COL, 'Details');
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_SERIAL_NO_COL - 14, 'Serial No');
        $this->_buPDF->printStringRJAt(BUPDFSUPPORT_PURCHASE_DATE + 8, 'Purchase Date');
        $this->_buPDF->setBoldOff();
        $this->_buPDF->setFont();
        $this->_buPDF->CR();
        $grandTotal = 0;
    }
}// End of class
?>