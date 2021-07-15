<?php
/**
 * Despatch controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUDespatch.inc.php');
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BUPDFDeliveryNote.inc.php");
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_ct'] . '/CTDeliveryNotes.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define(
    'CTDESPATCH_MSG_PURCHASEORDER_NOT_FND',
    'Purchase Order not found'
);
define(
    'CTDESPATCH_MSG_ORDHEADID_NOT_PASSED',
    'ordheadID not passed'
);
define(
    'CTDESPATCH_MSG_DELIVERYNOTEID_NOT_PASSED',
    'deliveryNoteID not passed'
);
define(
    'CTDESPATCH_MSG_SUPPLIERID_NOT_PASSED',
    'supplierID not passed'
);
define(
    'CTDESPATCH_MSG_MUST_BE_INITIAL',
    'Must be an initial order'
);
define(
    'CTDESPATCH_MSG_ORDLINE_NOT_FND',
    'order line not found'
);
// Actions
define(
    'CTDESPATCH_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTDESPATCH_ACT_DESPATCH',
    'despatch'
);
// HTML CSS classes
define(
    'CTDESPATCH_CLS_ORDER_LINE_ITEM',
    'orderLineItem'
);
define(
    'CTDESPATCH_CLS_ORDER_LINE_COMMENT',
    'orderLineComment'
);

class CTDespatch extends CTCNC
{
    public $buSalesOrder;
    public $dsOrdhead;
    /** @var DSForm */
    public $dsOrdline;
    /** @var DSForm */
    public $dsDespatch;
    public $deliveryMethodID;
    public $orderTypeArray = array(
        "I" => "Initial",
        "Q" => "Quotation",
        "P" => "Part",
        "C" => "Completed",
        "B" => "Both Initial & Part Despatched"
    );

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            'sales'
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->dsOrdhead = new Dataset($this);
        $this->dsDespatch = new DSForm($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(TECHNICAL_PERMISSION);
        switch ($this->getAction()) {
            case CTCNC_ACT_DISPLAY_DESPATCH:
                $this->displayDespatch();
                break;
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTDESPATCH_ACT_DISP_SEARCH:
                $this->displaySearchForm();
                break;
            case CTDESPATCH_ACT_DESPATCH:
                $this->despatch();
                break;
            case CTCNC_ACT_DISPLAY_DEL_NOTE_DOC:
                $this->displayNoteDoc();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displayDespatch()
    {
        $this->setMethodName('displayDespatch');
        $buDespatch = new BUDespatch($this);
        $buSalesOrder = new BUSalesOrder($this);
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        if ($this->getParam('ordheadID') == '') {
            $this->displayFatalError(CTDESPATCH_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        $buSalesOrder->getOrdheadByID(
            $this->getParam('ordheadID'),
            $dsOrdhead
        );
        $dsOrdhead->fetchNext();
        $buDespatch->getLinesByID(
            $dsOrdhead->getValue(DBEOrdhead::ordheadID),
            $dsOrdline
        );
        if (!$this->getFormError()) {
            $buDespatch->getInitialDespatchQtys(
                $dsOrdline,
                $this->dsDespatch
            );
        }
        $ordheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        $this->setPageTitle('Sales Order Despatch');
        $this->setTemplateFiles(
            array(
                'DespatchDisplay'      => 'DespatchDisplay.inc',
                'DespatchDisplayNotes' => 'DespatchDisplayNotes.inc'
            )
        );
        $urlDespatch =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTDESPATCH_ACT_DESPATCH,
                    'ordheadID' => $ordheadID
                )
            );
        $urlHome =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTDESPATCH_ACT_DISP_SEARCH
                )                                                                                                                    // remaining POs for SO
            );
        $urlSalesOrder =
            Controller::buildLink(
                CTCNC_PAGE_SALESORDER,
                array(
                    'action'    => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $ordheadID
                )                                                                                                                    // remaining POs for SO
            );

        if ($buDespatch->countNonReceivedPOsByOrdheadID($ordheadID)) {
            $this->template->set_var(
                'poNotRecd',
                'WARNING: Not all of the purchase orders have been received'
            );
        }
        $this->template->set_var(
            array(
                'ordheadID'     => $ordheadID,
                'urlDespatch'   => $urlDespatch,
                'urlSalesOrder' => $urlSalesOrder,
                'urlHome'       => $urlHome
            )
        );
        // despatch method
        $dsDeliveryMethod = new DataSet($this);
        $buDespatch->getAllDeliveryMethods($dsDeliveryMethod);
        $this->template->set_block(
            'DespatchDisplay',
            'deliveryMethodBlock',
            'deliveryMethods'
        );

        $buRenewal = null;
        while ($dsDeliveryMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'deliveryMethodDescription' => $dsDeliveryMethod->getValue(DBEDeliveryMethod::description),
                    'deliveryMethodID'          => $dsDeliveryMethod->getValue(DBEDeliveryMethod::deliveryMethodID),
                    'deliveryMethodSelected'    => ($this->getDeliveryMethodID() == $dsDeliveryMethod->getValue(
                            DBEDeliveryMethod::deliveryMethodID
                        )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'deliveryMethods',
                'deliveryMethodBlock',
                true
            );
        }

        $this->template->set_var(
            array(
                'deliveryMethodDescription' => $dsDeliveryMethod->getValue(DBEDeliveryMethod::description)
            )
        );

        $dsOrdline->initialise();
        $this->dsDespatch->initialise();

        if ($dsOrdline->rowCount() > 0) {
            $this->template->set_block(
                'DespatchDisplay',
                'orderLineBlock',
                'orderLines'
            );
            while ($dsOrdline->fetchNext()) {
                $this->dsDespatch->fetchNext();
                $renewalLink = null;
                if ($dsOrdline->getValue(DBEJOrdline::renewalTypeID)) {
                    if (!$buRenewal) {
                        $buRenewal = new BURenewal($this);
                    }

                    $buRenewal->getRenewalBusinessObject(
                        $dsOrdline->getValue(DBEJOrdline::renewalTypeID),
                        $page
                    );

                    $urlEditRenewal =
                        Controller::buildLink(
                            $page,
                            array(
                                'action'    => 'editFromSalesOrder',
                                'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                                'lineId'    => $dsOrdline->getValue(DBEOrdline::id)
                            )
                        );

                    /** @noinspection HtmlDeprecatedAttribute */
                    $renewalLink =
                        '<A HREF="' . $urlEditRenewal . ' " target="_BLANK" title="Edit renewal information"><IMG src="images/renew_new.png" height="15" border="0"></A>';
                }

                $this->template->set_var(
                    array(
                        'description' => Controller::htmlDisplayText($dsOrdline->getValue(DBEOrdline::description)),
                        'lineId'      => $dsOrdline->getValue(DBEOrdline::id),
                    )
                );
                if ($dsOrdline->getValue(DBEOrdline::lineType) != "I") {                    // Comment line
                    $this->template->set_var(
                        array(
                            'stockcat'           => '',
                            'qtyOrdered'         => '',
                            'qtyOutstanding'     => '',
                            'renewalLink'        => '',
                            'qtyOutstandingHide' => '1.0',
                            'qtyToDespatch'      => $this->dsDespatch->getValue(BUDespatch::despatchQtyToDespatch),
                            'orderLineClass'     => CTDESPATCH_CLS_ORDER_LINE_COMMENT
                        )
                    );
                } else {
                    // Item line
                    $this->template->set_var(
                        array(
                            'stockcat'           => $dsOrdline->getValue(DBEOrdline::stockcat),
                            'qtyOrdered'         => number_format(
                                $dsOrdline->getValue(DBEOrdline::qtyOrdered),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOutstanding'     => number_format(
                                $dsOrdline->getValue(DBEOrdline::qtyOrdered) - $dsOrdline->getValue(
                                    DBEOrdline::qtyDespatched
                                ),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOutstandingHide' => number_format(
                                $dsOrdline->getValue(DBEOrdline::qtyOrdered) - $dsOrdline->getValue(
                                    DBEOrdline::qtyDespatched
                                ),
                                2,
                                '.',
                                ''
                            ),
                            'qtyToDespatch'      => 0,
                            'renewalLink'        => $renewalLink,
                            'orderLineClass'     => CTDESPATCH_CLS_ORDER_LINE_ITEM
                        )
                    );
                }
                $this->template->parse(
                    'orderLines',
                    'orderLineBlock',
                    true
                );
            }
        }
        $ctDeliveryNotes = new CTDeliveryNotes(
            $this,
            $ordheadID,
            $buDespatch
        );
        $ctDeliveryNotes->execute();

        $this->template->parse(
            'CONTENTS',
            'DespatchDisplay',
            true
        );
        $this->parsePage();
    }

    function getDeliveryMethodID()
    {
        return $this->deliveryMethodID;
    }

    function setDeliveryMethodID($ID)
    {
        $this->deliveryMethodID = $ID;
    }

    /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        // remove trailing spaces from params passed
        foreach ($_REQUEST as $key => $value) {
            $_REQUEST[$key] = trim($value);
        }
        if (($this->getParam('ordheadID') != '') and (!is_numeric($this->getParam('ordheadID')))) {
            $this->setFormErrorMessage('Order no must be numeric');
        }
        if ($this->getFormError() == 0) {
            $buDespatch = new BUDespatch($this);
            $buDespatch->search(
                $this->getParam('customerID'),
                $this->getParam('ordheadID'),
                $this->dsOrdhead
            );
        }
        if ($this->dsOrdhead->rowCount() == 1) {
            $this->dsOrdhead->fetchNext();
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                        'ordheadID' => $this->dsOrdhead->getValue(DBEOrdhead::ordheadID)
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        } else {
            $this->setAction(CTDESPATCH_ACT_DISP_SEARCH);
            $this->displaySearchForm();
        }
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'DespatchSearch',
            'DespatchSearch.inc'
        );
