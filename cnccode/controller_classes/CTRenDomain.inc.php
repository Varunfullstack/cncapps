<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEArecord.inc.php');

class CTRenDomain extends CTCNC
{
    var $dsRenDomain = '';
    var $buRenDomain = '';
    var $buCustomerItem = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "renewals",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buRenDomain = new BURenDomain($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenDomain = new DSForm($this);
        $this->dsRenDomain->copyColumnsFrom($this->buRenDomain->dbeRenDomain);
        $this->dsRenDomain->addColumn('customerName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenDomain->addColumn('siteName', DA_STRING, DA_ALLOW_NULL);
        $this->dsRenDomain->addColumn('invoiceFromDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenDomain->addColumn('invoiceToDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsRenDomain->addColumn('itemDescription', DA_STRING, DA_ALLOW_NULL);
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
            case 'editArecord':
            case 'createArecord':
                $this->editArecord();
                break;
            case 'deleteArecord':
                $this->deleteArecord();
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
        $this->setPageTitle('Domain Names');
        $this->setTemplateFiles(
            array('RenDomainList' => 'RenDomainList.inc')
        );

        $this->buRenDomain->getAll($dsRenDomain, $_REQUEST['orderBy']);

        if ($dsRenDomain->rowCount() > 0) {
            $this->template->set_block('RenDomainList', 'rowBlock', 'rows');
            while ($dsRenDomain->fetchNext()) {

                $customerItemID = $dsRenDomain->getValue('customerItemID');

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
                        'customerName' => $dsRenDomain->getValue('customerName'),
                        'itemDescription' => $dsRenDomain->getValue('itemDescription'),
                        'domain' => $dsRenDomain->getValue('notes'),
                        'invoiceFromDate' => $this->dateYMDtoDMY($dsRenDomain->getValue('invoiceFromDate')),
                        'invoiceToDate' => $this->dateYMDtoDMY($dsRenDomain->getValue('invoiceToDate')),
                        'urlEdit' => $urlEdit,
                        'urlList' => $urlList,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse('rows', 'rowBlock', true);
            }//while $dsRenDomain->fetchNext()
        }
        $this->template->parse('CONTENTS', 'RenDomainList', true);
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

            $ID = $this->buRenDomain->createNewRenewal(
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
        $dsRenDomain = &$this->dsRenDomain; // ref to class var


        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buRenDomain->getRenDomainByID($_REQUEST['ID'], $dsRenDomain);
                $customerItemID = $_REQUEST['ID'];
            } else {                                                                    // creating new
                $dsRenDomain->initialise();
                $dsRenDomain->setValue('customerItemID', '0');
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenDomain->initialise();
            $dsRenDomain->fetchNext();
            $customerItemID = $dsRenDomain->getValue('customerItemID');
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
        $this->setPageTitle('Domain Name');
        $this->setTemplateFiles(
            array('RenDomainEdit' => 'RenDomainEdit.inc')
        );

        if ($this->hasPermissions(PHPLIB_PERM_RENEWALS)) {
            $readonly = ''; // not
            $disabled = ''; // not
            $declined =
                '<tr>
            <td class="promptText">Declined</td>
            <td class="fieldText">
            <input
              name="renDomain[1][declinedFlag]" 
              {readonly}
              type="checkbox"
              value="Y"
              ' . Controller::htmlChecked($dsRenDomain->getValue('declinedFlag')) . '
            /></td>
        </tr>';

            $pricePerMonth =
                '<tr>
            <td class="promptText">Sale Price/Annum </td>
            <td class="fieldText"><input
              name="renBroadband[1][salePrice]"
              type="text" value="' . $dsRenDomain->getValue('salePrice') . '"
              size="10"
              maxlength="10">
                    <span class="formErrorMessage">' . Controller::htmlDisplayText($dsRenDomain->getMessage('salePrice')) . '</span> </td>
        </tr>
        <tr>
            <td class="promptText">Cost Price/Annum</td>
            <td class="fieldText"><input
              name="renBroadband[1][costPrice]"
              type="text" value="' . $dsRenDomain->getValue('costPrice') . '"
              {readonly}
              size="10"
              maxlength="10" />
                    <span class="formErrorMessage">' . Controller::htmlDisplayText($dsRenDomain->getMessage('costPrice')) . '</span> </td>
        </tr>';


        } else {
            $readonly = CTCNC_HTML_READONLY;
            $disabled = CTCNC_HTML_DISABLED;
            $pricePerMonth = '';
        }

        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'renewalTypeID' => CONFIG_DOMAIN_RENEWAL_TYPE_ID,
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
                'pricePerMonth' => $pricePerMonth,
                'costPrice' => $dsRenDomain->getValue('costPrice'),
                'salePrice' => $dsRenDomain->getValue('salePrice'),
                'customerItemID' => $dsRenDomain->getValue('customerItemID'),
                'customerName' => Controller::htmlDisplayText($dsRenDomain->getValue('customerName')),
                'customerID' => Controller::htmlDisplayText($dsRenDomain->getValue('customerID')),
                'siteName' => Controller::htmlDisplayText($dsRenDomain->getValue('siteName')),
                'siteNo' => $dsRenDomain->getValue('siteNo'),
                'itemDescription' => Controller::htmlDisplayText($dsRenDomain->getValue('itemDescription')),
                'itemID' => Controller::htmlDisplayText($dsRenDomain->getValue('itemID')),
                'invoiceFromDate' => $dsRenDomain->getValue('invoiceFromDate'),
                'installationDate' => $this->dateYMDtoDMY($dsRenDomain->getValue('installationDate')),
                'invoiceToDate' => $dsRenDomain->getValue('invoiceToDate'),
                'invoicePeriodMonths' => Controller::htmlInputText($dsRenDomain->getValue('invoicePeriodMonths')),
                'invoicePeriodMonthsMessage' => Controller::htmlDisplayText($dsRenDomain->getMessage('invoicePeriodMonths')),
                'totalInvoiceMonths' => Controller::htmlInputText($dsRenDomain->getValue('totalInvoiceMonths')),
                'notes' => Controller::htmlInputText($dsRenDomain->getValue('notes')),
                'notesMessage' => Controller::htmlDisplayText($dsRenDomain->getMessage('notes')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'urlItemEdit' => $urlItemEdit,
                'urlItemPopup' => $urlItemPopup,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList,
                'declined' => $declined,
                'declinedFlag' => $dsRenDomain->getValue('declinedFlag'),
                'disabled' => $disabled,
                'readonly' => $readonly,
                'internalNotes' => Controller::htmlTextArea($dsRenDomain->getValue('internalNotes'))

            )
        );

        $dbeArecord = new DBEArecord($this);
        $dbeArecord->setValue('customerItemID', $dsRenDomain->getValue('customerItemID'));

        $dbeArecord->getRowsByColumn('customerItemID', 'name');

        $this->template->set_block('RenDomainEdit', 'arecordBlock', 'arecords');

        $urlAddArecord =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'createArecord',
                    'customerItemID' => $dsRenDomain->getValue('customerItemID')
                )
            );
        $this->template->set_var(array('urlAddArecord' => $urlAddArecord));

        while ($dbeArecord->fetchNext()) {

            $urlEditArecord =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'editArecord',
                        'arecordID' => $dbeArecord->getPKValue()
                    )
                );

