<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTRenDomain extends CTCNC
{
    const InitialContractLengthValues  = [
        12,
        24,
        36,
        48,
        60
    ];
    const renDomainFormCustomerName    = "customerName";
    const renDomainFormSiteName        = "siteName";
    const renDomainFormInvoiceFromDate = "invoiceFromDate";
    const renDomainFormInvoiceToDate   = "invoiceToDate";
    const renDomainFormItemDescription = "itemDescription";
    /** @var DSForm */
    public $dsRenDomain;
    /** @var BURenDomain */
    public $buRenDomain;
    /** @var BUCustomerItem */
    public $buCustomerItem;

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
        $this->setMenuId(604);
        $this->buRenDomain    = new BURenDomain($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenDomain    = new DSForm($this);
        $this->dsRenDomain->copyColumnsFrom(new DBEJRenDomain($this));
        $this->dsRenDomain->addColumn(
            self::renDomainFormCustomerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenDomain->addColumn(
            self::renDomainFormSiteName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenDomain->addColumn(
            self::renDomainFormInvoiceFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenDomain->addColumn(
            self::renDomainFormInvoiceToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenDomain->addColumn(
            self::renDomainFormItemDescription,
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
        $dsRenDomain = &$this->dsRenDomain; // ref to class var
        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buRenDomain->getRenDomainByID(
                    $this->getParam('ID'),
                    $dsRenDomain
                );
                $customerItemID = $this->getParam('ID');
            } else {                                                                    // creating new
                $dsRenDomain->initialise();
                $dsRenDomain->setValue(
                    DBEJCustomerItem::customerItemID,
                    null
                );
                $customerItemID = null;
            }
        } else {                                                                        // form validation error
            $dsRenDomain->initialise();
            $dsRenDomain->fetchNext();
            $customerItemID = $dsRenDomain->getValue(DBEJCustomerItem::customerItemID);
        }
        $urlUpdate      = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'update',
                'ordheadID'      => $this->getParam('ordheadID'),
                'customerItemID' => $customerItemID
            )
        );
        $urlDisplayList = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'list'
            )
        );
        $this->setPageTitle('Domain Name');
        $this->setTemplateFiles(
            array('RenDomainEdit' => 'RenDomainEdit.inc')
        );
        $this->loadReactScript('ItemSelectorWrapperComponent.js');
        $this->loadReactCSS('ItemSelectorWrapperComponent.css');
        $readonly      = CTCNC_HTML_READONLY;
        $disabled      = CTCNC_HTML_DISABLED;
        $declined      = null;
        $pricePerMonth = null;
        if ($this->hasPermissions(RENEWALS_PERMISSION)) {
            $readonly = null;
            $disabled = null;
            $declined = '<tr>
            <td class="promptText">Declined</td>
            <td class="fieldText">
            <input
              name="renDomain[1][declinedFlag]" 
              {readonly}
              type="checkbox"
              value="Y"
              ' . Controller::htmlChecked($dsRenDomain->getValue(DBEJCustomerItem::declinedFlag)) . '
            /></td>
        </tr>';
            $pricePerMonth = '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText"><input
              name="renBroadband[1][salePrice]"
              type="text" value="' . $dsRenDomain->getValue(DBEJCustomerItem::salePrice) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenDomain->getMessage(DBEJCustomerItem::salePrice)
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renBroadband[1][costPrice]"
              type="text" value="' . $dsRenDomain->getValue(DBEJCustomerItem::costPrice) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenDomain->getMessage(DBEJCustomerItem::costPrice)
                ) . '</span> </td>
        </tr>';


        }
        $urlItemPopup     = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                'renewalTypeID' => CONFIG_DOMAIN_RENEWAL_TYPE_ID,
                'htmlFmt'       => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit      = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlPrintContract = Controller::buildLink(
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
        $this->template->setBlock(
            'RenDomainEdit',
            'initialContractLengthBlock',
            'initialContractLengths'
        );
        $this->parseInitialContractLength($dsRenDomain->getValue(DBECustomerItem::initialContractLength));
        $expiryDate = null;
        if ($installationDate = DateTime::createFromFormat(
            'Y-m-d',
            $dsRenDomain->getValue(DBECustomerItem::installationDate)
        )) {
            $expiryDate = getExpiryDate(
                $installationDate,
                $dsRenDomain->getValue(DBECustomerItem::initialContractLength)
            )->format('d/m/Y');
        }
        $this->template->set_var(
            array(
                'pricePerMonth'                      => $pricePerMonth,
                'costPrice'                          => $dsRenDomain->getValue(DBEJCustomerItem::costPrice),
                'salePrice'                          => $dsRenDomain->getValue(DBEJCustomerItem::salePrice),
                'customerItemID'                     => $dsRenDomain->getValue(DBEJCustomerItem::customerItemID),
                'customerName'                       => Controller::htmlDisplayText(
                    $dsRenDomain->getValue(DBEJCustomerItem::customerName)
                ),
                'customerID'                         => Controller::htmlDisplayText(
                    $dsRenDomain->getValue(DBEJCustomerItem::customerID)
                ),
                'siteName'                           => Controller::htmlDisplayText(
                    $dsRenDomain->getValue(DBEJRenDomain::siteName)
                ),
                'siteNo'                             => $dsRenDomain->getValue(DBEJCustomerItem::siteNo),
                'itemDescription'                    => Controller::htmlDisplayText(
                    $dsRenDomain->getValue(DBEJCustomerItem::itemDescription)
                ),
                'itemID'                             => Controller::htmlDisplayText(
                    $dsRenDomain->getValue(DBEJCustomerItem::itemID)
                ),
                'invoiceFromDate'                    => $dsRenDomain->getValue(DBEJCustomerItem::invoiceFromDate),
                'installationDate'                   => $dsRenDomain->getValue(DBEJCustomerItem::installationDate),
                'invoiceToDate'                      => $dsRenDomain->getValue(DBEJCustomerItem::invoiceToDate),
                'invoicePeriodMonths'                => Controller::htmlInputText(
                    $dsRenDomain->getValue(DBEJCustomerItem::invoicePeriodMonths)
                ),
                'invoicePeriodMonthsMessage'         => Controller::htmlDisplayText(
                    $dsRenDomain->getMessage(DBEJCustomerItem::invoicePeriodMonths)
                ),
                'totalInvoiceMonths'                 => Controller::htmlInputText(
                    $dsRenDomain->getValue(DBEJCustomerItem::totalInvoiceMonths)
                ),
                'autoGenerateContractInvoiceChecked' => Controller::htmlChecked(
                    $dsRenDomain->getValue(DBEJCustomerItem::autoGenerateContractInvoice)
                ),
                'notes'                              => Controller::htmlInputText(
                    $dsRenDomain->getValue(DBEJCustomerItem::notes)
                ),
                'notesMessage'                       => Controller::htmlDisplayText(
                    $dsRenDomain->getMessage(DBEJCustomerItem::notes)
                ),
                'urlUpdate'                          => $urlUpdate,
                'urlItemEdit'                        => $urlItemEdit,
                'urlItemPopup'                       => $urlItemPopup,
                'urlDisplayList'                     => $urlDisplayList,
                'declined'                           => $declined,
                'declinedFlag'                       => $dsRenDomain->getValue(DBEJCustomerItem::declinedFlag),
                'disabled'                           => $disabled,
                'readonly'                           => $readonly,
                'internalNotes'                      => Controller::htmlTextArea(
                    $dsRenDomain->getValue(DBEJCustomerItem::internalNotes)
                ),
                'calculatedExpiryDate'               => $expiryDate,
            )
        );
        $this->template->parse(
            'CONTENTS',
            'RenDomainEdit',
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
        $DBEJOrdline  = new DBEJOrdline($this);
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
            $this->buRenDomain->createNewRenewal(
                $dsOrdhead->getValue(DBEJOrdhead::customerID),
                $DBEJOrdline->getValue(DBEJOrdline::itemID),
                $renewalCustomerItemID,
                $dsOrdhead->getValue(DBEJOrdhead::delSiteNo)                // returned by function
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
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'edit',
                'ID'     => $renewalCustomerItemID
            )
        );
        header('Location: ' . $urlNext);
        exit;
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsRenDomain->populateFromArray($this->getParam('renDomain')));
        if ($this->formError) {
            if ($this->dsRenDomain->getValue(
                DBEJCustomerItem::customerItemID
            )) {                    // attempt to insert
                $this->setAction('edit');
            } else {
                $this->setAction('create');
            }
            $this->edit();
            exit;
        }
        $this->buRenDomain->updateRenDomain($this->dsRenDomain);
        if ($this->getParam('ordheadID') == 1) {        // see whether more renewals need to be edited for this
            // despatch
            $urlNext = Controller::buildLink(
                'Despatch',
                array(
                    'action' => 'inputRenewals',
                    'ID'     => $this->getParam('ordheadID')
                )
            );

        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'ID'     => $this->dsRenDomain->getValue(DBEJCustomerItem::customerItemID)
                )
            );

        }
        header('Location: ' . $urlNext);
    }

    /**
     * This function creates quotes for the domain renewals that are due
     *
     */
    function createRenewalsSalesOrders()
    {
        $this->buRenDomain->createRenewalsSalesOrders();
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Domain Names');
        $this->setTemplateFiles(
            array('RenDomainList' => 'RenDomainList.inc')
        );
        $dsRenDomain = new DataSet($this);
        $this->buRenDomain->getAll(
            $dsRenDomain,
            $this->getParam('orderBy')
        );
        if ($dsRenDomain->rowCount() > 0) {
            $this->template->set_block(
                'RenDomainList',
                'rowBlock',
                'rows'
            );
            while ($dsRenDomain->fetchNext()) {

                $customerItemID = $dsRenDomain->getValue(DBEJCustomerItem::customerItemID);
                $urlEdit = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $customerItemID
                    )
                );
                $txtEdit = '[edit]';
                $urlList = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'list'
                    )
                );
                $this->template->set_var(
                    array(
                        'customerName'    => $dsRenDomain->getValue(DBEJCustomerItem::customerName),
                        'itemDescription' => $dsRenDomain->getValue(DBEJCustomerItem::itemDescription),
                        'domain'          => $dsRenDomain->getValue(DBEJCustomerItem::notes),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenDomain->getValue(DBEJCustomerItem::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenDomain->getValue(DBEJCustomerItem::invoiceToDate)
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
            }//while $dsRenDomain->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenDomainList',
            true
        );
        $this->parsePage();
    }
}