// Parameters
        $this->setPageTitle("Sales Order Despatch");
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $clearURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $urlCustomerPopup =
            Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->dsOrdhead->initialise();
        if ($this->dsOrdhead->rowCount() > 0) {
            $this->template->set_block(
                'DespatchSearch',
                'orderBlock',
                'orders'
            );
            $typeCol = $this->dsOrdhead->columnExists(DBEOrdhead::type);
            $customerNameCol = $this->dsOrdhead->columnExists(DBEJOrdhead::customerName);
            $ordheadIDCol = $this->dsOrdhead->columnExists(DBEOrdhead::ordheadID);
            $custPORefCol = $this->dsOrdhead->columnExists(DBEOrdhead::custPORef);
            $dateCol = $this->dsOrdhead->columnExists(DBEOrdhead::date);
            while ($this->dsOrdhead->fetchNext()) {
                $orderURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                            'ordheadID' => $this->dsOrdhead->getValue($ordheadIDCol)
                        )
                    );
                $customerName = $this->dsOrdhead->getValue($customerNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName' => $customerName,
                        'listOrderURL'     => $orderURL,
                        'listOrdheadID'    => $this->dsOrdhead->getValue($ordheadIDCol),
                        'listOrderType'    => $this->orderTypeArray[$this->dsOrdhead->getValue($typeCol)],
                        'listCustPORef'    => $this->dsOrdhead->getValue($custPORefCol),
                        'listOrderDate'    => Controller::dateYMDtoDMY($this->dsOrdhead->getValue($dateCol))
                    )
                );
                $this->template->parse(
                    'orders',
                    'orderBlock',
                    true
                );
            }
        }

        $customerString = null;
        if ($this->getParam('customerID') && $this->getParam('customerID')) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $this->getParam('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerString'   => $customerString,
                'ordheadID'        => $this->getParam('ordheadID'),
                'customerID'       => $this->getParam('customerID'),
                'submitURL'        => $submitURL,
                'clearURL'         => $clearURL,
                'urlCustomerPopup' => $urlCustomerPopup
            )
        );
        $this->template->parse(
            'CONTENTS',
            'DespatchSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Perform despatch
     * @access private
     * @throws Exception
     */
    function despatch()
    {
        $buDespatch = new BUDespatch($this);
        $buSalesOrder = new BUSalesOrder($this);
        $dsDespatch = &$this->dsDespatch;
        if ($this->getParam('deliveryMethodID') == '') {
            $this->setFormErrorMessage('Please select a delivery method');
        }

        $this->setDeliveryMethodID($this->getParam('deliveryMethodID'));
        $buDespatch->initialiseDespatchDataset($dsDespatch);
        if (!$dsDespatch->populateFromArray($this->getParam('despatch'))) {
            $this->setFormErrorMessage('Quantities entered must be numeric');
        }
        $forciblyCreateNote = $this->getParam('forciblyCreateNote');
        $dsDespatch->initialise();
        $buRenewal = null;
        while ($dsDespatch->fetchNext()) {
            $dsOrdline = new DBEJOrdline($this);
            $dsOrdline->getRow($dsDespatch->getValue(BUDespatch::despatchLineId));

            if ($dsOrdline->getValue(DBEOrdline::lineType) != 'C' && $dsDespatch->getValue(
                    BUDespatch::despatchQtyToDespatch
                ) >
                ($dsOrdline->getValue(DBEOrdline::qtyOrdered) - $dsOrdline->getValue(DBEOrdline::qtyDespatched))) {
                $this->setFormErrorMessage('Quantities must not exceed outstanding');
            }
            /*
             * Validate that renewals have been created and minimum information has been entered
             */
            if ($dsOrdline->getValue(DBEJOrdline::renewalTypeID) > 0) {

                if (!$forciblyCreateNote && !$dsOrdline->getValue(DBEOrdline::renewalCustomerItemID)) {

                    $this->setFormErrorMessage('You have not created all of the renewals');

                } else {
                    if (!$buRenewal) {
                        $buRenewal = new BURenewal($this);
                    }
                    $buRenewalObject =
                        $buRenewal->getRenewalBusinessObject(
                            $dsOrdline->getValue(DBEJOrdline::renewalTypeID),
                            $page
                        );

                    if (!$forciblyCreateNote && !$buRenewalObject->isCompleted(
                            $dsOrdline->getValue(DBEOrdline::renewalCustomerItemID)
                        )) {

                        $this->setFormErrorMessage('You have not completed all of the renewal information required');

                    }

                } // end else

            } // end if is a renewal line
        }

        if ($this->getformError()) {
            $this->displayDespatch();
            exit;
        } else {
            $deliveryNoteFile = $buDespatch->despatch(
                $_REQUEST['ordheadID'],
                $_REQUEST['deliveryMethodID'],
                $dsDespatch,
                @$_REQUEST['onlyCreateDespatchNote']
            );
        }
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                    'ordheadID' => $this->getParam('ordheadID')
                )
            );
        header('Location: ' . $urlNext);
        exit;

    }

    /**
     * Display PDF delivery Note
     */
    function displayNoteDoc()
    {
        $this->setMethodName('displayNoteDoc');
        if ($this->getParam('deliveryNoteID') == '') {
            $this->displayFatalError(CTDESPATCH_MSG_DELIVERYNOTEID_NOT_PASSED);
            return;
        }
        $buDespatch = new BUDespatch($this);
        $dsDeliveryNote = new DataSet($this);
        $buDespatch->getDeliveryNoteByID(
            $this->getParam('deliveryNoteID'),
            $dsDeliveryNote
        );
        $dsDeliveryNote->fetchNext();
        $fileName = $dsDeliveryNote->getValue(DBEDeliveryNote::ordheadID) . '_' . $dsDeliveryNote->getValue(
                DBEDeliveryNote::noteNo
            ) . '.pdf';
        $pdfFile = DELIVERY_NOTES_DIR . $fileName;
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=" . $fileName . ";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($pdfFile));
        readfile($pdfFile);
        exit();
    }
}
