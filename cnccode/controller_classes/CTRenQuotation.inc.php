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
    /** @var DSForm */
    public $dsRenQuotation;
    /** @var BURenQuotation */
    public $buRenQuotation;
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
        $this->setMenuId(601);
        $this->buRenQuotation = new BURenQuotation($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsRenQuotation = new DSForm($this);
        $this->dsRenQuotation->copyColumnsFrom($this->buRenQuotation->dbeRenQuotation);
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::itemID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::customerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::nextPeriodStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::nextPeriodEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::siteName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::costPrice,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsRenQuotation->addColumn(
            DBEJRenQuotation::salePrice,
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
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsRenQuotation = &$this->dsRenQuotation; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buRenQuotation->getRenQuotationByID(
                    $this->getParam('ID'),
                    $dsRenQuotation
                );
                $customerItemID = $this->getParam('ID');
            } else {                                                                    // creating new
                $dsRenQuotation->initialise();
                $dsRenQuotation->setValue(
                    DBEJRenQuotation::customerItemID,
                    '0'
                );
                $customerItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsRenQuotation->initialise();
            $dsRenQuotation->fetchNext();
            $customerItemID = $dsRenQuotation->getValue(DBEJRenQuotation::customerItemID);
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
        $this->setPageTitle('Edit Renewal');
        $this->setTemplateFiles(
            array('RenQuotationEdit' => 'RenQuotationEdit.inc')
        );

        $this->loadReactScript('ItemSelectorWrapperComponent.js');
        $this->loadReactCSS('ItemSelectorWrapperComponent.css');

        $disabled = CTCNC_HTML_DISABLED;
        $readonly = 'READONLY';

        if ($this->hasPermissions(RENEWALS_PERMISSION)) {
            $disabled = null; // not
            $readonly = null;
            $this->template->set_var(
                array(
                    'salePrice' => Controller::htmlDisplayText($dsRenQuotation->getValue(DBEJRenQuotation::salePrice)),
                    'costPrice' => Controller::htmlDisplayText($dsRenQuotation->getValue(DBEJRenQuotation::costPrice))
                )
            );
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
        $salesOrderLink = null;
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
                'startDate'            => $dsRenQuotation->getValue(DBEJRenQuotation::startDate),
                'calculatedExpiryDate' =>
                    DateTime::createFromFormat(
                        'Y-m-d',
                        $dsRenQuotation->getValue(DBEJRenQuotation::startDate)
                    )->add(
                        new DateInterval('P1Y')
                    )->format('d/m/Y'),
                'dateGenerated'        => $dsRenQuotation->getValue(DBEJRenQuotation::dateGenerated),
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

            $typeSelected = ($dsRenQuotation->getValue(
                    DBEJRenQuotation::renQuotationTypeID
                ) == $dbeRenQuotationType->getValue(
                    DBERenQuotationType::renQuotationTypeID
                )) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'typeSelected'       => $typeSelected,
                    'renQuotationTypeID' => $dbeRenQuotationType->getPKValue(),
                    'typeDescription'    => $dbeRenQuotationType->getValue(DBERenQuotationType::description)
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
            $this->buRenQuotation->createNewRenewal(
                $dsOrdhead->getValue(DBEJOrdhead::customerID),
                $DBEJOrdline->getValue(DBEJOrdline::itemID),
                $renewalCustomerItemID,
                $DBEJOrdline->getValue(DBEJOrdline::curUnitSale),
                $DBEJOrdline->getValue(DBEJOrdline::curUnitCost),
                $DBEJOrdline->getValue(DBEJOrdline::qtyOrdered),
                $dsOrdhead->getValue(DBEJOrdhead::delSiteNo)
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
        $this->formError = (!$this->dsRenQuotation->populateFromArray($this->getParam('renQuotation')));

        if ($this->formError) {

            if ($this->dsRenQuotation->getValue(
                DBEJRenQuotation::customerItemID
            )) {                    // attempt to insert
                $this->setAction('edit');
            } else {
                $this->setAction('create');
            }
            $this->edit();
            exit;
        }

        $this->buRenQuotation->updateRenQuotation($this->dsRenQuotation);

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
                        'ID'     => $this->dsRenQuotation->getValue(DBEJRenQuotation::customerItemID)
                    )
                );

        }

        header('Location: ' . $urlNext);
    }// end function editActivity()

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Renewals');

        $this->setTemplateFiles(
            array('RenQuotationList' => 'RenQuotationList.inc')
        );

        if (!$this->getParam('orderBy')) {
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
        $dsRenQuotation = new DataSet($this);
        $this->buRenQuotation->getAll(
            $dsRenQuotation,
            $this->getParam('orderBy')
        );

        if ($dsRenQuotation->rowCount()) {
            $this->template->set_block(
                'RenQuotationList',
                'rowBlock',
                'rows'
            );

            while ($dsRenQuotation->fetchNext()) {

                $customerItemID = $dsRenQuotation->getValue(DBEJRenQuotation::customerItemID);

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

                $latestQuoteSent = null;

                if ($dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent)) {
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
                    [
                        'customerName'        => $dsRenQuotation->getValue(DBEJRenQuotation::customerName),
                        'itemDescription'     => $dsRenQuotation->getValue(DBEJRenQuotation::itemDescription),
                        'type'                => $dsRenQuotation->getValue(DBEJRenQuotation::type),
                        'startDate'           => $dsRenQuotation->getValue(DBEJRenQuotation::startDate),
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
                        'latestQuoteSent'     => $latestQuoteSent ? $latestQuoteSent->format('d/m/Y H:i:s') : null,
                        'comments'            => substr(
                            $dsRenQuotation->getValue(DBEJRenQuotation::customerItemNotes),
                            0,
                            30
                        )
                    ]
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
}