            $urlDeleteArecord =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'deleteArecord',
                        'arecordID' => $dbeArecord->getPKValue()
                    )
                );
            $this->template->set_var(
                array(
                    'arecordID' => $dbeArecord->getValue('arecordID'),
                    'arecordName' => $dbeArecord->getValue('name'),
                    'arecordDestinationIp' => $dbeArecord->getValue('destinationIp'),
                    'arecordFunction' => $dbeArecord->getValue('function'),
                    'arecordType' => $dbeArecord->getValue('type'),
                    'urlDeleteArecord' => $urlDeleteArecord,
                    'urlEditArecord' => $urlEditArecord
                )
            );
            $this->template->parse('arecords', 'arecordBlock', true);
        } // while


        $this->template->parse('CONTENTS', 'RenDomainEdit', true);
        $this->parsePage();
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsRenDomain = &$this->dsRenDomain;
        $this->formError = (!$this->dsRenDomain->populateFromArray($_REQUEST['renDomain']));
        if ($this->formError) {
            if ($this->dsRenDomain->getValue('customerItemID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buRenDomain->updateRenDomain($this->dsRenDomain);

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
                                     'ID' => $this->dsRenDomain->getValue('customerItemID')
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

    function editArecord()
    {
        $this->setMethodName('editArecord');

        $dsArecord = new DSForm($this);
        $dbeArecord = new DBEArecord($this);
        $dsArecord->copyColumnsFrom($dbeArecord);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->setMethodName('updateRenDomain');
            $formError = (!$dsArecord->populateFromArray($_REQUEST['arecord']));
            if (!$formError) {
                $this->buRenDomain->updateArecord($dsArecord);

                $urlNext =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'ID' => $dsArecord->getValue('customerItemID')
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        } else {
            if ($_REQUEST['arecordID']) {                      // editing
                $this->buRenDomain->getArecordById($_REQUEST['arecordID'], $dsArecord);
            } else {                                               // create new record
                $dsArecord->setValue('arecordID', 0);
                $dsArecord->setValue('customerItemID', $_REQUEST['customerItemID']);
            }
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'editArecord',
                    'ordheadID' => $arecordID,
                    'customerItemID' => $customerItemID
                )
            );
        $this->setPageTitle('Edit A-Record');

        $this->setTemplateFiles(array('ArecordEdit' => 'ArecordEdit.inc'));

        $this->template->set_var(
            array(
                'customerItemID' => $dsArecord->getValue('customerItemID'),
                'arecordID' => $dsArecord->getValue('arecordID'),
                'type' => $dsArecord->getValue('type'),
                'typeMessage' => $dsArecord->getMessage('type'),
                'name' => $dsArecord->getValue('name'),
                'nameMessage' => $dsArecord->getMessage('name'),
                'function' => $dsArecord->getValue('function'),
                'functionMessage' => $dsArecord->getMessage('function'),
                'destinationIp' => $dsArecord->getValue('destinationIp'),
                'destinationIpMessage' => $dsArecord->getMessage('destinationIp'),
                'urlUpdate' => $urlUpdate
            )
        );

        $this->template->parse('CONTENTS', 'ArecordEdit', true);
        $this->parsePage();
    }

    function deleteArecord()
    {
        $this->setMethodName('deleteArecord');

        if (!$this->buRenDomain->getArecordById($_REQUEST['arecordID'], $dsArecord)) {
            $this->raiseError('arecordID ' . $_REQUEST['arecordID'] . ' not found');
            exit;
        }

        $this->buRenDomain->deleteArecord($_REQUEST['arecordID']);
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'ID' => $dsArecord->getValue('customerItemID')
                )
            );

        header('Location: ' . $urlNext);
        exit;
    }
}// end of class
?>