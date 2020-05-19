<?php
/**
 * Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEWarranty.inc.php');
require_once($cfg['path_dbe'] . '/DBERenewalType.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemBillingCategory.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define(
    'CTITEM_MSG_NONE_FND',
    'No items found'
);
define(
    'CTITEM_MSG_ITEM_NOT_FND',
    'Item not found'
);
define(
    'CTITEM_MSG_ITEMID_NOT_PASSED',
    'ItemID not passed'
);
define(
    'CTITEM_MSG_ITEM_ARRAY_NOT_PASSED',
    'Item array not passed'
);
// Actions
define(
    'CTITEM_ACT_ITEM_INSERT',
    'insertItem'
);
define(
    'CTITEM_ACT_ITEM_UPDATE',
    'updateItem'
);
// Page text
define(
    'CTITEM_TXT_NEW_ITEM',
    'Create Item'
);
define(
    'CTITEM_TXT_UPDATE_ITEM',
    'Update Item'
);

class CTItem extends CTCNC
{
    /** @var DSForm */
    public $dsItem;
    /**
     * @var BUItem
     */
    private $buItem;

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
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buItem = new BUItem($this);
        $this->dsItem = new DSForm($this);    // new specialised dataset with form message support
        $this->dsItem->copyColumnsFrom($this->buItem->dbeItem);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
            case CTCNC_ACT_ITEM_ADD:
            case CTCNC_ACT_ITEM_EDIT:
                $this->checkPermissions(SALES_PERMISSION);
                $this->itemForm();
                break;
            case CTITEM_ACT_ITEM_INSERT:
            case CTITEM_ACT_ITEM_UPDATE:
                $this->checkPermissions(SALES_PERMISSION);
                $this->itemUpdate();
                break;
            case 'discontinue':
                $this->checkPermissions(SALES_PERMISSION);
                $this->discontinue();
                break;
            case CTCNC_ACT_DISP_ITEM_POPUP:
            default:
                $this->displayItemSelectPopup();
                break;
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if ($this->getParam('parentIDField')) {
            $this->setSessionParam('itemParentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('itemParentDescField', $this->getParam('parentDescField'));
        }
        if ($this->getParam('parentSlaResponseHoursField')) {
            $this->setSessionParam('itemParentSlaResponseHoursField', $this->getParam('parentSlaResponseHoursField'));
        }
    }

    /**
     * Add/Edit Item
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function itemForm()
    {
        $this->setMethodName('itemForm');
        // initialisation stuff
        if ($this->getAction() == CTCNC_ACT_ITEM_ADD) {
            if ($this->getParam('renewalTypeID')) {
                $renewalTypeID = $this->getParam('renewalTypeID');
            } else {
                $renewalTypeID = false;
            }
            $urlSubmit = $this->itemFormPrepareAdd($renewalTypeID);
        } else {
            $urlSubmit = $this->itemFormPrepareEdit();
        }

        $urlManufacturerPopup =
            Controller::buildLink(
                'Manufacturer.php',
                array(
                    'action'  => 'displayPopup',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlManufacturerEdit =
            Controller::buildLink(
                'Manufacturer.php',
                array(
                    'action'  => 'editManufacturer',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $manufacturerName = null;
        if ($this->dsItem->getValue(DBEItem::manufacturerID)) {
            $dbeManufacturer = new DBEManufacturer($this);
            $dbeManufacturer->getRow($this->dsItem->getValue(DBEItem::manufacturerID));
            $manufacturerName = $dbeManufacturer->getValue(DBEManufacturer::name);
        }

        // template
        $this->setTemplateFiles(
            'ItemEdit',
            'ItemEdit.inc'
        );
        $this->template->set_var(
            array(
                'itemID'                         => $this->dsItem->getValue(DBEItem::itemID),
                'description'                    => Controller::htmlInputText(
                    $this->dsItem->getValue(DBEItem::description)
                ),
                'descriptionMessage'             => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::description)
                ),
                'curUnitSale'                    => Controller::htmlInputText(
                    $this->dsItem->getValue(DBEItem::curUnitSale)
                ),
                'curUnitSaleMessage'             => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::curUnitSale)
                ),
                'curUnitCost'                    => Controller::htmlInputText(
                    $this->dsItem->getValue(DBEItem::curUnitCost)
                ),
                'curUnitCostMessage'             => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::curUnitCost)
                ),
                'discontinuedFlagChecked'        => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::discontinuedFlag)
                ),
                'servercareFlagChecked'          => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::servercareFlag)
                ),
                'serialNoFlagChecked'            => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::serialNoFlag)
                ),
                'partNo'                         => Controller::htmlInputText($this->dsItem->getValue(DBEItem::partNo)),
                'notes'                          => Controller::htmlTextArea($this->dsItem->getValue(DBEItem::notes)),
                'contractResponseTime'           => Controller::htmlInputText(
                    $this->dsItem->getValue(DBEItem::contractResponseTime)
                ),
                'urlManufacturerPopup'           => $urlManufacturerPopup,
                'urlManufacturerEdit'            => $urlManufacturerEdit,
                'manufacturerID'                 => $this->dsItem->getValue(DBEItem::manufacturerID),
                'manufacturerName'               => $manufacturerName,
                'urlSubmit'                      => $urlSubmit,
                'allowDirectDebitChecked'        => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::allowDirectDebit)
                ),
                'excludeFromPOCompletionChecked' => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::excludeFromPOCompletion)
                ),
                'allowSRLog'                     => $this->dsItem->getValue(DBEItem::allowSRLog) ? "checked" : null
            )
        );
        $this->parseItemTypeSelector($this->dsItem->getValue(DBEItem::itemTypeID));
        $this->parseRenewalTypeSelector($this->dsItem->getValue(DBEItem::renewalTypeID));
        $this->parseWarrantySelector($this->dsItem->getValue(DBEItem::warrantyID));
        $this->parseItemBillingCategorySelector($this->dsItem->getValue(DBEItem::itemBillingCategoryID));
        $this->template->parse(
            'CONTENTS',
            'ItemEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Prepare for add
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @param bool $renewalTypeID
     * @return mixed|string
     * @throws Exception
     */
    function itemFormPrepareAdd($renewalTypeID = false)
    {
        // If form error then preserve values in $this->dsItem else initialise new
        $this->setPageTitle(CTITEM_TXT_NEW_ITEM);
        if (!$this->getFormError()) {
            $this->buItem->initialiseNewItem(
                $this->dsItem,
                $renewalTypeID
            );
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTITEM_ACT_ITEM_INSERT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    /**
     * Prepare for edit
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function itemFormPrepareEdit()
    {
        $this->setPageTitle(CTITEM_TXT_UPDATE_ITEM);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($this->getParam('itemID'))) {
                $this->displayFatalError(CTITEM_MSG_ITEMID_NOT_PASSED);
            }
            if (!$this->buItem->getItemByID(
                $this->getParam('itemID'),
                $this->dsItem
            )) {
                $this->displayFatalError(CTITEM_MSG_ITEM_NOT_FND);
            }
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTITEM_ACT_ITEM_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    function parseItemTypeSelector($itemTypeID)
    {
        $dsItemType = new DataSet($this);
        $this->buItem->getAllItemTypes($dsItemType);
        $this->template->set_block(
            'ItemEdit',
            'itemTypeBlock',
            'itemTypes'
        );
        while ($dsItemType->fetchNext()) {
            if (!$dsItemType->getValue(DBEItemType::active)) {
                continue;
            }
            $this->template->set_var(
                array(
                    'itemTypeDescription' => $dsItemType->getValue(DBEItemType::description),
                    'itemTypeID'          => $dsItemType->getValue(DBEItemType::itemTypeID),
                    'itemTypeSelected'    => ($itemTypeID == $dsItemType->getValue(
                            DBEItemType::itemTypeID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'itemTypes',
                'itemTypeBlock',
                true
            );
        }
    }

    function parseRenewalTypeSelector($renewalTypeID)
    {
        $dbeRenewalType = new DBERenewalType($this);
        $dbeRenewalType->getRows();
        $this->template->set_block(
            'ItemEdit',
            'renewalTypeBlock',
            'renewals'
        );

        $allowedDirectDebitRenewals = [1, 2, 5];

        while ($dbeRenewalType->fetchNext()) {
            $this->template->set_var(
                array(
                    'renewalTypeDescription'   => $dbeRenewalType->getValue(DBERenewalType::description),
                    'renewalTypeID'            => $dbeRenewalType->getValue(DBERenewalType::renewalTypeID),
                    'renewalAllowsDirectDebit' => in_array(
                        $dbeRenewalType->getValue(DBERenewalType::renewalTypeID),
                        $allowedDirectDebitRenewals
                    ) ? 'data-allows-direct-debit="true"' : null,
                    'renewalTypeSelected'      => ($renewalTypeID == $dbeRenewalType->getValue(
                            DBERenewalType::renewalTypeID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'renewals',
                'renewalTypeBlock',
                true
            );
        } // while ($dbeRenewalType->fetchNext()
    }

    function parseWarrantySelector($warrantyID)
    {
        // Manufacturer selector
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        $this->template->set_block(
            'ItemEdit',
            'warrantyBlock',
            'warranties'
        );
        while ($dbeWarranty->fetchNext()) {
            $this->template->set_var(
                array(
                    'warrantyDescription' => $dbeWarranty->getValue(DBEWarranty::description),
                    'warrantyID'          => $dbeWarranty->getValue(DBEWarranty::warrantyID),
                    'warrantySelected'    => ($warrantyID == $dbeWarranty->getValue(
                            DBEWarranty::warrantyID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'warranties',
                'warrantyBlock',
                true
            );
        } // while ($dbeWarranty->fetchNext()
    }

    function parseItemBillingCategorySelector($itemBillingCategoryID)
    {
        $dbeItemBillingCategory = new DBEItemBillingCategory($this);
        $dbeItemBillingCategory->getRows(DBEItemBillingCategory::name);
        $this->template->set_block(
            'ItemEdit',
            'itemBillingCategoryBlock',
            'itemBillingCategories'
        );
        while ($dbeItemBillingCategory->fetchNext()) {
            $this->template->set_var(
                array(
                    'itemBillingCategoryName'     => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::name),
                    'itemBillingCategoryID'       => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::id),
                    'itemBillingCategorySelected' => ($itemBillingCategoryID == $dbeItemBillingCategory->getValue(
                            DBEItemBillingCategory::id
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'itemBillingCategories',
                'itemBillingCategoryBlock',
                true
            );
        }
    }

    /**
     * Update item record
     * @access private
     * @throws Exception
     */
    function itemUpdate()
    {
        $this->setMethodName('itemUpdate');
        if (!$this->getParam('item')) {
            $this->displayFatalError(CTITEM_MSG_ITEM_ARRAY_NOT_PASSED);
            return;
        }

        //$this->buItem->initialiseNewItem($this->dsItem);
        if (!$this->dsItem->populateFromArray($this->getParam('item'))) {
            $this->setFormErrorOn();
            if ($this->getAction() == CTITEM_ACT_ITEM_INSERT) {
                $this->setAction(CTCNC_ACT_ITEM_ADD);
            } else {
                $this->setAction(CTCNC_ACT_ITEM_EDIT);
            }
            $this->setParam('itemID', $this->dsItem->getValue(DBEItem::itemID));
            $this->itemForm();
            exit;
        }
        $this->buItem->updateItem($this->dsItem);
        $itemID = $this->dsItem->getPKValue();

        // this forces update of itemID back through Javascript to parent HTML window
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'          => CTCNC_ACT_DISP_ITEM_POPUP,
                'itemDescription' => $itemID,
                'htmlFmt'         => CT_HTML_FMT_POPUP
            )
        );
        header('Location: ' . $urlNext);
    }

    function discontinue()
    {
        $this->setMethodName('discontinue');
        if ($this->getParam('discontinueItemIDs')) {

            $this->buItem->discontinue(
                $this->getParam('discontinueItemIDs')
            );

        }
        header('Location: ' . $this->getParam('returnTo'));
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayItemSelectPopup()
    {
        common_decodeQueryArray($_REQUEST);

        if ($this->getParam('renewalTypeID')) {
            $renewalTypeID = $this->getParam('renewalTypeID');
        } else {
            $renewalTypeID = false;
        }

        $this->setMethodName('displayItemSelectPopup');
        // this may be required in a number of situations
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'        => CTCNC_ACT_ITEM_ADD,
                'renewalTypeID' => $renewalTypeID,
                'htmlFmt'       => CT_HTML_FMT_POPUP
            )
        );

        // A single slash means create new item
        if ($this->getParam('itemDescription'){0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $dsItem = new DataSet($this);
        $this->buItem->getItemsByNameMatch(
            $this->getParam('itemDescription'),
            $dsItem,
            $renewalTypeID
        );

        $this->template->set_var(
            array(
                'parentIDField'               => @$_SESSION['itemParentIDField'],
                'parentSlaResponseHoursField' => @$_SESSION['itemParentSlaResponseHoursField'],
                'parentDescField'             => @$_SESSION['itemParentDescField']
            )
        );
        if ($dsItem->rowCount() == 1) {
            $this->setTemplateFiles(
                'ItemSelect',
                'ItemSelectOne.inc'
            );
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $this->template->set_var(
                array(
                    'submitDescription'       => addslashes($dsItem->getValue(DBEItem::description)),
                    // for javascript
                    'itemID'                  => $dsItem->getValue(DBEItem::itemID),
                    'curUnitCost'             => number_format(
                        $dsItem->getValue(DBEItem::curUnitCost),
                        2,
                        '.',
                        ''
                    ),
                    'curUnitSale'             => number_format(
                        $dsItem->getValue(DBEItem::curUnitSale),
                        2,
                        '.',
                        ''
                    ),
                    'qtyOrdered'              => $dsItem->getValue(DBEItem::salesStockQty),
                    // to indicate number in stock
                    'slaResponseHours'        => $dsItem->getValue(DBEItem::contractResponseTime),
                    'partNo'                  => $dsItem->getValue(DBEItem::partNo),
                    'allowDirectDebit'        => $dsItem->getValue(DBEItem::allowDirectDebit) == 'Y' ? 'true' : 'false',
                    'excludeFromPOCompletion' => $dsItem->getValue(
                        DBEItem::excludeFromPOCompletion
                    ) == 'Y' ? 'true' : 'false'
                )
            );
        } else {
            if ($dsItem->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'itemDescription' => $this->getParam('itemDescription'),
                    )
                );
                $this->setTemplateFiles(
                    'ItemSelect',
                    'ItemSelectNone.inc'
                );
            }
            if ($dsItem->rowCount() > 1) {
                $this->setTemplateFiles(
                    'ItemSelect',
                    'ItemSelectPopup.inc'
                );
            }

            $returnTo = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

            $urlDiscontinue =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'   => 'discontinue',
                        'returnTo' => $returnTo
                    )
                );

            $this->template->set_var(
                array(
                    'urlItemCreate'  => $urlCreate,
                    'urlDiscontinue' => $urlDiscontinue
                )
            );

            // Parameters
            $this->setPageTitle('Item Selection');
            $dbeItemBillingCategory = new DBEItemBillingCategory($this);
            if ($dsItem->rowCount() > 0) {
                $this->template->set_block(
                    'ItemSelect',
                    'itemBlock',
                    'items'
                );
                while ($dsItem->fetchNext()) {
                    $itemBillingCategory = null;
                    if ($dsItem->getValue(DBEItem::itemBillingCategoryID)) {
                        $dbeItemBillingCategory->getRow($dsItem->getValue(DBEItem::itemBillingCategoryID));
                        $itemBillingCategory = $dbeItemBillingCategory->getValue(DBEItemBillingCategory::name);
                    }
                    $this->template->set_var(
                        array(
                            'itemDescription'         => Controller::htmlDisplayText(
                                $dsItem->getValue(DBEItem::description)
                            ),
                            // this complicated thing is to cope with Javascript quote problems!
                            'submitDescription'       => Controller::htmlInputText(
                                addslashes($dsItem->getValue(DBEItem::description))
                            ),
                            'itemID'                  => $dsItem->getValue(DBEItem::itemID),
                            'curUnitCost'             => number_format(
                                $dsItem->getValue(DBEItem::curUnitCost),
                                2,
                                '.',
                                ''
                            ),
                            'curUnitSale'             => number_format(
                                $dsItem->getValue(DBEItem::curUnitSale),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOrdered'              => $dsItem->getValue(DBEItem::salesStockQty),
                            // to indicate number in stock
                            'partNo'                  => $dsItem->getValue(DBEItem::partNo),
                            'slaResponseHours'        => $dsItem->getValue(DBEItem::contractResponseTime),
                            "itemBillingCategory"     => $itemBillingCategory,
                            'allowDirectDebit'        => $dsItem->getValue(
                                DBEItem::allowDirectDebit
                            ) == 'Y' ? 'true' : 'false',
                            'allowDirectDebitValue'   => $dsItem->getValue(
                                DBEitem::allowDirectDebit
                            ) == 'Y' ? 'Y' : null,
                            'excludeFromPOCompletion' => $dsItem->getValue(
                                DBEItem::excludeFromPOCompletion
                            ) == 'Y' ? 'true' : 'false'
                        )
                    );
                    $this->template->parse(
                        'items',
                        'itemBlock',
                        true
                    );
                }
            }
        } // not ($dsItem->rowCount()==1)
        $this->template->parse(
            'CONTENTS',
            'ItemSelect',
            true
        );
        $this->parsePage();
    }

    function parseManufacturerSelector($manufacturerID)
    {
        $dsManufacturer = new DataSet($this);
        $this->buItem->getAllManufacturers($dsManufacturer);
        $this->template->set_block(
            'ItemEdit',
            'manufacturerBlock',
            'manufacturers'
        );
        while ($dsManufacturer->fetchNext()) {
            $this->template->set_var(
                array(
                    'manufacturerName'     => $dsManufacturer->getValue(DBEManufacturer::name),
                    'manufacturerID'       => $dsManufacturer->getValue(DBEManufacturer::manufacturerID),
                    'manufacturerSelected' => ($manufacturerID == $dsManufacturer->getValue(
                            DBEManufacturer::manufacturerID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'manufacturers',
                'manufacturerBlock',
                true
            );
        }
    }
}
