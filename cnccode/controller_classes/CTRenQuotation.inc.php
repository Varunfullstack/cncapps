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
            "renewals",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRenQuotation = new BURenQuotation($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenQuotation = new DSForm($this);
        $this->dsRenQuotation->copyColumnsFrom($this->buRenQuotation->dbeRenQuotation);
        $this->dsRenQuotation->addColumn(
            'invoiceFromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'invoiceToDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'itemID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'customerName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'nextPeriodStartDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'nextPeriodEndDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'itemDescription',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'siteName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'costPrice',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            'salePrice',
            DA_STRING,
            DA_ALLOW_NULL
        );
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

        if (!isset($_REQUEST['orderBy'])) {
            header(
                'Location: ' . Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => 'list',
                        'orderBy'        => 'customerName',
                        'orderDirection' => 'asc'
                    )
                )
            );
        }

        $this->buRenQuotation->getAll(
            $dsRenQuotation,
            $_REQUEST['orderBy']
        );

        if ($dsRenQuotation->rowCount() > 0) {
            $this->template->set_block(
                'RenQuotationList',
                'rowBlock',
                'rows'
            );

            while ($dsRenQuotation->fetchNext()) {

                $customerItemID = $dsRenQuotation->getValue('customerItemID');

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID'     => $customerItemID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => 'delete',
                            'customerItemID' => $customerItemID
                        )
                    );

                $urlList =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list'
                        )
                    );


                $txtDelete = '[delete]';

                $latestQuoteSent = null;

                if ($dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent) && $dsRenQuotation->getValue(
                        DBEJRenQuotation::latestQuoteSent
                    ) != '0000-00-00 00:00:00') {
                    $latestQuoteSent = DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $dsRenQuotation->getValue(
                            DBEJRenQuotation::latestQuoteSent
                        )
                    );
                }

                $salesOrderLink = null;
                $sent = false;
                if ($dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent) && $latestQuoteSent) {
                    $sent = true;
                }
                if ($dsRenQuotation->getValue(DBEJRenQuotation::ordheadID)) {
                    $salesOrderURL = Controller::buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => 'displaySalesOrder',
                            'ordheadID' => $dsRenQuotation->getValue(DBEJRenQuotation::ordheadID)
                        )
                    );

                    $salesOrderLink = "<a href='$salesOrderURL' target='_blank'>" . $dsRenQuotation->getValue(
                            DBEJRenQuotation::ordheadID
                        ) . "</a>";
                }


                $this->template->set_var(
                    array(
                        'customerName'        => $dsRenQuotation->getValue(DBEJRenQuotation::customerName),
                        'itemDescription'     => $dsRenQuotation->getValue(DBEJRenQuotation::itemDescription),
                        'type'                => $dsRenQuotation->getValue(DBEJRenQuotation::type),
                        'startDate'           => Controller::dateYMDtoDMY(
                            $dsRenQuotation->getValue(DBEJRenQuotation::startDate)
                        ),
                        'nextPeriodStartDate' => Controller::dateYMDtoDMY(
                            $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodStartDate)
                        ),
                        'nextPeriodEndDate'   => Controller::dateYMDtoDMY(
                            $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodEndDate)
                        ),
                        'urlEdit'             => $urlEdit,
                        'urlList'             => $urlList,
                        'txtEdit'             => $txtEdit,
                        'salesOrderLink'      => $salesOrderLink,
                        'sentQuotationColor'  => !$salesOrderLink ? 'white' : ($sent ? "#B2FFB2" : "#F5AEBD"),
                        'latestQuoteSent'     => $latestQuoteSent ? $latestQuoteSent->format('d/m/Y H:i:s') : '',
                        'comments'            => substr(
                            $dsRenQuotation->getValue(DBEJRenQuotation::customerItemNotes),
                            0,
                            30
                        )
                    )
                );
                $this->template->parse(
                    'rows',
                    'rowBlock',
                    true
                );
            }//while $dsRenQuotation->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenQuotationList',
            true
        );
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

            $buSalesOrder->getOrderByOrdheadID(
                $_REQUEST['ordheadID'],
                $dsOrdhead,
                $dsDontNeedOrdline
            );

            $ID = $this->buRenQuotation->createNewRenewal(
                $dsOrdhead->getValue('customerID'),
                $dsOrdhead->getValue('delSiteNo'),
                $dsOrdline->getValue('itemID'),
                $renewalCustomerItemID,
                // returned by function
                $dsOrdline->getValue('curUnitSale'),
                $dsOrdline->getValue('curUnitCost'),
                $dsOrdline->getValue('qtyOrdered')
            );


            // For despatch, prevents the renewal appearing again today during despatch process.
            $dbeOrdline = new DBEOrdline($this);

            $dbeOrdline->setValue(
                'ordheadID',
                $dsOrdline->getValue('ordheadID')
            );
            $dbeOrdline->setValue(
                'sequenceNo',
                $dsOrdline->getValue('sequenceNo')
            );

            $dbeOrdline->getRow();
            $dbeOrdline->setValue(
                'renewalCustomerItemID',
                $renewalCustomerItemID
            );

            $dbeOrdline->updateRow();

        }

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'ID'     => $renewalCustomerItemID
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
                $this->buRenQuotation->getRenQuotationByID(
                    $_REQUEST['ID'],
                    $dsRenQuotation
                );
                $customerItemID = $_REQUEST['ID'];
            } else {                                                                    // creating new
                $dsRenQuotation->initialise();
                $dsRenQuotation->setValue(
                    'customerItemID',
                    '0'
                );
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenQuotation->initialise();
            $dsRenQuotation->fetchNext();
            $customerItemID = $dsRenQuotation->getValue('customerItemID');
        }

        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'update',
                    'ordheadID'      => $_REQUEST['ordheadID'],
                    'customerItemID' => $customerItemID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
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
            Controller::buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_QUOTATION_RENEWAL_TYPE_ID,
                    'htmlFmt'       => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            Controller::buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'  => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $salesOrderLink = '';
        if ($dsRenQuotation->getValue(DBEJRenQuotation::ordheadID)) {
            $salesOrderURL = Controller::buildLink(
                CTCNC_PAGE_SALESORDER,
                array(
                    'action'    => 'displaySalesOrder',
                    'ordheadID' => $dsRenQuotation->getValue(DBEJRenQuotation::ordheadID)
                )
            );

            $salesOrderLink = "<a href='$salesOrderURL' target='_blank'>" . $dsRenQuotation->getValue(
                    DBEJRenQuotation::ordheadID
                ) . "</a>";
        }


        $this->template->set_var(
            array(
                'customerID'           => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::customerID)
                ),
                'siteName'             => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::siteName)
                ),
                'siteNo'               => $dsRenQuotation->getValue(DBEJRenQuotation::siteNo),
                'itemID'               => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::itemID)
                ),
                'customerItemID'       => $dsRenQuotation->getValue(DBEJRenQuotation::customerItemID),
                'customerName'         => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::customerName)
                ),
                'itemDescription'      => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::itemDescription)
                ),
                'startDate'            => Controller::dateYMDtoDMY(
                    $dsRenQuotation->getValue(DBEJRenQuotation::startDate)
                ),
                'calculatedExpiryDate' =>
                    DateTime::createFromFormat(
                        'Y-m-d',
                        $dsRenQuotation->getValue(DBEJRenQuotation::startDate)
                    )->add(
                        new DateInterval('P1Y')
                    )->format('d/m/Y'),
                'dateGenerated'        => Controller::dateYMDtoDMY(
                    $dsRenQuotation->getValue(DBEJRenQuotation::dateGenerated)
                ),
                'dateGeneratedMessage' => $dsRenQuotation->getMessage(DBEJRenQuotation::dateGenerated),
                'grantNumber'          => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::grantNumber)
                ),
                'serialNo'             => Controller::htmlDisplayText(
                    $dsRenQuotation->getValue(DBEJRenQuotation::serialNo)
                ),
                'qty'                  => Controller::htmlDisplayText($dsRenQuotation->getValue(DBEJRenQuotation::qty)),
                'declinedFlagChecked'  => Controller::htmlChecked(
                    $dsRenQuotation->getValue(DBEJRenQuotation::declinedFlag)
                ),
                'urlUpdate'            => $urlUpdate,
                'urlDelete'            => $urlDelete,
                'txtDelete'            => $txtDelete,
                'urlItemEdit'          => $urlItemEdit,
                'urlItemPopup'         => $urlItemPopup,
                'urlDisplayList'       => $urlDisplayList,
                'disabled'             => $disabled,
                'internalNotes'        => Controller::htmlTextArea(
                    $dsRenQuotation->getValue(DBEJRenQuotation::internalNotes)
                ),
                'comments'             => Controller::htmlTextArea(
                    $dsRenQuotation->getValue(DBEJRenQuotation::customerItemNotes)
                ),
                'readonly'             => $readonly,
                'salesOrderLink'       => $salesOrderLink,
                'ordheadID'            => $dsRenQuotation->getValue(DBEJRenQuotation::ordheadID)
            )
        );
        $dbeRenQuotationType = new DBERenQuotationType($this);

        $dbeRenQuotationType->getRows();

        $this->template->set_block(
            'RenQuotationEdit',
            'typeBlock',
            'types'
        );

        while ($dbeRenQuotationType->fetchNext()) {

            $typeSelected = ($dsRenQuotation->getValue('renQuotationTypeID') == $dbeRenQuotationType->getValue(
                    'renQuotationTypeID'
                )) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'typeSelected'       => $typeSelected,
                    'renQuotationTypeID' => $dbeRenQuotationType->getPKValue(),
                    'typeDescription'    => $dbeRenQuotationType->getValue('description')
                )
            );
            $this->template->parse(
                'types',
                'typeBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'RenQuotationEdit',
            true
        );

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
                Controller::buildLink(
                    'Despatch',
                    array(
                        'action' => 'inputRenewals',
                        'ID'     => $_REQUEST['ordheadID']
                    )
                );

        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $this->dsRenQuotation->getValue('customerItemID')
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
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'list'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>