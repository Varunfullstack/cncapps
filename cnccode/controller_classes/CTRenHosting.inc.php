<?php
/**
 * Quote renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenHosting.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');

class CTRenHosting extends CTCNC
{
    var $dsRenHosting = '';
    var $buRenHosting = '';
    var $buCustomerItem = '';
    var $renewalStatusArray = array(
        "Q" => "Quotation generated",
        "S" => "Quick quote sent",
        "D" => "Declined",
        "R" => "Renewed",
        "A" => "Awaiting Paperwork"
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
            "renewals",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRenHosting = new BURenHosting($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenHosting = new DSForm($this);
        $this->dsRenHosting->copyColumnsFrom($this->buRenHosting->dbeRenHosting);
        $this->dsRenHosting->addColumn(
            'customerName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'invoiceFromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'invoiceToDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'itemID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'itemDescription',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'siteName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            'costPrice',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
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
            case 'createRenewalsSalesOrders':
                $this->createRenewalsSalesOrders();
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
        $this->setPageTitle('Hosting Renewals');
        $this->setTemplateFiles(
            array('RenHostingList' => 'RenHostingList.inc')
        );

        $this->buRenHosting->getAll(
            $dsRenHosting,
            $_REQUEST['orderBy']
        );

        if ($dsRenHosting->rowCount() > 0) {
            $this->template->set_block(
                'RenHostingList',
                'rowBlock',
                'rows'
            );
            while ($dsRenHosting->fetchNext()) {

                $customerItemID = $dsRenHosting->getValue('customerItemID');

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID'     => $customerItemID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => 'delete',
                            'customerItemID' => $customerItemID
                        )
                    );
                $txtDelete = '[delete]';

                $urlList =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list'
                        )
                    );

                $this->template->set_var(
                    array(
                        'customerName'    => $dsRenHosting->getValue('customerName'),
                        'itemDescription' => $dsRenHosting->getValue('itemDescription'),
                        'invoiceFromDate' => Controller::dateYMDtoDMY($dsRenHosting->getValue('invoiceFromDate')),
                        'invoiceToDate'   => Controller::dateYMDtoDMY($dsRenHosting->getValue('invoiceToDate')),
                        'urlEdit'         => $urlEdit,
                        'urlList'         => $urlList,
                        'txtEdit'         => $txtEdit
                    )
                );
                $this->template->parse(
                    'rows',
                    'rowBlock',
                    true
                );
            }//while $dsRenHosting->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenHostingList',
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

            $this->buRenHosting->createNewRenewal(
                $dsOrdhead->getValue('customerID'),
                $dsOrdhead->getValue('delSiteNo'),
                $dsOrdline->getValue('itemID'),
                $renewalCustomerItemID                // returned by function
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
            $this->buildLink(
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
        $dsRenHosting = &$this->dsRenHosting; // ref to class var


        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buRenHosting->getRenHostingByID(
                    $_REQUEST['ID'],
                    $dsRenHosting
                );
                $customerItemID = $_REQUEST['ID'];
            } else {                                                                    // creating new
                $dsRenHosting->initialise();
                $dsRenHosting->setValue(
                    'customerItemID',
                    '0'
                );
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenHosting->initialise();
            $dsRenHosting->fetchNext();
            $customerItemID = $dsRenHosting->getValue('customerItemID');
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'update',
                    'ordheadID'      => $_REQUEST['ordheadID'],
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
        $this->setPageTitle('Edit Hosting');
        $this->setTemplateFiles(
            array('RenHostingEdit' => 'RenHostingEdit.inc')
        );

        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $readonly = ''; // not
            $disabled = ''; // not
        } else {
            //$disabled = CTCNC_HTML_DISABLED;
            $disabled = 'DISABLED';
            $readonly = 'READONLY';
        }


        if (!$disabled) {
            $prices =
                '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText"><input
              name="renHosting[1][curUnitSale]"
              type="text" value="' . Controller::htmlInputText($dsRenHosting->getValue('curUnitSale')) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('curUnitSale')
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renHosting[1][curUnitCost]"
              type="text" value="' . Controller::htmlInputText($dsRenHosting->getValue('curUnitCost')) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('curUnitCost')
                ) . '</span> </td>
        </tr>';

            $declined =
                '<tr>
            <td class="promptText">Declined</td>
            <td class="fieldText">
            <input
              name="renHosting[1][declinedFlag]" 
              {readonly}
              type="checkbox"
              value="Y"
              ' . Controller::htmlChecked($dsRenHosting->getValue('declinedFlag')) . '
            /></td>
        </tr>';

            $this->template->set_var(
                array(
                    'prices'   => $prices,
                    'declined' => $declined
                )
            );
        }
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_HOSTING_RENEWAL_TYPE_ID,
                    'htmlFmt'       => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'  => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlPrintContract =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'action'         => 'printContract',
                    'customerItemID' => $customerItemID
                )
            );
        $this->template->set_var(
            array(
                'txtPrintContract' => 'Print Contract',
                'urlPrintContract' => $urlPrintContract
            )
        );

        $this->template->set_var(
            array(
                'customerItemID'                     => $dsRenHosting->getValue('customerItemID'),
                'customerName'                       => Controller::htmlDisplayText(
                    $dsRenHosting->getValue('customerName')
                ),
                'customerID'                         => Controller::htmlDisplayText(
                    $dsRenHosting->getValue('customerID')
                ),
                'users'                              => Controller::htmlDisplayText($dsRenHosting->getValue('users')),
                'siteName'                           => Controller::htmlDisplayText(
                    $dsRenHosting->getValue('siteName')
                ),
                'siteNo'                             => $dsRenHosting->getValue('siteNo'),
                'itemID'                             => Controller::htmlDisplayText($dsRenHosting->getValue('itemID')),
                'itemDescription'                    => Controller::htmlDisplayText(
                    $dsRenHosting->getValue('itemDescription')
                ),
                'invoiceFromDate'                    => $dsRenHosting->getValue('invoiceFromDate'),
                'installationDate'                   => Controller::dateYMDtoDMY(
                    $dsRenHosting->getValue('installationDate')
                ),
                'invoiceToDate'                      => $dsRenHosting->getValue('invoiceToDate'),
                'invoicePeriodMonths'                => Controller::htmlInputText(
                    $dsRenHosting->getValue('invoicePeriodMonths')
                ),
                'invoicePeriodMonthsMessage'         => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('invoicePeriodMonths')
                ),
                'totalInvoiceMonths'                 => Controller::htmlInputText(
                    $dsRenHosting->getValue('totalInvoiceMonths')
                ),
                'notes'                              => Controller::htmlInputText($dsRenHosting->getValue('notes')),
                'notesMessage'                       => Controller::htmlDisplayText($dsRenHosting->getMessage('notes')),
                'hostingCompany'                     => Controller::htmlInputText(
                    $dsRenHosting->getValue('hostingCompany')
                ),
                'hostingCompanyMessage'              => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('hostingCompany')
                ),
                'hostingUserName'                    => Controller::htmlInputText(
                    $dsRenHosting->getValue('hostingUserName')
                ),
                'hostingUserNameMessage'             => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('hostingUserName')
                ),
                'password'                           => Controller::htmlInputText($dsRenHosting->getValue('password')),
                'passwordMessage'                    => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('password')
                ),
                'osPlatform'                         => Controller::htmlInputText(
                    $dsRenHosting->getValue('osPlatform')
                ),
                'osPlatformMessage'                  => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('osPlatform')
                ),
                'controlPanelUrl'                    => Controller::htmlInputText(
                    $dsRenHosting->getValue('controlPanelUrl')
                ),
                'controlPanelUrlMessage'             => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('controlPanelUrl')
                ),
                'ftpAddress'                         => Controller::htmlInputText(
                    $dsRenHosting->getValue('ftpAddress')
                ),
                'ftpAddressMessage'                  => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('ftpAddress')
                ),
                'ftpUsername'                        => Controller::htmlInputText(
                    $dsRenHosting->getValue('ftpUsername')
                ),
                'ftpUsernameMessage'                 => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage('ftpUsername')
                ),
                'autoGenerateContractInvoiceChecked' => Controller::htmlChecked(
                    $dsRenHosting->getValue('autoGenerateContractInvoice')
                ),
                'directDebitFlagChecked'             => Controller::htmlChecked(
                    $dsRenHosting->getValue(DBECustomerItem::directDebitFlag)
                ),
                'curUnitSale'                        => $dsRenHosting->getValue('curUnitSale'),
                'curUnitCost'                        => $dsRenHosting->getValue('curUnitCost'),
                'urlUpdate'                          => $urlUpdate,
                'urlDelete'                          => $urlDelete,
                'txtDelete'                          => $txtDelete,
                'urlItemEdit'                        => $urlItemEdit,
                'urlItemPopup'                       => $urlItemPopup,
                'urlDisplayList'                     => $urlDisplayList,
                'disabled'                           => $disabled,
                'readonly'                           => $readonly,
                'customerItemNotes'                  => Controller::htmlTextArea(
                    $dsRenHosting->getValue('customerItemNotes')
                ),
                'internalNotes'                      => Controller::htmlTextArea(
                    $dsRenHosting->getValue('internalNotes')
                )
            )
        );

        $this->template->set_block(
            'RenHostingEdit',
            'TransactionTypesBlock',
            'transactionTypesOptions'
        );
        $transactionTypes = [
            "01",
            "17",
        ];
        foreach ($transactionTypes as $transactionType) {
            $this->template->set_var(
                array(
                    'transactionType' => $transactionType,
                    'selected'        => $dsRenHosting->getValue(
                        DBECustomerItem::transactionType
                    ) == $transactionType ? 'selected' : null,
                )
            );
            $this->template->parse(
                'transactionTypesOptions',
                'TransactionTypesBlock',
                true
            );
        }
        $this->template->set_block(
            'RenHostingEdit',
            'renewalStatusBlock',
            'renewalStatuss'
        );
        $this->parseRenewalSelector($dsRenHosting->getValue('renewalStatus'));

        $this->template->parse(
            'CONTENTS',
            'RenHostingEdit',
            true
        );

        $this->parsePage();

    }

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsRenHosting = $this->dsRenHosting;
        $this->formError = (!$this->dsRenHosting->populateFromArray($_REQUEST['renHosting']));
        if ($this->formError) {
            if ($this->dsRenHosting->getValue('customerItemID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buRenHosting->updateRenHosting($this->dsRenHosting);

        if ($_REQUEST['ordheadID'] == 1) {        // see whether more renewals need to be edited for this
            // despatch
            $urlNext =
                $this->buildLink(
                    'Despatch',
                    array(
                        'action' => 'inputRenewals',
                        'ID'     => $_REQUEST['ordheadID']
                    )
                );

        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $this->dsRenHosting->getValue('customerItemID')
                    )
                );

        }

        header('Location: ' . $urlNext);
    }

    /**
     * This function creates quotes for the contract renewals that are due
     *
     */
    function createRenewalsSalesOrders()
    {

        $this->buRenHosting->createRenewalsSalesOrders();


    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     */
    function parseRenewalSelector($renewalStatus)
    {
        foreach ($this->renewalStatusArray as $key => $value) {
            $renewalStatusSelected = ($renewalStatus == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'renewalStatusSelected'    => $renewalStatusSelected,
                    'renewalStatus'            => $key,
                    'renewalStatusDescription' => $value
                )
            );
            $this->template->parse(
                'renewalStatuss',
                'renewalStatusBlock',
                true
            );
        }
    }

}// end of class
?>