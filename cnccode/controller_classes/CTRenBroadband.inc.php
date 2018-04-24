<?php
/**
 * Broadband renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEBroadbandServiceType.inc.php');
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');

class CTRenBroadband extends CTCNC
{
    var $dsRenBroadband = '';
    var $buRenBroadband = '';
    var $buCustomerItem = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "renewals"
        ];
        if (!self::canAccess($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRenBroadband = new BURenBroadband($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenBroadband = new DSForm($this);
        $this->dsRenBroadband->copyColumnsFrom($this->buRenBroadband->dbeRenBroadband);
        $this->dsRenBroadband->addColumn('customerName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('siteDesc', DA_ALLOW_NULL, DA_STRING);
        $this->dsRenBroadband->addColumn('invoiceFromDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('invoiceToDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('itemID', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('itemDescription', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('siteName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('costPrice', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenBroadband->addColumn('salePrice', DA_STRING, DA_ALLOW_NULL);
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
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Internet Services');
        $this->setTemplateFiles(
            array('RenBroadbandList' => 'RenBroadbandList.inc')
        );

        $this->buRenBroadband->getAll($dsRenBroadband, $_REQUEST['orderBy']);

        if ($dsRenBroadband->rowCount() > 0) {
            $this->template->set_block('RenBroadbandList', 'rowBlock', 'rows');
            while ($dsRenBroadband->fetchNext()) {

                $customerItemID = $dsRenBroadband->getValue('customerItemID');

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID' => $customerItemID
                        )
                    );

                $urlList =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list'
                        )
                    );

                $txtEdit = '[edit]';

                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'delete',
                            'ID' => $customerItemID
                        )
                    );
                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'customerItemID' => $customerItemID,
                        'customerName' => $dsRenBroadband->getValue('customerName'),
                        'itemDescription' => $dsRenBroadband->getValue('itemDescription'),
                        'ispID' => $dsRenBroadband->getValue('ispID'),
                        'adslPhone' => $dsRenBroadband->getValue('adslPhone'),
                        'salePricePerMonth' => $dsRenBroadband->getValue('salePricePerMonth'),
                        'costPricePerMonth' => $dsRenBroadband->getValue('costPricePerMonth'),
                        'invoiceFromDate' => $this->dateYMDtoDMY($dsRenBroadband->getValue('invoiceFromDate')),
                        'invoiceToDate' => $this->dateYMDtoDMY($dsRenBroadband->getValue('invoiceToDate')),
                        'urlEdit' => $urlEdit,
                        'urlList' => $urlList,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse('rows', 'rowBlock', true);
            }//while $dsRenBroadband->fetchNext()
        }
        $this->template->parse('CONTENTS', 'RenBroadbandList', true);
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

            $this->buRenBroadband->createNewRenewal(
                $dsOrdhead->getValue('customerID'),
                $dsOrdhead->getValue('delSiteNo'),
                $dsOrdline->getValue('itemID'),
                $renewalCustomerItemID                // returned by function
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
        $dsRenBroadband = &$this->dsRenBroadband; // ref to class var


        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buRenBroadband->getRenBroadbandByID($_REQUEST['ID'], $dsRenBroadband);
                $customerItemID = $_REQUEST['ID'];
            } else {                                                                    // creating new
                $dsRenBroadband->initialise();
                $dsRenBroadband->setValue('customerItemID', '0');
            }
        } else {                                                                        // form validation error
            $dsRenBroadband->initialise();
            $dsRenBroadband->fetchNext();
            $customerItemID = $dsRenBroadband->getValue('customerItemID');
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
        $urlEmailTo =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'emailTo',
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
        $this->setPageTitle('Internet Service');
        $this->setTemplateFiles(
            array('RenBroadbandEdit' => 'RenBroadbandEdit.inc')
        );

        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $disabled = ''; // not
            $pricePerMonth =
                '<tr>
            <td class="promptText">Sale Price/Month </td>
            <td class="fieldText"><input
              name="renBroadband[1][salePricePerMonth]"
              type="text" value="' . Controller::htmlInputText($dsRenBroadband->getValue('salePricePerMonth')) . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText($dsRenBroadband->getMessage('salePricePerMonth')) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Month</td>
            <td class="fieldText"><input
              name="renBroadband[1][costPricePerMonth]"
              type="text" value="' . Controller::htmlInputText($dsRenBroadband->getValue('costPricePerMonth')) . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText($dsRenBroadband->getMessage('costPricePerMonth')) . '</span> </td>
        </tr>';

            $urlSiteEdit =
                $this->buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action' => CTCNC_ACT_SITE_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
            $urlSitePopup =
                $this->buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action' => CTCNC_ACT_SITE_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'pricePerMonth' => $pricePerMonth,
                    'urlSitePopup' => $urlSitePopup,
                    'urlSiteEdit' => $urlSiteEdit
                )
            );
        } else {
            $disabled = CTCNC_HTML_DISABLED;
            $readonly = CTCNC_HTML_READONLY;
        }

        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_BROADBAND_RENEWAL_TYPE_ID,
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

        $urlPrintContract =
            $this->buildLink(
                'CustomerItem.php',
                array(
                    'action' => 'printContract',
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
                'itemDescription' => Controller::htmlDisplayText($dsRenBroadband->getValue('itemDescription')),
                'salePricePerMonth' => $dsRenBroadband->getValue('salePricePerMonth'),
                'costPricePerMonth' => $dsRenBroadband->getValue('costPricePerMonth'),
                'customerID' => Controller::htmlDisplayText($dsRenBroadband->getValue('customerID')),
                'siteDesc' => Controller::htmlDisplayText($dsRenBroadband->getValue('siteName')),
                'siteNo' => $dsRenBroadband->getValue('siteNo'),
                'itemID' => Controller::htmlDisplayText($dsRenBroadband->getValue('itemID')),
                'customerItemID' => $dsRenBroadband->getValue('customerItemID'),
                'customerName' => Controller::htmlDisplayText($dsRenBroadband->getValue('customerName')),
                'invoiceFromDate' => $dsRenBroadband->getValue('invoiceFromDate'),
                'invoiceToDate' => $dsRenBroadband->getValue('invoiceToDate'),
                'months' => Controller::htmlInputText($dsRenBroadband->getValue('months')),
                'monthsMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('months')),
                'invoicePeriodMonths' => Controller::htmlInputText($dsRenBroadband->getValue('invoicePeriodMonths')),
                'invoicePeriodMonthsMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('invoicePeriodMonths')),
                'totalInvoiceMonths' => Controller::htmlInputText($dsRenBroadband->getValue('totalInvoiceMonths')),
                'invoicePeriodMonthsMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('totalInvoiceMonths')),
                'adslPhone' => Controller::htmlInputText($dsRenBroadband->getValue('adslPhone')),
                'adslPhoneMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('adslPhone')),
                'macCode' => Controller::htmlInputText($dsRenBroadband->getValue('macCode')),
                'macCodeMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('macCode')),
                'reference' => Controller::htmlInputText($dsRenBroadband->getValue('reference')),
                'referenceMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('reference')),
                'defaultGateway' => Controller::htmlInputText($dsRenBroadband->getValue('defaultGateway')),
                'defaultGatewayMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('defaultGateway')),
                'networkAddress' => Controller::htmlInputText($dsRenBroadband->getValue('networkAddress')),
                'networkAddressMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('networkAddress')),
                'subnetMask' => Controller::htmlInputText($dsRenBroadband->getValue('subnetMask')),
                'subnetMaskMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('subnetMask')),
                'routerIPAddress' => Controller::htmlInputText($dsRenBroadband->getValue('routerIPAddress')),
                'routerIPAddressMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('routerIPAddress')),
                'userName' => Controller::htmlInputText($dsRenBroadband->getValue('userName')),
                'userNameMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('userName')),
                'password' => Controller::htmlInputText($dsRenBroadband->getValue('password')),
                'etaDate' => $this->dateYMDtoDMY($dsRenBroadband->getValue('etaDate')),
                'installationDate' => $this->dateYMDtoDMY($dsRenBroadband->getValue('installationDate')),
                'ispID' => Controller::htmlInputText($dsRenBroadband->getValue('ispID')),
                'dualBroadbandFlagChecked' => Controller::htmlChecked($dsRenBroadband->getValue('dualBroadbandFlag')),
                'dnsCompany' => Controller::htmlInputText($dsRenBroadband->getValue('dnsCompany')),
                'ipCurrentNo' => Controller::htmlInputText($dsRenBroadband->getValue('ipCurrentNo')),
                'mx' => Controller::htmlInputText($dsRenBroadband->getValue('mx')),
                'secureServer' => Controller::htmlInputText($dsRenBroadband->getValue('secureServer')),
                'vpns' => Controller::htmlInputText($dsRenBroadband->getValue('vpns')),
                'owa' => Controller::htmlInputText($dsRenBroadband->getValue('owa')),
                'oma' => Controller::htmlInputText($dsRenBroadband->getValue('oma')),
                'remotePortal' => Controller::htmlInputText($dsRenBroadband->getValue('remotePortal')),
                'smartHost' => Controller::htmlInputText($dsRenBroadband->getValue('smartHost')),
                'preparationRecords' => Controller::htmlInputText($dsRenBroadband->getValue('preparationRecords')),
                'assignedTo' => Controller::htmlInputText($dsRenBroadband->getValue('assignedTo')),
                'initialSpeedTest' => Controller::htmlInputText($dsRenBroadband->getValue('initialSpeedTest')),
                'preMigrationNotes' => Controller::htmlInputText($dsRenBroadband->getValue('preMigrationNotes')),
                'postMigrationNotes' => Controller::htmlInputText($dsRenBroadband->getValue('postMigrationNotes')),
                'docsUpdatedAndChecksCompleted' => Controller::htmlInputText($dsRenBroadband->getValue('docsUpdatedAndChecksCompleted')),
                'passwordMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('password')),
                'etaDateMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('etaDate')),
                'installationDateMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('installationDate')),
                'ispIDMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('ispID')),
                'dnsCompanyMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('dnsCompany')),
                'ipCurrentNoMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('ipCurrentNo')),
                'mxMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('mx')),
                'secureServerMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('secureServer')),
                'vpnsMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('vpns')),
                'owaMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('owa')),
                'omaMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('oma')),
                'remotePortalMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('remotePortal')),
                'smartHostMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('smartHost')),
                'preparationRecordsMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('preparationRecords')),
                'assignedToMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('assignedTo')),
                'initialSpeedTestMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('initialSpeedTest')),
                'preMigrationNotesMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('preMigrationNotes')),
                'postMigrationNotesMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('postMigrationNotes')),
                'docsUpdatedAndChecksCompletedMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('docsUpdatedAndChecksCompleted')),
                'declinedFlagChecked' => Controller::htmlChecked($dsRenBroadband->getValue('declinedFlag')),
                'bandwidthAllowance' => Controller::htmlInputText($dsRenBroadband->getValue('bandwidthAllowance')),
                'bandwidthAllowanceMessage' => Controller::htmlDisplayText($dsRenBroadband->getMessage('bandwidthAllowance')),
                'urlUpdate' => $urlUpdate,
                'urlItemEdit' => $urlItemEdit,
                'urlItemPopup' => $urlItemPopup,
                'urlEditCustomerItem' => $urlEditCustomerItem,
                'internalNotes' => Controller::htmlTextArea($dsRenBroadband->getValue('internalNotes')),
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList,
                'disabled' => $disabled,
                'readonly' => $readonly,
                'urlEmailTo' => $urlEmailTo
            )
        );

        /*
         * build broadband service type dropdown
         */
        $dbeBroadbandServiceType = new DBEBroadbandServiceType($this);

        $dbeBroadbandServiceType->getRows();

        $this->template->set_block('RenBroadbandEdit', 'serviceTypeBlock', 'serviceTypes');

        while ($dbeBroadbandServiceType->fetchNext()) {

            $serviceTypeSelected = ($dsRenBroadband->getValue('broadbandSericeTypeID') == $dbeBroadbandServiceType->getValue('broadbandSericeTypeID')) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'serviceTypeSelected' => $serviceTypeSelected,
                    'serviceTypeID' => $key,
                    'orderTypeDescription' => $value
                )
            );
            $this->template->parse('serviceTypes', 'serviceTypeBlock', true);
        }


        $this->template->parse('CONTENTS', 'RenBroadbandEdit', true);
        $this->parsePage();
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsRenBroadband = &$this->dsRenBroadband;
        $this->formError = (!$this->dsRenBroadband->populateFromArray($_REQUEST['renBroadband']));
        if ($this->formError) {
            if ($this->dsRenBroadband->getValue('customerItemID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buRenBroadband->updateRenBroadband($this->dsRenBroadband);

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
                                     'ID' => $this->dsRenBroadband->getValue('customerItemID')
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
        if (!$this->buRenBroadband->deleteRenBroadband($_REQUEST['customerItemID'])) {
            $this->displayFatalError('Cannot delete this broadband contract');
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
     */
    function emailTo()
    {
        $this->setMethodName('emailTo');
        $this->buRenBroadband->sendEmailTo($_REQUEST['customerItemID'], $_REQUEST['emailAddress']);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'action' => 'edit',
                                 'ID' => $_REQUEST['customerItemID']
                             )
            );

        header('Location: ' . $urlNext);

    }// end function emailTo()
}// end of class
?>