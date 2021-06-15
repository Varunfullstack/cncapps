<?php
/**
 * Broadband renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');

class CTRenBroadband extends CTCNC
{
    const InitialContractLengthValues = [
        12,
        24,
        36,
        48,
        60
    ];
    public $dsRenBroadband;
    public $buRenBroadband;
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
        $this->setMenuId(603);
        $this->buRenBroadband = new BURenBroadband($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenBroadband = new DSForm($this);
        $this->dsRenBroadband->copyColumnsFrom($this->buRenBroadband->dbeRenBroadband);
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
            case 'emailTo':
                $this->emailTo();
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
        $dsRenBroadband = &$this->dsRenBroadband; // ref to class var
        $customerItemID = null;
        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buRenBroadband->getRenBroadbandByID(
                    $this->getParam('ID'),
                    $dsRenBroadband
                );
                $customerItemID = $this->getParam('ID');
            } else {                                                                    // creating new
                $dsRenBroadband->initialise();
                $dsRenBroadband->setValue(
                    DBEJRenBroadband::customerItemID,
                    null
                );
            }
        } else {                                                                        // form validation error
            $dsRenBroadband->initialise();
            $dsRenBroadband->fetchNext();
            $customerItemID = $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID);
        }
        $urlUpdate      = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'update',
                'ordheadID'      => $this->getParam('ordheadID'),
                'customerItemID' => $customerItemID
            )
        );
        $urlEmailTo     = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'emailTo',
                'customerItemID' => $customerItemID
            )
        );
        $urlDisplayList = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'list'
            )
        );
        $this->setPageTitle('Internet Service');
        $this->setTemplateFiles(
            array('RenBroadbandEdit' => 'RenBroadbandEdit.inc')
        );
        $this->loadReactScript('ItemSelectorWrapperComponent.js');
        $this->loadReactCSS('ItemSelectorWrapperComponent.css');
        $disabled = CTCNC_HTML_DISABLED;
        $readonly = CTCNC_HTML_READONLY;
        if ($this->hasPermissions(RENEWALS_PERMISSION)) {
            $disabled      = null;
            $readonly      = null;
            $pricePerMonth = '<tr>
            <td class="promptText">Sale Price/Month </td>
            <td class="fieldText">
            <input
              name="renBroadband[1][salePricePerMonth]"
              type="text" value="' . Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth)
                ) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::salePricePerMonth)
                ) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Month</td>
            <td class="fieldText"><input
              name="renBroadband[1][costPricePerMonth]"
              type="text" value="' . Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth)
                ) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::costPricePerMonth)
                ) . '</span> </td>
        </tr>';
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
            $this->template->set_var(
                array(
                    'pricePerMonth' => $pricePerMonth,
                    'urlSitePopup'  => $urlSitePopup,
                    'urlSiteEdit'   => $urlSiteEdit
                )
            );
        }
        $this->template->setBlock(
            'RenBroadbandEdit',
            'initialContractLengthBlock',
            'initialContractLengths'
        );
        $this->parseInitialContractLength($dsRenBroadband->getValue(DBECustomerItem::initialContractLength));
        $urlItemPopup = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'        => CTCNC_ACT_DISP_ITEM_POPUP,
                'renewalTypeID' => CONFIG_BROADBAND_RENEWAL_TYPE_ID,
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
        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($dsRenBroadband->getValue(DBEJRenBroadband::customerID));
        $isDirectDebitAllowed = $dsCustomer->getValue(DBECustomer::sortCode) && $dsCustomer->getValue(
                DBECustomer::accountName
            ) && $dsCustomer->getValue(DBECustomer::accountNumber);
        $installationDate = DateTime::createFromFormat(
            'Y-m-d',
            $dsRenBroadband->getValue(DBECustomerItem::installationDate)
        );
        $this->template->set_var(
            array(
                'itemDescription'                      => Controller::htmlDisplayText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription)
                ),
                'salePricePerMonth'                    => $dsRenBroadband->getValue(
                    DBEJRenBroadband::salePricePerMonth
                ),
                'costPricePerMonth'                    => $dsRenBroadband->getValue(
                    DBEJRenBroadband::costPricePerMonth
                ),
                'customerID'                           => Controller::htmlDisplayText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::customerID)
                ),
                'siteDesc'                             => Controller::htmlDisplayText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::siteName)
                ),
                'siteNo'                               => $dsRenBroadband->getValue(DBEJRenBroadband::siteNo),
                'itemID'                               => Controller::htmlDisplayText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::itemID)
                ),
                'customerItemID'                       => $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID),
                'customerName'                         => Controller::htmlDisplayText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::customerName)
                ),
                'invoiceFromDate'                      => $dsRenBroadband->getValue(DBEJRenBroadband::invoiceFromDate),
                'invoiceToDate'                        => $dsRenBroadband->getValue(DBEJRenBroadband::invoiceToDate),
                'months'                               => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::months)
                ),
                'monthsMessage'                        => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::months)
                ),
                'invoicePeriodMonths'                  => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::invoicePeriodMonths)
                ),
                'invoicePeriodMonthsMessage'           => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::invoicePeriodMonths)
                ),
                'totalInvoiceMonths'                   => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::totalInvoiceMonths)
                ),
                'adslPhone'                            => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::adslPhone)
                ),
                'adslPhoneMessage'                     => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::adslPhone)
                ),
                'macCode'                              => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::macCode)
                ),
                'macCodeMessage'                       => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::macCode)
                ),
                'reference'                            => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::reference)
                ),
                'referenceMessage'                     => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::reference)
                ),
                'defaultGateway'                       => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::defaultGateway)
                ),
                'defaultGatewayMessage'                => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::defaultGateway)
                ),
                'networkAddress'                       => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::networkAddress)
                ),
                'networkAddressMessage'                => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::networkAddress)
                ),
                'subnetMask'                           => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::subnetMask)
                ),
                'subnetMaskMessage'                    => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::subnetMask)
                ),
                'routerIPAddress'                      => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::routerIPAddress)
                ),
                'routerIPAddressMessage'               => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::routerIPAddress)
                ),
                'userName'                             => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::userName)
                ),
                'userNameMessage'                      => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::userName)
                ),
                'password'                             => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::password)
                ),
                'etaDate'                              => $dsRenBroadband->getValue(DBEJRenBroadband::etaDate),
                'installationDate'                     => $dsRenBroadband->getValue(DBEJRenBroadband::installationDate),
                'ispID'                                => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::ispID)
                ),
                'dualBroadbandFlagChecked'             => Controller::htmlChecked(
                    $dsRenBroadband->getValue(DBEJRenBroadband::dualBroadbandFlag)
                ),
                'dnsCompany'                           => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::dnsCompany)
                ),
                'ipCurrentNo'                          => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::ipCurrentNo)
                ),
                'mx'                                   => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::mx)
                ),
                'secureServer'                         => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::secureServer)
                ),
                'vpns'                                 => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::vpns)
                ),
                'owa'                                  => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::owa)
                ),
                'oma'                                  => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::oma)
                ),
                'remotePortal'                         => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::remotePortal)
                ),
                'smartHost'                            => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::smartHost)
                ),
                'preparationRecords'                   => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::preparationRecords)
                ),
                'assignedTo'                           => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::assignedTo)
                ),
                'initialSpeedTest'                     => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::initialSpeedTest)
                ),
                'preMigrationNotes'                    => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::preMigrationNotes)
                ),
                'postMigrationNotes'                   => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::postMigrationNotes)
                ),
                'docsUpdatedAndChecksCompleted'        => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::docsUpdatedAndChecksCompleted)
                ),
                'passwordMessage'                      => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::password)
                ),
                'etaDateMessage'                       => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::etaDate)
                ),
                'installationDateMessage'              => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::installationDate)
                ),
                'ispIDMessage'                         => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::ispID)
                ),
                'dnsCompanyMessage'                    => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::dnsCompany)
                ),
                'ipCurrentNoMessage'                   => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::ipCurrentNo)
                ),
                'mxMessage'                            => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::mx)
                ),
                'secureServerMessage'                  => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::secureServer)
                ),
                'vpnsMessage'                          => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::vpns)
                ),
                'owaMessage'                           => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::owa)
                ),
                'omaMessage'                           => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::oma)
                ),
                'remotePortalMessage'                  => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::remotePortal)
                ),
                'smartHostMessage'                     => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::smartHost)
                ),
                'preparationRecordsMessage'            => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::preparationRecords)
                ),
                'assignedToMessage'                    => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::assignedTo)
                ),
                'initialSpeedTestMessage'              => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::initialSpeedTest)
                ),
                'preMigrationNotesMessage'             => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::preMigrationNotes)
                ),
                'postMigrationNotesMessage'            => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::postMigrationNotes)
                ),
                'docsUpdatedAndChecksCompletedMessage' => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::docsUpdatedAndChecksCompleted)
                ),
                'declinedFlagChecked'                  => Controller::htmlChecked(
                    $dsRenBroadband->getValue(DBEJRenBroadband::declinedFlag)
                ),
                'bandwidthAllowance'                   => Controller::htmlInputText(
                    $dsRenBroadband->getValue(DBEJRenBroadband::bandwidthAllowance)
                ),
                'bandwidthAllowanceMessage'            => Controller::htmlDisplayText(
                    $dsRenBroadband->getMessage(DBEJRenBroadband::bandwidthAllowance)
                ),
                'autoGenerateContractInvoiceChecked'   => Controller::htmlChecked(
                    $dsRenBroadband->getValue(DBECustomerItem::autoGenerateContractInvoice)
                ),
                'directDebitFlagChecked'               => Controller::htmlChecked(
                    $dsRenBroadband->getValue(DBECustomerItem::directDebitFlag)
                ),
                'urlUpdate'                            => $urlUpdate,
                'urlItemEdit'                          => $urlItemEdit,
                'urlItemPopup'                         => $urlItemPopup,
                'internalNotes'                        => Controller::htmlTextArea(
                    $dsRenBroadband->getValue(DBEJRenBroadband::internalNotes)
                ),
                'urlDisplayList'                       => $urlDisplayList,
                'disabled'                             => $disabled,
                'readonly'                             => $readonly,
                'urlEmailTo'                           => $urlEmailTo,
                'calculatedExpiryDate'                 => $installationDate ? getExpiryDate(
                    $installationDate,
                    $dsRenBroadband->getValue(DBECustomerItem::initialContractLength)
                )->format('d/m/Y') : null,
                'allowDirectDebit'                     => $dsRenBroadband->getValue(
                    DBEJRenBroadband::allowDirectDebit
                ) == 'Y' ? 'true' : 'false',
                'clientCheckDirectDebit'               => $isDirectDebitAllowed ? 'true' : 'false'
            )
        );
        $this->template->set_block(
            'RenBroadbandEdit',
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
                    'selected'        => $dsRenBroadband->getValue(
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
        $this->template->parse(
            'CONTENTS',
            'RenBroadbandEdit',
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
                $DBEJOrdline->getValue(DBEOrdline::ordheadID),
                $dsOrdhead,
                $dsDontNeedOrdline
            );
            $this->buRenBroadband->createNewRenewal(
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
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buRenBroadband->deleteRenBroadband($this->getParam('customerItemID'))) {
            $this->displayFatalError('Cannot delete this broadband contract');
            exit;
        } else {
            $urlNext = Controller::buildLink(
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
     * Update call activity type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsRenBroadband->populateFromArray($this->getParam('renBroadband')));
        if ($this->formError) {
            if ($this->dsRenBroadband->getValue(DBEJRenBroadband::customerItemID)) {
                $this->setAction('edit');
            } else {
                $this->setAction('create');
            }
            $this->edit();
            exit;
        }
        $this->buRenBroadband->updateRenBroadband($this->dsRenBroadband);
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
                    'ID'     => $this->dsRenBroadband->getValue(DBEJRenBroadband::customerItemID)
                )
            );

        }
        header('Location: ' . $urlNext);
    }

    /**
     * This function creates sales orders for the broadband renewals that are due
     *
     */
    function createRenewalsSalesOrders()
    {
        $this->buRenBroadband->createRenewalsSalesOrders();
    }

    /**
     * Send an email to recipient
     * @access private
     * @throws Exception
     */
    function emailTo()
    {
        $this->setMethodName('emailTo');
        $this->buRenBroadband->sendEmailTo(
            $this->getParam('customerItemID'),
            $this->getParam('emailAddress')
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'edit',
                'ID'     => $this->getParam('customerItemID')
            )
        );
        header('Location: ' . $urlNext);

    }// end function emailTo()

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Internet Services');
        $this->setTemplateFiles(
            array('RenBroadbandList' => 'RenBroadbandList.inc')
        );
        $dsRenBroadband = new DataSet($this);
        $this->buRenBroadband->getAll(
            $dsRenBroadband,
            $this->getParam('orderBy')
        );
        if ($dsRenBroadband->rowCount() > 0) {
            $this->template->set_block(
                'RenBroadbandList',
                'rowBlock',
                'rows'
            );
            while ($dsRenBroadband->fetchNext()) {

                $customerItemID = $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID);
                $urlEdit = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'ID'     => $customerItemID
                    )
                );
                $urlList = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'list'
                    )
                );
                $txtEdit = '[edit]';
                $this->template->set_var(
                    array(
                        'customerItemID'    => $customerItemID,
                        'customerName'      => $dsRenBroadband->getValue(DBEJRenBroadband::customerName),
                        'itemDescription'   => $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription),
                        'ispID'             => $dsRenBroadband->getValue(DBEJRenBroadband::ispID),
                        'adslPhone'         => $dsRenBroadband->getValue(DBEJRenBroadband::adslPhone),
                        'salePricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth),
                        'costPricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth),
                        'invoiceFromDate'   => Controller::dateYMDtoDMY(
                            $dsRenBroadband->getValue(DBEJRenBroadband::invoiceFromDate)
                        ),
                        'invoiceToDate'     => Controller::dateYMDtoDMY(
                            $dsRenBroadband->getValue(DBEJRenBroadband::invoiceToDate)
                        ),
                        'urlEdit'           => $urlEdit,
                        'urlList'           => $urlList,
                        'txtEdit'           => $txtEdit
                    )
                );
                $this->template->parse(
                    'rows',
                    'rowBlock',
                    true
                );
            }//while $dsRenBroadband->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'RenBroadbandList',
            true
        );
        $this->parsePage();
    }
}
