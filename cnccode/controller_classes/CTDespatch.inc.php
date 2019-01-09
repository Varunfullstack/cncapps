<?php
/**
 * Despatch controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUDespatch.inc.php');
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
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
    'CTDESPATCH_MSG_SEQNO_NOT_PASSED',
    'sequence no not passed'
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
    var $buSalesOrder = '';
    var $dsOrdhead = '';
    var $dsOrdline = '';
    var $dsDespatch = '';
    var $deliveryMethodID = '';
    var $orderTypeArray = array(
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

    function setDeliveryMethodID($ID)
    {
        $this->deliveryMethodID = $ID;
    }

    function getDeliveryMethodID()
    {
        return $this->deliveryMethodID;
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
        switch ($_REQUEST['action']) {
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
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
        // remove trailing spaces from params passed
        foreach ($_REQUEST as $key => $value) {
            $_REQUEST[$key] = trim($value);
        }
        if (($_REQUEST['ordheadID'] != '') AND (!is_numeric($_REQUEST['ordheadID']))) {
            $this->setFormErrorMessage('Order no must be numeric');;
        }
        if ($this->getFormError() == 0) {
            $buDespatch = new BUDespatch($this);
            $buDespatch->search(
                $_REQUEST['customerID'],
                $_REQUEST['ordheadID'],
                $this->dsOrdhead
            );
        }
        if ($this->dsOrdhead->rowCount() == 1) {
            $this->dsOrdhead->fetchNext();
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                        'ordheadID' => $this->dsOrdhead->getValue('ordheadID')
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
        $submitURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $clearURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $urlCustomerPopup =
            $this->buildLink(
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
            $typeCol = $this->dsOrdhead->columnExists('type');
            $customerNameCol = $this->dsOrdhead->columnExists('customerName');
            $ordheadIDCol = $this->dsOrdhead->columnExists('ordheadID');
            $quotationOrdheadIDCol = $this->dsOrdhead->columnExists('quotationOrdheadID');
            $custPORefCol = $this->dsOrdhead->columnExists('custPORef');
            $dateCol = $this->dsOrdhead->columnExists('date');
            while ($this->dsOrdhead->fetchNext()) {
                $orderURL =
                    $this->buildLink(
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
// search parameter section
        if ($_REQUEST['customerID'] != '') {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID(
                $_REQUEST['customerID'],
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerString'   => $customerString,
                'ordheadID'        => $_REQUEST['ordheadID'],
                'customerID'       => $_REQUEST['customerID'],
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
     * Display the results of order search
     * @access private
     */
    function displayDespatch()
    {
        $this->setMethodName('displayDespatch');
        $buDespatch = new BUDespatch($this);
        $buSalesOrder = new BUSalesOrder($this);
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        if ($_REQUEST['ordheadID'] == '') {
            $this->displayFatalError(CTDESPATCH_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        $buSalesOrder->getOrdheadByID(
            $_REQUEST['ordheadID'],
            $dsOrdhead
        );
        $dsOrdhead->fetchNext();
        $buDespatch->getLinesByID(
            $dsOrdhead->getValue('ordheadID'),
            $dsOrdline
        );
        if (!$this->getFormError()) {
            $buDespatch->getInitialDespatchQtys(
                $dsOrdline,
                $this->dsDespatch
            );
        }
        $ordheadID = $dsOrdhead->getValue('ordheadID');
        $orderType = $dsOrdhead->getValue('type');
        $this->setPageTitle('Sales Order Despatch');
        $this->setTemplateFiles(
            array(
                'DespatchDisplay'      => 'DespatchDisplay.inc',
                'DespatchDisplayNotes' => 'DespatchDisplayNotes.inc'
            )
        );
        // this is for handling F5 Toggle
        //$this->template->set_var('onKeyPress', 'onKeyPress="keyPressHandler();"');
        $urlDespatch =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTDESPATCH_ACT_DESPATCH,
                    'ordheadID' => $ordheadID
                )
            );
        $urlHome =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTDESPATCH_ACT_DISP_SEARCH
                )                                                                                                                    // remaining POs for SO
            );
        $urlSalesOrder =
            $this->buildLink(
                CTCNC_PAGE_SALESORDER,
                array(
                    'action'    => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $ordheadID
                )                                                                                                                    // remaining POs for SO
            );

        if ($buDespatch->countNonReceievedPOsByOrdheadID($ordheadID) > 0) {
            $this->template->set_var(
                'poNotRecd',
                'WARNING: Not all of the purchase orders have been receieved'
            );
        }

        /*
                if ( $countRenewalLines > 0 ){

                    $renewalsButton = '<input type="submit" name="Renewals" value="Renewals">';

                }
                else{
                    $renewalsButton = '';
                }
        */
        $this->template->set_var(
            array(
                'ordheadID'     => $ordheadID,
                'urlDespatch'   => $urlDespatch,
                'urlSalesOrder' => $urlSalesOrder,
                'urlHome'       => $urlHome//,
                //				'renewalsButton' => $renewalsButton
            )
        );
        // despatch method
        $buDespatch->getAllDeliveryMethods($dsDeliveryMethod);
        $this->template->set_block(
            'DespatchDisplay',
            'deliveryMethodBlock',
            'deliveryMethods'
        );
        while ($dsDeliveryMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'deliveryMethodDescription' => $dsDeliveryMethod->getValue('description'),
                    'deliveryMethodID'          => $dsDeliveryMethod->getValue('deliveryMethodID'),
                    'deliveryMethodSelected'    => ($this->getDeliveryMethodID() == $dsDeliveryMethod->getValue(
                            'deliveryMethodID'
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
                'deliveryMethodDescription' => $dsDeliveryMethod->getValue('description')
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

                /*
                 * renewals edit icon
                 */
                if ($dsOrdline->getValue('renewalTypeID')) {

                    if (!$buRenewal) {

                        $buRenewal = new BURenewal($this);

                    }

                    $buRenewalObject =
                        $buRenewal->getRenewalBusinessObject(
                            $dsOrdline->getValue('renewalTypeID'),
                            $page
                        );


                    $urlEditRenewal =
                        $this->buildLink(
                            $page,
                            array(
                                'action'     => 'editFromSalesOrder',
                                'ordheadID'  => $dsOrdhead->getValue('ordheadID'),
                                'sequenceNo' => $dsOrdline->getValue("sequenceNo")
                            )
                        );

                    $renewalLink =
                        '<A HREF="' . $urlEditRenewal . ' " target="_BLANK" title="Edit renewal information"><IMG src="images/renew_new.png" height="15" border="0"></A>';

                } else {
                    $renewalLink = '';
                }

                $this->template->set_var(
                    array(
                        'description' => Controller::htmlDisplayText($dsOrdline->getValue("description")),
                        'sequenceNo'  => $dsOrdline->getValue('sequenceNo')
                    )
                );
                if ($dsOrdline->getValue("lineType") != "I") {                    // Comment line
                    $this->template->set_var(
                        array(
                            'stockcat'           => '',
                            'qtyOrdered'         => '',
                            'qtyOutstanding'     => '',
                            'renewalLink'        => '',
                            'qtyOutstandingHide' => '1.0',
                            'qtyToDespatch'      => $this->dsDespatch->getValue("qtyToDespatch"),
                            'orderLineClass'     => CTDESPATCH_CLS_ORDER_LINE_COMMENT
                        )
                    );
                } else {
                    // Item line
                    $this->template->set_var(
                        array(
                            'stockcat'           => $dsOrdline->getValue("stockcat"),
                            'qtyOrdered'         => number_format(
                                $dsOrdline->getValue("qtyOrdered"),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOutstanding'     => number_format(
                                $dsOrdline->getValue("qtyOrdered") - $dsOrdline->getValue("qtyDespatched"),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOutstandingHide' => number_format(
                                $dsOrdline->getValue("qtyOrdered") - $dsOrdline->getValue("qtyDespatched"),
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


    /**
     * Perform despatch
     * @access private
     */
    function despatch()
    {
        $buDespatch = new BUDespatch($this);
        $buSalesOrder = new BUSalesOrder($this);
        $dsDespatch = &$this->dsDespatch;
        if ($_REQUEST['deliveryMethodID'] == '') {
            $this->setFormErrorMessage('Please select a delivery method');
        }


        $this->setDeliveryMethodID($_REQUEST['deliveryMethodID']);
        $buDespatch->initialiseDespatchDataset($dsDespatch);

        if (!$dsDespatch->populateFromArray($_REQUEST['despatch'])) {
            $this->setFormErrorMessage('Quantitites entered must be numeric');
        }
//		$hasRenewalsLines = false;
        $forciblyCreateNote = isset($_REQUEST['forciblyCreateNote']);

        $dsDespatch->initialise();
        while ($dsDespatch->fetchNext()) {
            $buSalesOrder->getOrdlineByIDSeqNo(
                $_REQUEST['ordheadID'],
                $dsDespatch->getValue('sequenceNo'),
                $dsOrdline
            );
            if ($dsOrdline->getValue('lineType') != 'C') {
                if (
                    $dsDespatch->getValue('qtyToDespatch') >
                    ($dsOrdline->getValue('qtyOrdered') - $dsOrdline->getValue('qtyDespatched'))
                ) {
                    $this->setFormErrorMessage('Quantitites must not exceed outstanding');
                }
            }
            /*
             * Validate that renewals have been created and minimum information has been entered
             */
            if ($dsOrdline->getValue('renewalTypeID') > 0) {

                if (!$forciblyCreateNote && !$dsOrdline->getValue('renewalCustomerItemID')) {

                    $this->setFormErrorMessage('You have not created all of the renewals');

                } else {
                    if (!$buRenewal) {

                        $buRenewal = new BURenewal($this);

                    }
                    $buRenewalObject =
                        $buRenewal->getRenewalBusinessObject(
                            $dsOrdline->getValue('renewalTypeID'),
                            $page
                        );

                    if (!$forciblyCreateNote && !$buRenewalObject->isCompleted(
                            $dsOrdline->getValue('renewalCustomerItemID')
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
                $_REQUEST['onlyCreateDespatchNote']
            );
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                        'ordheadID' => $_REQUEST['ordheadID']
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * Display PDF delivery Note
     */
    function displayNoteDoc()
    {
        $this->setMethodName('displayNoteDoc');
        if ($_REQUEST['deliveryNoteID'] == '') {
            $this->displayFatalError(CTDESPATCH_MSG_DELIVERYNOTEID_NOT_PASSED);
            return;
        }
        $buDespatch = new BUDespatch($this);
        $buDespatch->getDeliveryNoteByID(
            $_REQUEST['deliveryNoteID'],
            $dsDeliveryNote
        );
        $dsDeliveryNote->fetchNext();
        $fileName = $dsDeliveryNote->getValue('ordheadID') . '_' . $dsDeliveryNote->getValue('noteNo') . '.pdf';
        $pdfFile = DELIVERY_NOTES_DIR . '/' . $fileName;
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
}// end of class
?>