<?php
/**
 * Quote renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenHosting.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');

class CTRenHosting extends CTCNC
{
    const InitialContractLengthValues = [
        12,
        24,
        36,
        48,
        60
    ];
    public $dsRenHosting;
    public $buRenHosting;
    public $buCustomerItem;
    public $renewalStatusArray = array(
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
        $roles = [RENEWALS_PERMISSION, TECHNICAL_PERMISSION];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(605);
        $this->buRenHosting = new BURenHosting($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenHosting = new DSForm($this);
        $this->dsRenHosting->copyColumnsFrom($this->buRenHosting->dbeRenHosting);
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::customerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::invoiceFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::invoiceToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::itemID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::siteName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::costPrice,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenHosting->addColumn(
            DBEJRenHosting::salePrice,
            DA_STRING,
            DA_ALLOW_NULL
        );
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
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
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRenHosting = &$this->dsRenHosting; // ref to class var


        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buRenHosting->getRenHostingByID(
                    $this->getParam('ID'),
                    $dsRenHosting
                );
                $customerItemID = $this->getParam('ID');
            } else {                                                                    // creating new
                $dsRenHosting->initialise();
                $dsRenHosting->setValue(
                    DBEJRenHosting::customerItemID,
                    '0'
                );
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenHosting->initialise();
            $dsRenHosting->fetchNext();
            $customerItemID = $dsRenHosting->getValue(DBEJRenHosting::customerItemID);
        }

        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'update',
                    'ordheadID'      => $this->getParam('ordheadID'),
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
        $this->setPageTitle('Edit Hosting');
        $this->setTemplateFiles(
            array('RenHostingEdit' => 'RenHostingEdit.inc')
        );
        $this->loadReactScript('ItemSelectorWrapperComponent.js');
        $this->loadReactCSS('ItemSelectorWrapperComponent.css');
        $readonly = null;
        $disabled = null;

        if (!$this->hasPermissions(RENEWALS_PERMISSION)) {
            $disabled = 'DISABLED';
            $readonly = 'READONLY';
        }

        if (!$disabled) {
            $prices =
                '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText"><input
              name="renHosting[1][curUnitSale]"
              type="text" value="' . Controller::htmlInputText($dsRenHosting->getValue(DBEJRenHosting::curUnitSale)) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::curUnitSale)
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renHosting[1][curUnitCost]"
              type="text" value="' . Controller::htmlInputText($dsRenHosting->getValue(DBEJRenHosting::curUnitCost)) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::curUnitCost)
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
              ' . Controller::htmlChecked($dsRenHosting->getValue(DBEJRenHosting::declinedFlag)) . '
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
            Controller::buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_HOSTING_RENEWAL_TYPE_ID,
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

        $urlPrintContract =
            Controller::buildLink(
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


        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($dsRenHosting->getValue(DBECustomerItem::customerID));
        $isDirectDebitAllowed = $dsCustomer->getValue(DBECustomer::sortCode) && $dsCustomer->getValue(
                DBECustomer::accountName
            ) && $dsCustomer->getValue(DBECustomer::accountNumber);

        $expiryDate = null;
        if ($installationDate = DateTime::createFromFormat(
            'Y-m-d',
            $dsRenHosting->getValue(DBECustomerItem::installationDate)
        )) {
            $expiryDate = getExpiryDate(
                $installationDate,
                $dsRenHosting->getValue(DBECustomerItem::initialContractLength)
            )->format('d/m/Y');
        }

        $this->template->set_var(
            array(
                'customerItemID'                     => $dsRenHosting->getValue(DBEJRenHosting::customerItemID),
                'customerName'                       => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::customerName)
                ),
                'customerID'                         => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::customerID)
                ),
                'users'                              => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::users)
                ),
                'siteName'                           => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::siteName)
                ),
                'siteNo'                             => $dsRenHosting->getValue(DBEJRenHosting::siteNo),
                'itemID'                             => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::itemID)
                ),
                'itemDescription'                    => Controller::htmlDisplayText(
                    $dsRenHosting->getValue(DBEJRenHosting::itemDescription)
                ),
                'invoiceFromDate'                    => $dsRenHosting->getValue(DBEJRenHosting::invoiceFromDate),
                'installationDate'                   => $dsRenHosting->getValue(DBEJRenHosting::installationDate),
                'invoiceToDate'                      => $dsRenHosting->getValue(DBEJRenHosting::invoiceToDate),
                'invoicePeriodMonths'                => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::invoicePeriodMonths)
                ),
                'invoicePeriodMonthsMessage'         => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::invoicePeriodMonths)
                ),
                'totalInvoiceMonths'                 => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::totalInvoiceMonths)
                ),
                'notes'                              => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::notes)
                ),
                'notesMessage'                       => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::notes)
                ),
                'hostingCompany'                     => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::hostingCompany)
                ),
                'hostingCompanyMessage'              => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::hostingCompany)
                ),
                'hostingUserName'                    => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::hostingUserName)
                ),
                'hostingUserNameMessage'             => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::hostingUserName)
                ),
                'password'                           => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::password)
                ),
                'passwordMessage'                    => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::password)
                ),
                'osPlatform'                         => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::osPlatform)
                ),
                'osPlatformMessage'                  => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::osPlatform)
                ),
                'controlPanelUrl'                    => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::controlPanelUrl)
                ),
                'controlPanelUrlMessage'             => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::controlPanelUrl)

                ),
                'ftpAddress'                         => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::ftpAddress)
                ),
                'ftpAddressMessage'                  => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::ftpAddress)
                ),
                'ftpUsername'                        => Controller::htmlInputText(
                    $dsRenHosting->getValue(DBEJRenHosting::ftpUsername)
                ),
                'ftpUsernameMessage'                 => Controller::htmlDisplayText(
                    $dsRenHosting->getMessage(DBEJRenHosting::ftpUsername)
                ),
                'autoGenerateContractInvoiceChecked' => Controller::htmlChecked(
                    $dsRenHosting->getValue(DBEJRenHosting::autoGenerateContractInvoice)
                ),
                'directDebitFlagChecked'             => Controller::htmlChecked(
                    $dsRenHosting->getValue(DBECustomerItem::directDebitFlag)
                ),
                'curUnitSale'                        => $dsRenHosting->getValue(DBEJRenHosting::curUnitSale),
                'curUnitCost'                        => $dsRenHosting->getValue(DBEJRenHosting::curUnitCost),
                'urlUpdate'                          => $urlUpdate,
                'urlItemEdit'                        => $urlItemEdit,
                'urlItemPopup'                       => $urlItemPopup,
                'urlDisplayList'                     => $urlDisplayList,
                'disabled'                           => $disabled,
                'readonly'                           => $readonly,
                'customerItemNotes'                  => Controller::htmlTextArea(
                    $dsRenHosting->getValue(DBEJRenHosting::customerItemNotes)
                ),
                'internalNotes'                      => Controller::htmlTextArea(
                    $dsRenHosting->getValue(DBEJRenHosting::internalNotes)
                ),
                'calculatedExpiryDate'               => $expiryDate,
                "allowDirectDebit"                   => $dsRenHosting->getValue(
                    DBEJRenHosting::allowDirectDebit
                ) == 'Y' ? 'true' : 'false',
                'clientCheckDirectDebit'             => $isDirectDebitAllowed ? 'true' : 'false'
            )
        );


        $this->template->setBlock(
            'RenHostingEdit',
            'initialContractLengthBlock',
            'initialContractLengths'
        );

        $this->parseInitialContractLength($dsRenHosting->getValue(DBECustomerItem::initialContractLength));
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
        $this->parseRenewalSelector($dsRenHosting->getValue(DBEJRenHosting::renewalStatus));

        $this->template->parse(
            'CONTENTS',
            'RenHostingEdit',
            true
        );

        $this->parsePage();

    }

    private function parseInitialContractLength($initialContractLength)
    {
        foreach (self::InitialContractLengthValues as $value) {
            $initialContractLengthSelected = ($initialContractLength == $value) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'initialContractLengthSelected'    => $initialContractLengthSelected,
                    'initialContractLength'            => $value,
                    'initialContractLengthDescription' => $value
                )
            );
            $this->template->parse(
                'initialContractLengths',
                'initialContractLengthBlock',
                true
            );
        }
    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     * @param $renewalStatus
     */
    function parseRenewalSelector($renewalStatus)
    {
        foreach ($this->renewalStatusArray as $key => $value) {
            $renewalStatusSelected = ($renewalStatus == $key) ? CT_SELECTED : null;
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

    /**
     * Called from sales order line to edit a renewal.
     * The page passes
     * ordheadID
     * sequenceNo (line)
     * renewalCustomerItemID (blank if renewal not created yet
     *
     *
     * @throws Exception
     */
    function editFromSalesOrder()
    {
        $buSalesOrder = new BUSalesOrder($this);
        $DBEJOrdline = new DBEJOrdline($this);
        $DBEJOrdline->getRow($this->getParam('lineId'));
        $renewalCustomerItemID = $DBEJOrdline->getValue(DBEJOrdline::renewalCustomerItemID);

        // has the order line get a renewal already?
        if (!$renewalCustomerItemID) {
            // create a new record first
            $dsOrdhead = new DataSet($this);
            $buSalesOrder->getOrderByOrdheadID(
                $DBEJOrdline->getValue(DBEJOrdline::ordheadID),
                $dsOrdhead,
                $dsDontNeedOrdline
            );

            $this->buRenHosting->createNewRenewal(
                $dsOrdhead->getValue(DBEOrdhead::customerID),
                $DBEJOrdline->getValue(DBEOrdline::itemID),
                $renewalCustomerItemID,
                $dsOrdhead->getValue(DBEOrdhead::delSiteNo)                // returned by function
            );


            // For despatch, prevents the renewal appearing again today during despatch process.
            $dbeOrdline = new DBEOrdline($this);
            $dbeOrdline->getRow($DBEJOrdline->getValue(DBEJOrdline::id));
            $dbeOrdline->setValue(
                DBEJOrdline::renewalCustomerItemID,
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
     * Update call activity type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsRenHosting->populateFromArray($this->getParam('renHosting')));
        if ($this->formError) {
            if ($this->dsRenHosting->getValue(DBEJRenHosting::customerItemID)) {                    // attempt to insert
                $this->setAction('edit');
            } else {
                $this->setAction('create');
            }
            $this->edit();
            exit;
        }

        $this->buRenHosting->updateRenHosting($this->dsRenHosting);

        if ($this->getParam('ordheadID') == 1) {        // see whether more renewals need to be edited for this
            // despatch
            $urlNext =
                Controller::buildLink(
                    'Despatch',
                    array(
                        'action' => 'inputRenewals',
                        'ID'     => $this->getParam('ordheadID')
                    )
                );

        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $this->dsRenHosting->getValue(DBEJRenHosting::customerItemID)
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
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Hosting Renewals');
        $this->setTemplateFiles(
            array('RenHostingList' => 'RenHostingList.inc')
        );
        $dsRenHosting = new DataSet($this);
        $this->buRenHosting->getAll(
            $dsRenHosting,
            $this->getParam('orderBy')
        );

        if ($dsRenHosting->rowCount() > 0) {
            $this->template->set_block(
                'RenHostingList',
                'rowBlock',
                'rows'
            );
            while ($dsRenHosting->fetchNext()) {

                $customerItemID = $dsRenHosting->getValue(DBEJRenHosting::customerItemID);

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID'     => $customerItemID
                        )
                    );
                $txtEdit = '[edit]';

                $urlList =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list'
                        )
                    );

                $this->template->set_var(
                    array(
                        'customerName'    => $dsRenHosting->getValue(DBEJRenHosting::customerName),
                        'itemDescription' => $dsRenHosting->getValue(DBEJRenHosting::itemDescription),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenHosting->getValue(DBEJRenHosting::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenHosting->getValue(DBEJRenHosting::invoiceToDate)
                        ),
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

}// end of class
