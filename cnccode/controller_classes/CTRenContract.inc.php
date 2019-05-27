<?php
/**
 * Quote renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenContract.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');

class CTRenContract extends CTCNC
{
    const customerName = 'customerName';
    const invoiceFromDate = 'invoiceFromDate';
    const invoiceToDate = 'invoiceToDate';
    const itemID = 'itemID';
    const itemDescription = 'itemDescription';
    const siteDesc = 'siteDesc';
    const costPrice = 'costPrice';
    const salePrice = 'salePrice';

    public $dsRenContract;
    public $buRenContract;
    public $buCustomerItem;
    public $renewalStatusArray = array(
        "D" => "Declined",
        "R" => "Renewed"
    );

    const InitialContractLengthValues = [
        12,
        24,
        36,
        48,
        60
    ];

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
        $this->buRenContract = new BURenContract($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenContract = new DSForm($this);
        $this->dsRenContract->copyColumnsFrom($this->buRenContract->dbeRenContract);
        $this->dsRenContract->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::invoiceFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::invoiceToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::itemID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::siteDesc,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::costPrice,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenContract->addColumn(
            self::salePrice,
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
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Contract Renewals');
        $this->setTemplateFiles(
            array('RenContractList' => 'RenContractList.inc')
        );
        $dsRenContract = new DataSet($this);
        $this->buRenContract->getAll(
            $dsRenContract,
            $this->getParam('orderBy')
        );

        if ($dsRenContract->rowCount() > 0) {
            $this->template->set_block(
                'RenContractList',
                'rowBlock',
                'rows'
            );
            while ($dsRenContract->fetchNext()) {

                $customerItemID = $dsRenContract->getValue(DBEJRenContract::customerItemID);

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
                        'customerName'    => $dsRenContract->getValue(DBEJRenContract::customerName),
                        'itemDescription' => $dsRenContract->getValue(DBEJRenContract::itemDescription),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenContract->getValue(DBEJRenContract::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenContract->getValue(DBEJRenContract::invoiceToDate)
                        ),
                        'notes'           => Controller::dateYMDtoDMY($dsRenContract->getValue(DBEJRenContract::notes)),
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
            }//while $dsRenContract->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenContractList',
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
     * @throws Exception
     */
    function editFromSalesOrder()
    {
        $buSalesOrder = new BUSalesOrder($this);
        $dsOrdline = new DataSet($this);
        $buSalesOrder->getOrdlineByIDSeqNo(
            $this->getParam('ordheadID'),
            $this->getParam('sequenceNo'),
            $dsOrdline
        );

        $renewalCustomerItemID = $dsOrdline->getValue(DBEJOrdline::renewalCustomerItemID);

        // has the order line get a renewal already?
        if (!$renewalCustomerItemID) {
            // create a new record first
            $dsOrdhead = new DataSet($this);
            $buSalesOrder->getOrderByOrdheadID(
                $this->getParam('ordheadID'),
                $dsOrdhead,
                $dsDontNeedOrdline
            );

            $this->buRenContract->createNewRenewal(
                $dsOrdhead->getValue(DBEJOrdhead::customerID),
                $dsOrdline->getValue(DBEJOrdline::itemID),
                $renewalCustomerItemID,
                $dsOrdhead->getValue(DBEJOrdhead::delSiteNo)                // returned by function
            );


            // For despatch, prevents the renewal appearing again today during despatch process.
            $dbeOrdline = new DBEOrdline($this);

            $dbeOrdline->setValue(
                DBEJOrdline::ordheadID,
                $dsOrdline->getValue(DBEJOrdline::ordheadID)
            );
            $dbeOrdline->setValue(
                DBEJOrdline::sequenceNo,
                $dsOrdline->getValue(DBEJOrdline::sequenceNo)
            );

            $dbeOrdline->getRow();
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
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRenContract = &$this->dsRenContract; // ref to class var


        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buRenContract->getRenContractByID(
                    $this->getParam('ID'),
                    $dsRenContract
                );
                $customerItemID = $this->getParam('ID');
            } else {                                                                    // creating new
                $dsRenContract->initialise();
                $dsRenContract->setValue(
                    DBEJRenContract::customerItemID,
                    null
                );
                $customerItemID = null;
            }
        } else {                                                                        // form validation error
            $dsRenContract->initialise();
            $dsRenContract->fetchNext();
            $customerItemID = $dsRenContract->getValue(DBEJRenContract::customerItemID);
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
        $this->setPageTitle('Edit Contract');
        $this->setTemplateFiles(
            array(
                'RenContractEdit'         => 'RenContractEdit.inc',
                'RenContractPrepayFields' => 'RenContractPrepayFields.inc'
            )
        );

        $disabled = 'DISABLED';
        $readonly = 'READONLY';
        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $readonly = null;
            $disabled = null;
        }

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

        if (!$disabled) {
            $prices =
                '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText"><input
              name="renContract[1][curUnitSale]"
              type="text" value="' . Controller::htmlInputText($dsRenContract->getValue(DBEJRenContract::curUnitSale)) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::curUnitSale)
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renContract[1][curUnitCost]"
              type="text" value="' . Controller::htmlInputText($dsRenContract->getValue(DBEJRenContract::curUnitCost)) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::curUnitCost)
                ) . '</span> </td>
        </tr>';

            $declined =
                '<tr>
            <td class="promptText">Declined</td>
            <td class="fieldText">
            <input
              name="renContract[1][declinedFlag]" 
              {readonly}
              type="checkbox"
              value="Y"
              ' . Controller::htmlChecked($dsRenContract->getValue(DBEJRenContract::declinedFlag)) . '
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
                    'renewalTypeID' => CONFIG_CONTRACT_RENEWAL_TYPE_ID,
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
        $urlSiteEdit =
            Controller::buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action'  => CTCNC_ACT_SITE_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSitePopup =
            Controller::buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action'  => CTCNC_ACT_SITE_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($dsRenContract->getValue(DBECustomerItem::customerID));

        $isDirectDebitAllowed = $dsCustomer->getValue(DBECustomer::sortCode) && $dsCustomer->getValue(
                DBECustomer::accountName
            ) && $dsCustomer->getValue(DBECustomer::accountNumber);

        $expiryDate = null;
        if ($installationDate = DateTime::createFromFormat(
            'Y-m-d',
            $dsRenContract->getValue(DBECustomerItem::installationDate)
        )) {
            $expiryDate = getExpiryDate(
                $installationDate,
                $dsRenContract->getValue(DBECustomerItem::initialContractLength)
            )->format('d/m/Y');
        }


        $this->template->set_var(
            array(
                'customerItemID'                     => $dsRenContract->getValue(DBEJRenContract::customerItemID),
                'customerName'                       => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::customerName)
                ),
                'customerID'                         => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::customerID)
                ),
                'users'                              => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::users)
                ),
                'siteDesc'                           => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::siteName)
                ),
                'siteNo'                             => $dsRenContract->getValue(DBEJRenContract::siteNo),
                'urlSitePopup'                       => $urlSitePopup,
                'urlSiteEdit'                        => $urlSiteEdit,
                'itemID'                             => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::itemID)
                ),
                'itemDescription'                    => Controller::htmlDisplayText(
                    $dsRenContract->getValue(DBEJRenContract::itemDescription)
                ),
                'invoiceFromDate'                    => $dsRenContract->getValue(DBEJRenContract::invoiceFromDate),
                'installationDate'                   => Controller::dateYMDtoDMY(
                    $dsRenContract->getValue(DBEJRenContract::installationDate)
                ),
                'invoiceToDate'                      => $dsRenContract->getValue(DBEJRenContract::invoiceToDate),
                'invoicePeriodMonths'                => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::invoicePeriodMonths)
                ),
                'invoicePeriodMonthsMessage'         => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::invoicePeriodMonths)

                ),
                'totalInvoiceMonths'                 => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::totalInvoiceMonths)

                ),
                'curUnitCost'                        => $dsRenContract->getValue(DBEJRenContract::curUnitCost),
                'curUnitSale'                        => $dsRenContract->getValue(DBEJRenContract::curUnitSale),
                'notes'                              => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::notes)
                ),
                'notesMessage'                       => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::notes)
                ),
                'hostingCompany'                     => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::hostingCompany)
                ),
                'hostingCompanyMessage'              => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::hostingCompany)
                ),
                'password'                           => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::password)
                ),
                'passwordMessage'                    => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::password)
                ),
                'osPlatform'                         => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::osPlatform)
                ),
                'osPlatformMessage'                  => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::osPlatform)
                ),
                'domainNames'                        => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::domainNames)
                ),
                'domainNamesMessage'                 => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::domainNames)
                ),
                'controlPanelUrl'                    => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::controlPanelUrl)
                ),
                'controlPanelUrlMessage'             => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::controlPanelUrl)

                ),
                'ftpAddress'                         => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::ftpAddress)
                ),
                'ftpAddressMessage'                  => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::ftpAddress)
                ),
                'ftpUsername'                        => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::ftpUsername)
                ),
                'ftpUsernameMessage'                 => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::ftpUsername)
                ),
                'wwwAddress'                         => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::wwwAddress)
                ),
                'wwwAddressMessage'                  => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::wwwAddress)
                ),
                'websiteDeveloper'                   => Controller::htmlInputText(
                    $dsRenContract->getValue(DBEJRenContract::websiteDeveloper)
                ),
                'websiteDeveloperMessage'            => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::websiteDeveloper)
                ),
                'officialOrderNumber'                => Controller::htmlInputText(
                    $dsRenContract->getValue(DBECustomerItem::officialOrderNumber)
                ),
                'urlUpdate'                          => $urlUpdate,
                'urlDisplayList'                     => $urlDisplayList,
                //        'declined'          => $declined,
                'disabled'                           => $disabled,
                'readonly'                           => $readonly,
                /* This is NOW used as the printed contract start date when you print a contract */
                'customerItemNotes'                  => Controller::htmlTextArea(
                    $dsRenContract->getValue(DBEJRenContract::customerItemNotes)
                ),
                'internalNotes'                      => Controller::htmlTextArea(
                    $dsRenContract->getValue(DBEJRenContract::internalNotes)
                ),
                'despatchDate'                       => Controller::dateYMDtoDMY(
                    $dsRenContract->getValue(DBEJRenContract::despatchDate)
                ),
                'despatchDateMessage'                => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::despatchDate)
                ),
                'expiryDate'                         => Controller::dateYMDtoDMY(
                    $dsRenContract->getValue(DBEJRenContract::expiryDate)
                ),
                'calculatedExpiryDate'               => $expiryDate,
                'expiryDateMessage'                  => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::expiryDate)
                ),
                'autoGenerateContractInvoiceChecked' => Controller::htmlChecked(
                    $dsRenContract->getValue(DBEJRenContract::autoGenerateContractInvoice)
                ),
                'directDebitFlagChecked'             => Controller::htmlChecked(
                    $dsRenContract->getValue(DBECustomerItem::directDebitFlag)
                ),
                'urlItemPopup'                       => $urlItemPopup,
                'urlItemEdit'                        => $urlItemEdit,
                'allowDirectDebit'                   => $dsRenContract->getValue(
                    DBEJRenContract::allowDirectDebit
                ) === 'Y' ? 'true' : 'false',
                'clientCheckDirectDebit'             => $isDirectDebitAllowed ? 'true' : 'false'
            )
        );

        // prepay fields
        if ($this->dsRenContract->getValue(DBEJRenContract::itemID) == CONFIG_DEF_PREPAY_ITEMID) {
            $this->template->set_var(
                array(
                    'curGSCBalance'        => Controller::htmlDisplayText(
                        $dsRenContract->getValue(DBEJRenContract::curGSCBalance)
                    ),
                    'curGSCBalanceMessage' => Controller::htmlDisplayText(
                        $dsRenContract->getMessage(DBEJRenContract::curGSCBalance)
                    )
                )
            );
        }

        $this->template->set_block(
            'RenContractEdit',
            'renewalStatusBlock',
            'renewalStatus'
        );
        $this->parseRenewalSelector($dsRenContract->getValue(DBEJRenContract::renewalStatus));


        $this->template->setBlock(
            'RenContractEdit',
            'initialContractLengthBlock',
            'initialContractLengths'
        );

        $this->parseInitialContractLength($dsRenContract->getValue(DBECustomerItem::initialContractLength));

        $buCustomerItem = new BUCustomerItem($this);
        $dsCustomerItem = new DataSet($this);
        $buCustomerItem->getCustomerItemsByContractID(
            $dsRenContract->getValue(DBEJRenContract::customerItemID),
            $dsCustomerItem
        );

        $this->template->set_block(
            'RenContractEdit',
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
                    'selected'        => $dsRenContract->getValue(
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
            'RenContractEdit',
            'coveredItemsBlock',
            'coveredItems'
        );
        while ($dsCustomerItem->fetchNext()) {
            $description = $dsCustomerItem->getValue(DBEJCustomerItem::itemDescription);

            if ($dsCustomerItem->getValue(DBEJCustomerItem::serverName)) {
                $description .= '(' . $dsCustomerItem->getValue(DBEJCustomerItem::serverName) . ')';
            }

            $url =
                Controller::buildLink(
                    'CustomerItem.php',
                    array(
                        'action'         => 'displayCI',
                        'customerItemID' => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
            $this->template->set_var(
                array(
                    'coveredItemDescription' => $description,
                    'coveredItemSerialNo'    => $dsCustomerItem->getValue(DBEJCustomerItem::serialNo),
                    'coveredItemUrl'         => $url
                )
            );
            $this->template->parse(
                'coveredItems',
                'coveredItemsBlock',
                true
            );
        }

        if ($this->dsRenContract->getValue(DBEJRenContract::itemID) == CONFIG_DEF_PREPAY_ITEMID) {
            $this->template->parse(
                'renContractPrePayFields',
                'RenContractPrepayFields',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'RenContractEdit',
            true
        );

        $this->parsePage();

    }

    /**
     * Update call activity type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsRenContract->populateFromArray($this->getParam('renContract')));
        if ($this->formError) {
            if ($this->dsRenContract->getValue(DBEJRenContract::customerItemID)) {
                $this->setAction('edit');
            } else {
                $this->setAction('create');
            }
            $this->edit();
            exit;
        }

        $this->buRenContract->updateRenContract($this->dsRenContract);

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
                        'ID'     => $this->dsRenContract->getValue(DBEJRenContract::customerItemID)
                    )
                );

        }

        header('Location: ' . $urlNext);
    }

    /**
     * This function creates quotes for the contract renewals that are due
     *
     * @throws Exception
     */
    function createRenewalsSalesOrders()
    {

        $this->buRenContract->createRenewalsSalesOrders();


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
                'renewalStatus',
                'renewalStatusBlock',
                true
            );
        }
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
}
