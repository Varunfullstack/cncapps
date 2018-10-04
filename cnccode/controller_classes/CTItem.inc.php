<?php
/**
 * Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUItem.inc.php');
//require_once($cfg['path_bu'].'/BUNotepad.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEWarranty.inc.php');
require_once($cfg['path_dbe'] . '/DBERenewalType.inc.php');
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
    /**
     * Dataset for item record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsItem = '';

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
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_ITEM_ADD:
            case CTCNC_ACT_ITEM_EDIT:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->itemForm();
                break;
            case CTITEM_ACT_ITEM_INSERT:
            case CTITEM_ACT_ITEM_UPDATE:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->itemUpdate();
                break;
            case 'discontinue':
                $this->checkPermissions(PHPLIB_PERM_SALES);
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
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['itemParentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['itemParentDescField'] = $_REQUEST['parentDescField'];
        }
        if (isset($_REQUEST['parentSlaResponseHoursField'])) {
            $_SESSION['itemParentSlaResponseHoursField'] = $_REQUEST['parentSlaResponseHoursField'];
        }
    }

    /**
     * Display the popup selector form
     * @access private
     */
    function displayItemSelectPopup()
    {
        common_decodeQueryArray($_REQUEST);

        if ($_REQUEST['renewalTypeID']) {
            $renewalTypeID = $_REQUEST['renewalTypeID'];
        } else {
            $renewalTypeID = false;
        }

        $this->setMethodName('displayItemSelectPopup');
        // this may be required in a number of situations
        $urlCreate = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'        => CTCNC_ACT_ITEM_ADD,
                'renewalTypeID' => $renewalTypeID,
                'htmlFmt'       => CT_HTML_FMT_POPUP
            )
        );

        // A single slash means create new item
        if ($_REQUEST['itemDescription']{0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $this->buItem->getItemsByNameMatch(
            $_REQUEST['itemDescription'],
            $dsItem,
            $renewalTypeID
        );

        $this->template->set_var(
            array(
                'parentIDField'               => $_SESSION['itemParentIDField'],
                'parentSlaResponseHoursField' => $_SESSION['itemParentSlaResponseHoursField'],
                'parentDescField'             => $_SESSION['itemParentDescField']
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
                    'submitDescription' => addslashes($dsItem->getValue("description")),
                    // for javascript
                    'itemID'            => $dsItem->getValue("itemID"),
                    'curUnitCost'       => number_format(
                        $dsItem->getValue("curUnitCost"),
                        2,
                        '.',
                        ''
                    ),
                    'curUnitSale'       => number_format(
                        $dsItem->getValue("curUnitSale"),
                        2,
                        '.',
                        ''
                    ),
                    'qtyOrdered'        => $dsItem->getValue("salesStockQty"),
                    // to indicate number in stock
                    'slaResponseHours'  => $dsItem->getValue("contractResponseTime"),
                    'partNo'            => $dsItem->getValue("partNo"),
                    'allowDirectDebit' => $dsItem->getValue(DBEItem::allowDirectDebit) =='Y' ? 'true': 'false'
                )
            );
        } else {
            if ($dsItem->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'itemDescription' => $_REQUEST['itemDescription'],
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
                $this->buildLink(
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
            if ($dsItem->rowCount() > 0) {
                $this->template->set_block(
                    'ItemSelect',
                    'itemBlock',
                    'items'
                );
                while ($dsItem->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'itemDescription'   => Controller::htmlDisplayText($dsItem->getValue("description")),
                            // this complicated thing is to cope with Javascript quote problems!
                            'submitDescription' => Controller::htmlInputText(
                                addslashes($dsItem->getValue("description"))
                            ),
                            'itemID'            => $dsItem->getValue("itemID"),
                            'curUnitCost'       => number_format(
                                $dsItem->getValue("curUnitCost"),
                                2,
                                '.',
                                ''
                            ),
                            'curUnitSale'       => number_format(
                                $dsItem->getValue("curUnitSale"),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOrdered'        => $dsItem->getValue("salesStockQty"),
                            // to indicate number in stock
                            'partNo'            => $dsItem->getValue("partNo"),
                            'slaResponseHours'  => $dsItem->getValue("contractResponseTime"),
                            'allowDirectDebit' => $dsItem->getValue(DBEItem::allowDirectDebit) =='Y' ? 'true': 'false'
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

    /**
     * Add/Edit Item
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function itemForm()
    {
        $this->setMethodName('itemForm');
        // initialisation stuff
        if ($_REQUEST['action'] == CTCNC_ACT_ITEM_ADD) {
            if ($_REQUEST['renewalTypeID']) {
                $renewalTypeID = $_REQUEST['renewalTypeID'];
            } else {
                $renewalTypeID = false;
            }
            $urlSubmit = $this->itemFormPrepareAdd($renewalTypeID);
        } else {
            $urlSubmit = $this->itemFormPrepareEdit();
        }

        $urlManufacturerPopup =
            $this->buildLink(
                'Manufacturer.php',
                array(
                    'action'  => 'displayPopup',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlManufacturerEdit =
            $this->buildLink(
                'Manufacturer.php',
                array(
                    'action'  => 'editManufacturer',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        if ($this->dsItem->getValue('manufacturerID')) {
            $dbeManufacturer = new DBEManufacturer($this);
            $dbeManufacturer->getRow($this->dsItem->getValue('manufacturerID'));
            $manufacturerName = $dbeManufacturer->getValue('name');
        } else {
            $manufacturerName = '';
        }

        // template
        $this->setTemplateFiles(
            'ItemEdit',
            'ItemEdit.inc'
        );
        $this->template->set_var(
            array(
                'itemID'                  => $this->dsItem->getValue('itemID'),
                'description'             => Controller::htmlInputText($this->dsItem->getValue(DBEItem::description)),
                'descriptionMessage'      => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::description)
                ),
                'curUnitSale'             => Controller::htmlInputText($this->dsItem->getValue(DBEItem::curUnitSale)),
                'curUnitSaleMessage'      => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::curUnitSale)
                ),
                'curUnitCost'             => Controller::htmlInputText($this->dsItem->getValue(DBEItem::curUnitCost)),
                'curUnitCostMessage'      => Controller::htmlDisplayText(
                    $this->dsItem->getMessage(DBEItem::curUnitCost)
                ),
                'discontinuedFlagChecked' => Controller::htmlChecked(
                    $this->dsItem->getValue(DBEItem::discontinuedFlag)
                ),
                'servercareFlagChecked'   => Controller::htmlChecked($this->dsItem->getValue(DBEItem::servercareFlag)),
                'serialNoFlagChecked'     => Controller::htmlChecked($this->dsItem->getValue(DBEItem::serialNoFlag)),
                'partNo'                  => Controller::htmlInputText($this->dsItem->getValue(DBEItem::partNo)),
                'notes'                   => Controller::htmlTextArea($this->dsItem->getValue(DBEItem::notes)),
                'contractResponseTime'    => Controller::htmlInputText(
                    $this->dsItem->getValue(DBEItem::contractResponseTime)
                ),
                'urlManufacturerPopup'    => $urlManufacturerPopup,
                'urlManufacturerEdit'     => $urlManufacturerEdit,
                'manufacturerID'          => $this->dsItem->getValue(DBEItem::manufacturerID),
                'manufacturerName'        => $manufacturerName,
                'urlSubmit'               => $urlSubmit,
                'urlCancel'               => $urlCancel,
                'allowDirectDebitChecked' => Controller::htmlChecked($this->dsItem->getValue(DBEItem::allowDirectDebit))
            )
        );
        $this->parseItemTypeSelector($this->dsItem->getValue('itemTypeID'));
        $this->parseRenewalTypeSelector($this->dsItem->getValue('renewalTypeID'));
        $this->parseWarrantySelector($this->dsItem->getValue('warrantyID'));
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
        $this->buildLink(
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
     */
    function itemFormPrepareEdit()
    {
        $this->setPageTitle(CTITEM_TXT_UPDATE_ITEM);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($_REQUEST['itemID'])) {
                $this->displayFatalError(CTITEM_MSG_ITEMID_NOT_PASSED);
            }
            if (!$this->buItem->getItemByID(
                $_REQUEST['itemID'],
                $this->dsItem
            )) {
                $this->displayFatalError(CTITEM_MSG_ITEM_NOT_FND);
            }
        }
        return (
        $this->buildLink(
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
        // Item type selector
        $this->buItem->getAllItemTypes($dsItemType);
        $this->template->set_block(
            'ItemEdit',
            'itemTypeBlock',
            'itemTypes'
        );
        while ($dsItemType->fetchNext()) {
            $this->template->set_var(
                array(
                    'itemTypeDescription' => $dsItemType->getValue('description'),
                    'itemTypeID'          => $dsItemType->getValue('itemTypeID'),
                    'itemTypeSelected'    => ($itemTypeID == $dsItemType->getValue('itemTypeID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'itemTypes',
                'itemTypeBlock',
                true
            );
        }
    }

    function parseManufacturerSelector($manufacturerID)
    {
        // Manufacturer selector
        $this->buItem->getAllManufacturers($dsManufacturer);
        $this->template->set_block(
            'ItemEdit',
            'manufacturerBlock',
            'manufacturers'
        );
        while ($dsManufacturer->fetchNext()) {
            $this->template->set_var(
                array(
                    'manufacturerName'     => $dsManufacturer->getValue('name'),
                    'manufacturerID'       => $dsManufacturer->getValue('manufacturerID'),
                    'manufacturerSelected' => ($manufacturerID == $dsManufacturer->getValue(
                            'manufacturerID'
                        )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'manufacturers',
                'manufacturerBlock',
                true
            );
        }
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
                    'warrantyDescription' => $dbeWarranty->getValue('description'),
                    'warrantyID'          => $dbeWarranty->getValue('warrantyID'),
                    'warrantySelected'    => ($warrantyID == $dbeWarranty->getValue('warrantyID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'warranties',
                'warrantyBlock',
                true
            );
        } // while ($dbeWarranty->fetchNext()
    }

    function parseRenewalTypeSelector($renewalTypeID)
    {
        // Manufacturer selector
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
                    'renewalTypeDescription'   => $dbeRenewalType->getValue('description'),
                    'renewalTypeID'            => $dbeRenewalType->getValue('renewalTypeID'),
                    'renewalAllowsDirectDebit' => in_array(
                        $dbeRenewalType->getValue('renewalTypeID'),
                        $allowedDirectDebitRenewals
                    ) ? 'data-allows-direct-debit="true"' : null,
                    'renewalTypeSelected'      => ($renewalTypeID == $dbeRenewalType->getValue(
                            'renewalTypeID'
                        )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'renewals',
                'renewalTypeBlock',
                true
            );
        } // while ($dbeRenewalType->fetchNext()
    }

    /**
     * Update item record
     * @access private
     */
    function itemUpdate()
    {
        $this->setMethodName('itemUpdate');
        if (!isset($_REQUEST['item'])) {
            $this->displayFatalError(CTITEM_MSG_ITEM_ARRAY_NOT_PASSED);
            return;
        }

        //$this->buItem->initialiseNewItem($this->dsItem);
        if (!$this->dsItem->populateFromArray($_REQUEST['item'])) {
            $this->setFormErrorOn();
            if ($_REQUEST['action'] == CTITEM_ACT_ITEM_INSERT) {
                $_REQUEST['action'] = CTCNC_ACT_ITEM_ADD;
            } else {
                $_REQUEST['action'] = CTCNC_ACT_ITEM_EDIT;
            }
            $_REQUEST['itemID'] = $this->dsItem->getValue('itemID');
            $this->itemForm();
            exit;
        }
        $this->buItem->updateItem($this->dsItem);
        $itemID = $this->dsItem->getPKValue();

        // this forces update of itemID back through Javascript to parent HTML window
        $urlNext = $this->buildLink(
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
        if (isset($_REQUEST['discontinueItemIDs'])) {

            $this->buItem->discontinue(
                $_REQUEST['discontinueItemIDs']
            );

        }
        header('Location: ' . $_REQUEST['returnTo']);
    }
}// end of class
?>