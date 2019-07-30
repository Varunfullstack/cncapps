<?php
/**
 * Quotation template controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUQuotationTemplate.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'ctQuotationTemplates_ACT_DISPLAY_LIST',
    'quotationTemplateList'
);
define(
    'ctQuotationTemplates_ACT_CREATE',
    'createQuotationTemplate'
);
define(
    'ctQuotationTemplates_ACT_EDIT',
    'editQuotationTemplate'
);
define(
    'ctQuotationTemplates_ACT_DELETE',
    'deleteQuotationTemplate'
);
define(
    'ctQuotationTemplates_ACT_UPDATE',
    'updateQuotationTemplate'
);

define(
    'ctQuotationTemplates_ACT_CHANGE_ORDER',
    'changeOrder'
);

class CTQuotationTemplates extends CTCNC
{
    public $dsQuotationTemplate;
    /** @var BUQuotationTemplate */
    public $buQuotationTemplate;

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
        $this->buQuotationTemplate = new BUQuotationTemplate($this);
        $this->dsQuotationTemplate = new DSForm($this);
        $this->dsQuotationTemplate->copyColumnsFrom($this->buQuotationTemplate->dbeQuotationTemplate);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($this->getAction()) {
            case ctQuotationTemplates_ACT_EDIT:
            case ctQuotationTemplates_ACT_CREATE:
                $this->edit();
                break;
            case ctQuotationTemplates_ACT_DELETE:
                $this->delete();
                break;
            case ctQuotationTemplates_ACT_UPDATE:
                $this->update();
                break;
            case CTCNC_ACT_DISP_TEMPLATE_QUOTATION_POPUP:
                $this->displayPopup();
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case ctQuotationTemplates_ACT_CHANGE_ORDER:
                $this->changeOrder();
            case ctQuotationTemplates_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsQuotationTemplate = &$this->dsQuotationTemplate; // ref to class var

        if (!$this->getFormError()) {

            if ($this->getAction() == ctQuotationTemplates_ACT_EDIT) {
                $this->buQuotationTemplate->getQuotationTemplateByID(
                    $this->getParam('id'),
                    $dsQuotationTemplate
                );
                $quotationTemplateID = $this->getParam('id');
            } else {                                                                    // creating new
                $dsQuotationTemplate->initialise();
                $dsQuotationTemplate->setValue(
                    DBEQuotationTemplate::id,
                    null
                );
                $dbeQuotationTemplate = new DBEQuotationTemplate($this);
                $dsQuotationTemplate->setValue(
                    DBEQuotationTemplate::sortOrder,
                    $dbeQuotationTemplate->getNextSortOrder()
                );
                $quotationTemplateID = 0;
            }
        } else {                                                                        // form validation error
            $dsQuotationTemplate->initialise();
            $dsQuotationTemplate->fetchNext();
            $quotationTemplateID = $dsQuotationTemplate->getValue(DBEQuotationTemplate::id);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() != ctQuotationTemplates_ACT_EDIT) {
        } else {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => ctQuotationTemplates_ACT_DELETE,
                        'id'     => $quotationTemplateID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => ctQuotationTemplates_ACT_UPDATE,
                    'id'     => $quotationTemplateID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => ctQuotationTemplates_ACT_DISPLAY_LIST
                )
            );
        $title = 'Edit Quotation Template';
        if (!$quotationTemplateID) {
            $title = "Create Quotation Template";
        }
        $this->setPageTitle($title);
        $this->setTemplateFiles(
            array('QuotationTemplateEdit' => 'QuotationTemplateEdit.inc')
        );
        $this->template->set_var(
            array(
                'id'                        => $quotationTemplateID,
                'sortOrder'                 => Controller::htmlInputText(
                    $dsQuotationTemplate->getValue(DBEQuotationTemplate::sortOrder)
                ),
                'sortOrderMessage'          => Controller::htmlDisplayText(
                    $dsQuotationTemplate->getMessage(DBEQuotationTemplate::sortOrder)
                ),
                'description'               => Controller::htmlInputText(
                    $dsQuotationTemplate->getValue(DBEQuotationTemplate::description)
                ),
                'descriptionMessage'        => Controller::htmlDisplayText(
                    $dsQuotationTemplate->getMessage(DBEQuotationTemplate::description)
                ),
                'linkedSalesOrderId'        => Controller::htmlDisplayText(
                    $dsQuotationTemplate->getValue(DBEQuotationTemplate::linkedSalesOrderId)
                ),
                'linkedSalesOrderIdMessage' => Controller::htmlDisplayText(
                    $dsQuotationTemplate->getMessage(DBEQuotationTemplate::linkedSalesOrderId)
                ),
                'updateOrCreate'            => !$quotationTemplateID ? 'Create' : 'Update',
                'urlUpdate'                 => $urlUpdate,
                'urlDelete'                 => $urlDelete,
                'txtDelete'                 => $txtDelete,
                'urlDisplayList'            => $urlDisplayList
            )
        );

        $this->template->parse(
            'CONTENTS',
            'QuotationTemplateEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        try {
            $this->buQuotationTemplate->deleteQuotationTemplate($this->getParam('id'));
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => ctQuotationTemplates_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        } catch (Exception $exception) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        }
    }

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsQuotationTemplate->populateFromArray($this->getParam('quotationTemplate')));

        if (!$this->dsQuotationTemplate->getValue(DBEQuotationTemplate::id)) {
            $this->setAction(ctQuotationTemplates_ACT_EDIT);
        } else {
            $this->setAction(ctQuotationTemplates_ACT_CREATE);
        }
        if ($this->formError) {
            $this->edit();
            exit;
        }


        if (!$this->buQuotationTemplate->updateQuotationTemplate($this->dsQuotationTemplate)) {
            $this->edit();
            exit;
        };

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'id'     => $this->dsQuotationTemplate->getValue(
                        DBEQuotationTemplate::id
                    ),
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayPopup()
    {

        $dsResults = new DataSet($this);
        $this->buQuotationTemplate->getByNameMatch(
            $this->getParam('description'),
            $dsResults,
        );

//        $this->template->set_var(
//            array(
//                'parentIDField'               => @$_SESSION['itemParentIDField'],
//                'parentSlaResponseHoursField' => @$_SESSION['itemParentSlaResponseHoursField'],
//                'parentDescField'             => @$_SESSION['itemParentDescField']
//            )
//        );
        if ($dsResults->rowCount() == 1) {
            $this->setTemplateFiles(
                'ItemSelect',
                'ItemSelectOne.inc'
            );
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $this->template->set_var(
                array(
                    'submitDescription'       => addslashes($dsResults->getValue(DBEItem::description)),
                    // for javascript
                    'itemID'                  => $dsResults->getValue(DBEItem::itemID),
                    'curUnitCost'             => number_format(
                        $dsResults->getValue(DBEItem::curUnitCost),
                        2,
                        '.',
                        ''
                    ),
                    'curUnitSale'             => number_format(
                        $dsResults->getValue(DBEItem::curUnitSale),
                        2,
                        '.',
                        ''
                    ),
                    'qtyOrdered'              => $dsResults->getValue(DBEItem::salesStockQty),
                    // to indicate number in stock
                    'slaResponseHours'        => $dsResults->getValue(DBEItem::contractResponseTime),
                    'partNo'                  => $dsResults->getValue(DBEItem::partNo),
                    'allowDirectDebit'        => $dsResults->getValue(DBEItem::allowDirectDebit) == 'Y' ? 'true' : 'false',
                    'excludeFromPOCompletion' => $dsResults->getValue(
                        DBEItem::excludeFromPOCompletion
                    ) == 'Y' ? 'true' : 'false'
                )
            );
        } else {
            if ($dsResults->rowCount() == 0) {
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
            if ($dsResults->rowCount() > 1) {
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
            if ($dsResults->rowCount() > 0) {
                $this->template->set_block(
                    'ItemSelect',
                    'itemBlock',
                    'items'
                );
                while ($dsResults->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'itemDescription'         => Controller::htmlDisplayText(
                                $dsResults->getValue(DBEItem::description)
                            ),
                            // this complicated thing is to cope with Javascript quote problems!
                            'submitDescription'       => Controller::htmlInputText(
                                addslashes($dsResults->getValue(DBEItem::description))
                            ),
                            'itemID'                  => $dsResults->getValue(DBEItem::itemID),
                            'curUnitCost'             => number_format(
                                $dsResults->getValue(DBEItem::curUnitCost),
                                2,
                                '.',
                                ''
                            ),
                            'curUnitSale'             => number_format(
                                $dsResults->getValue(DBEItem::curUnitSale),
                                2,
                                '.',
                                ''
                            ),
                            'qtyOrdered'              => $dsResults->getValue(DBEItem::salesStockQty),
                            // to indicate number in stock
                            'partNo'                  => $dsResults->getValue(DBEItem::partNo),
                            'slaResponseHours'        => $dsResults->getValue(DBEItem::contractResponseTime),
                            'allowDirectDebit'        => $dsResults->getValue(
                                DBEItem::allowDirectDebit
                            ) == 'Y' ? 'true' : 'false',
                            'excludeFromPOCompletion' => $dsResults->getValue(
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
    }// end function editFurther Action()

    function changeOrder()
    {
        if (!$this->getParam('sortOrder')) {
            return;
        }

        foreach ($this->getParam('sortOrder') as $quotationTemplateID => $value) {

            $dbeQuotationTemplate = new DBEQuotationTemplate($this);

            switch ($value) {
                case 'top':
                    $dbeQuotationTemplate->moveItemToTop($quotationTemplateID);
                    break;
                case 'bottom':
                    $dbeQuotationTemplate->moveItemToBottom($quotationTemplateID);
                    break;
                case 'down':
                    $dbeQuotationTemplate->moveItemDown($quotationTemplateID);
                    break;
                case 'up':
                    $dbeQuotationTemplate->moveItemUp($quotationTemplateID);
                    break;
            }

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
        $this->setPageTitle('Quotation Templates');
        $this->setTemplateFiles(
            array('QuotationTemplateList' => 'QuotationTemplateList.inc')
        );


        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => ctQuotationTemplates_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );


        $dbeQuotationTemplate = new DBEQuotationTemplate($this);
        $dbeQuotationTemplate->getRows(DBEQuotationTemplate::sortOrder);

        $this->template->set_block(
            'QuotationTemplateList',
            'quotationTemplateBlock',
            'quotationTemplates'
        );
        $count = 0;
        $totalCount = $dbeQuotationTemplate->rowCount();

        while ($dbeQuotationTemplate->fetchNext()) {

            $quotationTemplateID = $dbeQuotationTemplate->getValue(DBEQuotationTemplate::id);

            $urlEdit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => ctQuotationTemplates_ACT_EDIT,
                        'id'     => $quotationTemplateID
                    )
                );
            $txtEdit = '[edit]';

            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => ctQuotationTemplates_ACT_DELETE,
                        'id'     => $quotationTemplateID
                    )
                );
            $txtDelete = '[delete]';

            $up = true;
            $down = true;
            $top = true;
            $bottom = true;

            if (!$count) {
                $top = false;
                $up = false;
            }

            $count++;

            if ($count == $totalCount) {
                $down = false;
                $bottom = false;
            }


            $this->template->set_var(
                array(
                    'id'                 => $quotationTemplateID,
                    'description'        => Controller::htmlDisplayText(
                        $dbeQuotationTemplate->getValue(DBEQuotationTemplate::description)
                    ),
                    'linkedSalesOrderId' => Controller::htmlDisplayText(
                        $dbeQuotationTemplate->getValue(DBEQuotationTemplate::linkedSalesOrderId)
                    ),
                    'urlEdit'            => $urlEdit,
                    'urlDelete'          => $urlDelete,
                    'txtEdit'            => $txtEdit,
                    'txtDelete'          => $txtDelete,
                    'sortOrderUp'        => $up ? null : 'disabled',
                    'sortOrderDown'      => $down ? null : 'disabled',
                    'sortOrderTop'       => $top ? null : 'disabled',
                    'sortOrderBottom'    => $bottom ? null : 'disabled',
                )
            );
            $this->template->parse(
                'quotationTemplates',
                'quotationTemplateBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'QuotationTemplateList',
            true
        );
        $this->parsePage();
    }
}
