<?php
/**
 * Quote renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Business\BURenContract;
use CNCLTD\Data\DBEItem;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/Burencontract.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');

class CTRenContract extends CTCNC
{
    const customerName                = 'customerName';
    const invoiceFromDate             = 'invoiceFromDate';
    const invoiceToDate               = 'invoiceToDate';
    const itemID                      = 'itemID';
    const itemDescription             = 'itemDescription';
    const siteDesc                    = 'siteDesc';
    const costPrice                   = 'costPrice';
    const salePrice                   = 'salePrice';
    const InitialContractLengthValues = [
        12,
        24,
        36,
        48,
        60
    ];
    public $dsRenContract;
    public $buRenContract;
    public $buCustomerItem;
    public $renewalStatusArray = array(
        "D" => "Declined",
        "R" => "Renewed"
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
        $this->setMenuId(602);
        $this->buRenContract  = new BURenContract($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenContract  = new DSForm($this);
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
            case 'addItemToContract':
                $contractCustomerItemId = $this->getParam('contractCustomerItemId');
                $itemToAddId            = $this->getParam('itemToAddId');
                try {
                    $this->addItemToContract($contractCustomerItemId, $itemToAddId);
                    $data = ["status" => "ok"];
                } catch (Exception $exception) {
                    $data = ["status" => "error", "message" => $exception->getMessage()];
                }
                echo json_encode($data);
                exit;
            case 'createRenewalsSalesOrders':
                $this->createRenewalsSalesOrders();
                break;
            case 'searchDesc':
                $itemsPerPage = 20;
                $page         = 1;
                $term         = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }
                if (isset($_REQUEST['itemsPerPage'])) {
                    $itemsPerPage = $_REQUEST['itemsPerPage'];
                }
                if (isset($_REQUEST['page'])) {
                    $page = $_REQUEST['page'];
                }
                global $db;
                $result = $db->preparedQuery(
                    "SELECT itm_desc AS `name`, itm_itemno AS id  FROM custitem LEFT JOIN item ON itm_itemno = cui_itemno WHERE declinedFlag = 'N' AND directDebitFlag <> 'Y'
        AND renewalTypeID = 2 AND itm_desc LIKE ? GROUP BY itm_itemno ORDER BY `name` ",
                    [
                        [
                            "type"  => "s",
                            "value" => '%' . $term . '%'
                        ]
                    ]
                );
                if (!$result) {
                    echo json_encode(["error" => $db->errorInfo()]);
                    http_response_code(400);
                }
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($data);
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
        $this->setPageTitle('Edit Contract');
        $this->setTemplateFiles(
            array(
                'RenContractEdit'         => 'RenContractEdit.inc',
                'RenContractPrepayFields' => 'RenContractPrepayFields.inc'
            )
        );
        $this->loadReactScript('ItemSelectorWrapperComponent.js');
        $this->loadReactCSS('ItemSelectorWrapperComponent.css');
        $disabled = 'DISABLED';
        $readonly = 'READONLY';
        if ($this->hasPermissions(RENEWALS_PERMISSION)) {
            $readonly = null;
            $disabled = null;
        }
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
        if (!$disabled) {
            $prices   = '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText">
            <input name="renContract[1][curUnitSale]"
              type="text" value="' . Controller::htmlInputText($dsRenContract->getValue(DBEJRenContract::curUnitSale)) . '"
              size="10"
              id="annualSalePrice"
              readonly
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::curUnitSale)
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renContract[1][curUnitCost]"
              id="annualCostPrice"
              type="text" value="' . Controller::htmlInputText($dsRenContract->getValue(DBEJRenContract::curUnitCost)) . '"
              size="10"
              readonly
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::curUnitCost)
                ) . '</span> </td>
        </tr>';
            $declined = '<tr>
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
        $urlItemPopup = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                'renewalTypeID' => CONFIG_CONTRACT_RENEWAL_TYPE_ID,
                'htmlFmt'       => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit  = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSiteEdit  = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSitePopup = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $dbeItem      = new DBEItem($this);
        $dbeItem->getRow($dsRenContract->getValue(DBECustomerItem::itemID));
        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($dsRenContract->getValue(DBECustomerItem::customerID));
        $isDirectDebitAllowed = $dsCustomer->getValue(DBECustomer::sortCode) && $dsCustomer->getValue(
                DBECustomer::accountName
            ) && $dsCustomer->getValue(DBECustomer::accountNumber);
        $expiryDate           = null;
        if ($installationDate = DateTime::createFromFormat(
            'Y-m-d',
            $dsRenContract->getValue(DBECustomerItem::installationDate)
        )) {
            $expiryDate = getExpiryDate(
                $installationDate,
                $dsRenContract->getValue(DBECustomerItem::initialContractLength)
            )->format('d/m/Y');
        }
        $officeItems = new DBEItem($this);
        $officeItems->getRowsByDescriptionMatch("+CNC +Office +365 +Backup");
        $isOfficeItem = false;
        while (!$isOfficeItem && $officeItems->fetchNext()) {
            $isOfficeItem = $officeItems->getValue(DBEItem::itemID) == $dbeItem->getValue(DBEItem::itemID);
        }
        $isWebroot = $dbeItem->getValue(DBEItem::itemID) == CONFIG_WEBROOT_ITEMTYPEID;
        $isDUO     = $dbeItem->getValue(DBEItem::itemID) == CONFIG_DUO_ITEMID;
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
                'usersDisable'                       => Controller::htmlDisplayText(
                    $dbeItem->getValue(DBEItem::isStreamOne) || $isOfficeItem || $isWebroot || $isDUO ? 'readonly' : ''
                ),
                'salePricePerMonth'                  => $dsRenContract->getValue(DBECustomerItem::salePricePerMonth),
                'costPricePerMonth'                  => $dsRenContract->getValue(DBECustomerItem::costPricePerMonth),
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
                'installationDate'                   => $dsRenContract->getValue(DBEJRenContract::installationDate),
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
                'despatchDate'                       => $dsRenContract->getValue(DBEJRenContract::despatchDate),
                'despatchDateMessage'                => Controller::htmlDisplayText(
                    $dsRenContract->getMessage(DBEJRenContract::despatchDate)
                ),
                'expiryDate'                         => $dsRenContract->getValue(DBEJRenContract::expiryDate),
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
            $url = Controller::buildLink(
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
                    'renewalStatusValue'       => $key,
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
            $this->buRenContract->createNewRenewal(
                $dsOrdhead->getValue(DBEJOrdhead::customerID),
                $DBEJOrdline,
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
                    'ID'     => $this->dsRenContract->getValue(DBEJRenContract::customerItemID)
                )
            );

        }
        header('Location: ' . $urlNext);
    }

    private function addItemToContract($contractCustomerItemId, $itemToAddId)
    {
        if (!$contractCustomerItemId || !$itemToAddId) {
            throw new InvalidArgumentException('ContractCustomerItemId and itemToAddId is mandatory');
        }
        $dsRenContract = new DataSet($this);
        $this->buRenContract->getRenContractByID(
            $contractCustomerItemId,
            $dsRenContract
        );
        $customerId      = $dsRenContract->getValue(DBEJRenContract::customerID);
        $dbeCustomerItem = new DBECustomerItem($this);
        if (!$dbeCustomerItem->getRow($itemToAddId)) {
            throw new Exception('Item not found');
        }
        if ($customerId !== $dbeCustomerItem->getValue(DBECustomerItem::customerID)) {
            throw new Exception('The item does not belong to the same customer');
        }
        $dbeCustomerItem->addContract($itemToAddId, $contractCustomerItemId);
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
                $urlEdit        = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $customerItemID
                    )
                );
                $txtEdit        = '[edit]';
                $urlList        = Controller::buildLink(
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
                        'quantity'        => $dsRenContract->getValue(DBEJRenContract::users),
                        'notes'           => Controller::dateYMDtoDMY($dsRenContract->getValue(DBEJRenContract::notes)),
                        'costAnnum'       => utf8MoneyFormat(
                            UK_MONEY_FORMAT,
                            $dsRenContract->getValue(DBEJContract::curUnitCost)
                        ),
                        'saleAnnum'       => utf8MoneyFormat(
                            UK_MONEY_FORMAT,
                            $dsRenContract->getValue(DBEJContract::curUnitSale)
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
            }//while $dsRenContract->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenContractList',
            true
        );
        $this->parsePage();
    }
}
