<?php
/**
 * Quote renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBERenQuotationType.inc.php');

class CTRenQuotation extends CTCNC
{
    var $dsRenQuotation = '';
    var $buRenQuotation = '';
    var $buCustomerItem = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "renewals"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRenQuotation = new BURenQuotation($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenQuotation = new DSForm($this);
        $this->dsRenQuotation->copyColumnsFrom($this->buRenQuotation->dbeRenQuotation);
        $this->dsRenQuotation->addColumn('invoiceFromDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('invoiceToDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('itemID', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('customerName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('nextPeriodStartDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('nextPeriodEndDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('itemDescription', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('siteName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('costPrice', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenQuotation->addColumn('salePrice', DA_STRING, DA_ALLOW_NULL);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'edit':
            case 'create':
                $this->edit();
                break;
            case 'editFromSalesOrder':
                $this->editFromSalesOrder();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'update':
                $this->update();
                break;
            case 'createRenewalsQuotations':
                $this->createRenewalsQuotations();
                break;

            case 'list':
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Renewals');

        $this->setTemplateFiles(
            array('RenQuotationList' => 'RenQuotationList.inc')
        );

        $this->buRenQuotation->getAll($dsRenQuotation, $_REQUEST['orderBy']);

        if ($dsRenQuotation->rowCount() > 0) {
            $this->template->set_block('RenQuotationList', 'rowBlock', 'rows');

            while ($dsRenQuotation->fetchNext()) {

                $customerItemID = $dsRenQuotation->getValue('customerItemID');

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID' => $customerItemID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'delete',
                            'customerItemID' => $customerItemID
                        )
                    );

                $urlList =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list'
                        )
                    );

                $urlCreateRenewalsQuotations =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'createRenewalsQuotations'
                        )
                    );

                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'customerName' => $dsRenQuotation->getValue('customerName'),
                        'itemDescription' => $dsRenQuotation->getValue('itemDescription'),
                        'type' => $dsRenQuotation->getValue('type'),
                        'startDate' => $this->dateYMDtoDMY($dsRenQuotation->getValue('startDate')),
                        'nextPeriodStartDate' => $this->dateYMDtoDMY($dsRenQuotation->getValue('nextPeriodStartDate')),
                        'nextPeriodEndDate' => $this->dateYMDtoDMY($dsRenQuotation->getValue('nextPeriodEndDate')),
                        'urlEdit' => $urlEdit,
                        'urlList' => $urlList,
                        'urlCreateRenewalsQuotations' => $urlCreateRenewalsQuotations,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse('rows', 'rowBlock', true);
            }//while $dsRenQuotation->fetchNext()
        }
        $this->template->parse('CONTENTS', 'RenQuotationList', true);
        $this->parsePage();
    }

    /**
     * Called from sales order line to edit a renewal.
     * The page passes
     * ordheadID
     * sequenceNo (line)
     * renewalCustomerItemID (blank if renewal not created yet
     *
     *
     */
    function editFromSalesOrder()
    {
        $buSalesOrder = new BUSalesOrder($this);

        $buSalesOrder->getOrdlineByIDSeqNo(
            $_REQUEST['ordheadID'],
            $_REQUEST['sequenceNo'],
            $dsOrdline
        );

        $renewalCustomerItemID = $dsOrdline->getValue('renewalCustomerItemID');

        // has the order line get a renewal already?
        if (!$renewalCustomerItemID) {

            // create a new record first

            $buSalesOrder->getOrderByOrdheadID($_REQUEST['ordheadID'], $dsOrdhead, $dsDontNeedOrdline);

            $ID = $this->buRenQuotation->createNewRenewal(
                $dsOrdhead->getValue('customerID'),
                $dsOrdhead->getValue('delSiteNo'),
                $dsOrdline->getValue('itemID'),
                $renewalCustomerItemID,                // returned by function
                $dsOrdline->getValue('curUnitSale'),
                $dsOrdline->getValue('curUnitCost'),
                $dsOrdline->getValue('qtyOrdered')
            );


            // For despatch, prevents the renewal appearing again today during despatch process.
            $dbeOrdline = new DBEOrdline($this);

            $dbeOrdline->setValue('ordheadID', $dsOrdline->getValue('ordheadID'));
            $dbeOrdline->setValue('sequenceNo', $dsOrdline->getValue('sequenceNo'));

            $dbeOrdline->getRow();
            $dbeOrdline->setValue('renewalCustomerItemID', $renewalCustomerItemID);

            $dbeOrdline->updateRow();

        }

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'ID' => $renewalCustomerItemID
                )
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Edit/Add Activity
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRenQuotation = &$this->dsRenQuotation; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buRenQuotation->getRenQuotationByID($_REQUEST['ID'], $dsRenQuotation);
                $customerItemID = $_REQUEST['ID'];
            } else {                                                                    // creating new
                $dsRenQuotation->initialise();
                $dsRenQuotation->setValue('customerItemID', '0');
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenQuotation->initialise();
            $dsRenQuotation->fetchNext();
            $customerItemID = $dsRenQuotation->getValue('customerItemID');
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'update',
                    'ordheadID' => $_REQUEST['ordheadID'],
                    'customerItemID' => $customerItemID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'list'
                )
            );
        $this->setPageTitle('Edit Renewal');
        $this->setTemplateFiles(
            array('RenQuotationEdit' => 'RenQuotationEdit.inc')
        );

        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $disabled = ''; // not
            $readonly = '';
            $this->template->set_var(
                array(
                    'salePrice' => Controller::htmlDisplayText($dsRenQuotation->getValue('salePrice')),
                    'costPrice' => Controller::htmlDisplayText($dsRenQuotation->getValue('costPrice'))
                )
            );
        } else {
            $disabled = CTCNC_HTML_DISABLED;
            $readonly = 'READONLY';
            $urlEditCustomerItem = '#';
        }
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_QUOTATION_RENEWAL_TYPE_ID,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


        $this->template->set_var(
            array(
                'customerID' => Controller::htmlDisplayText($dsRenQuotation->getValue('customerID')),
                'siteName' => Controller::htmlDisplayText($dsRenQuotation->getValue('siteName')),
                'siteNo' => $dsRenQuotation->getValue('siteNo'),
                'itemID' => Controller::htmlDisplayText($dsRenQuotation->getValue('itemID')),
                'customerItemID' => $dsRenQuotation->getValue('customerItemID'),
                'customerName' => Controller::htmlDisplayText($dsRenQuotation->getValue('customerName')),
                'itemDescription' => Controller::htmlDisplayText($dsRenQuotation->getValue('itemDescription')),
                'startDate' => $this->dateYMDtoDMY($dsRenQuotation->getValue('startDate')),
                'dateGenerated' => $this->dateYMDtoDMY($dsRenQuotation->getValue('dateGenerated')),
                'dateGeneratedMessage' => $dsRenQuotation->getMessage('dateGenerated'),
                'grantNumber' => Controller::htmlDisplayText($dsRenQuotation->getValue('grantNumber')),
                'serialNo' => Controller::htmlDisplayText($dsRenQuotation->getValue('serialNo')),
                'qty' => Controller::htmlDisplayText($dsRenQuotation->getValue('qty')),
                'declinedFlagChecked' => Controller::htmlChecked($dsRenQuotation->getValue('declinedFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlItemEdit' => $urlItemEdit,
                'urlItemPopup' => $urlItemPopup,
                'urlDisplayList' => $urlDisplayList,
                'disabled' => $disabled,
                'internalNotes' => Controller::htmlTextArea($dsRenQuotation->getValue('internalNotes')),
                'readonly' => $readonly
            )
        );
        $dbeRenQuotationType = new DBERenQuotationType($this);

        $dbeRenQuotationType->getRows();

        $this->template->set_block('RenQuotationEdit', 'typeBlock', 'types');

        while ($dbeRenQuotationType->fetchNext()) {

            $typeSelected = ($dsRenQuotation->getValue('renQuotationTypeID') == $dbeRenQuotationType->getValue('renQuotationTypeID')) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'typeSelected' => $typeSelected,
                    'renQuotationTypeID' => $dbeRenQuotationType->getPKValue(),
                    'typeDescription' => $dbeRenQuotationType->getValue('description')
                )
            );
            $this->template->parse('types', 'typeBlock', true);
        }

        $this->template->parse('CONTENTS', 'RenQuotationEdit', true);

        $this->parsePage();

    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsRenQuotation = &$this->dsRenQuotation;

        $this->formError = (!$this->dsRenQuotation->populateFromArray($_REQUEST['renQuotation']));

        if ($this->formError) {

            if ($this->dsRenQuotation->getValue('customerItemID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buRenQuotation->updateRenQuotation($this->dsRenQuotation);

        if ($_REQUEST['ordheadID'] == 1) {        // see whether more renewals need to be edited for this
            // despatch
            $urlNext =
                $this->buildLink(
                    'Despatch',
                    array(
                        'action' => 'inputRenewals',
                        'ID' => $_REQUEST['ordheadID']
                    )
                );

        } else {
            $urlNext =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => 'edit',
                                     'ID' => $this->dsRenQuotation->getValue('customerItemID')
                                 )
                );

        }

        header('Location: ' . $urlNext);
    }

    /**
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buRenQuotation->deleteRenQuotation($_REQUEST['customerItemID'])) {
            $this->displayFatalError('Cannot delete this quote renewal');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'list'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * This function creates sales orders for the quotation renewals that are due
     *
     */
    function createRenewalsQuotations()
    {
        if (
            ($_SERVER['REQUEST_METHOD'] == 'POST') &&
            isset($_REQUEST['customerItemIDs'])
        ) {
            $this->buRenQuotation->createRenewalsQuotations(
                $_REQUEST['customerItemIDs']
            );
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {

            $this->buRenQuotation->createRenewalsQuotations();

        }

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'list'
                )
            );
        header('Location: ' . $urlNext);
        exit;

    }
}// end of class
?>