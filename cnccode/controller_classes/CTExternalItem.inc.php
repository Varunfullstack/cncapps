<?php
/**
 * External Customer Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExternalItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'CREXTERNALITEM_ACT_DISPLAY_LIST',
    'external itemList'
);
define(
    'CREXTERNALITEM_ACT_ACT',
    'add'
);
define(
    'CREXTERNALITEM_ACT_EDIT',
    'edit'
);
define(
    'CREXTERNALITEM_ACT_DELETE',
    'delete'
);
define(
    'CREXTERNALITEM_ACT_UPDATE',
    'update'
);

class CTExternalItem extends CTCNC
{
    public $dsExternalItem;
    public $buExternalItem;

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
            "sales"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buExternalItem = new BUExternalItem($this);
        $this->dsExternalItem = new DSForm($this);
        $this->dsExternalItem->copyColumnsFrom($this->buExternalItem->dbeExternalItem);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CREXTERNALITEM_ACT_EDIT:
            case CREXTERNALITEM_ACT_ACT:
                $this->edit();
                break;
            case CREXTERNALITEM_ACT_DELETE:
                $this->delete();
                break;
            case 'popup':
                $this->popup();
                break;
            case CREXTERNALITEM_ACT_UPDATE:
                $this->update();
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
        $dsExternalItem = &$this->dsExternalItem; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CREXTERNALITEM_ACT_EDIT) {
                $this->buExternalItem->getExternalItemByID(
                    $this->getParam('externalItemID'),
                    $dsExternalItem
                );
                $externalItemID = $this->getParam('externalItemID');
            } else {                                                                    // creating new
                $dsExternalItem->initialise();
                $dsExternalItem->setValue(
                    DBEExternalItem::externalItemID,
                    null
                );
                $dsExternalItem->setValue(
                    DBEExternalItem::customerID,
                    $this->getParam('customerID')
                );
                $externalItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsExternalItem->initialise();
            $dsExternalItem->fetchNext();
            $externalItemID = $dsExternalItem->getValue(DBEExternalItem::externalItemID);
        }
        $urlDelete = null;
        $txtDelete = null;

        if ($this->getAction() == CREXTERNALITEM_ACT_EDIT && $this->buExternalItem->canDelete(
                $this->getParam('externalItemID')
            )) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CREXTERNALITEM_ACT_DELETE,
                        'externalItemID' => $externalItemID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CREXTERNALITEM_ACT_UPDATE,
                    'externalItemID' => $externalItemID
                )
            );
        $urlDisplayCustomer =
            Controller::buildLink(
                'RenewalReport.php',
                array(
                    'customerID' => $this->dsExternalItem->getValue(DBEExternalItem::customerID),
                    'action'     => 'produceReport'
                )
            );
        $this->setPageTitle('Edit External Item');
        $this->setTemplateFiles(
            array('ExternalItemEdit' => 'ExternalItemEdit.inc')
        );
        $this->template->set_var(
            array(
                'customerID'                => $dsExternalItem->getValue(DBEExternalItem::customerID),
                'externalItemID'            => $externalItemID,
                'description'               => Controller::htmlInputText(
                    $dsExternalItem->getValue(DBEExternalItem::description)
                ),
                'descriptionMessage'        => Controller::htmlDisplayText(
                    $dsExternalItem->getMessage(DBEExternalItem::description)
                ),
                'notes'                     => Controller::htmlInputText(
                    $dsExternalItem->getValue(DBEExternalItem::notes)
                ),
                'notesMessage'              => Controller::htmlDisplayText(
                    $dsExternalItem->getMessage(DBEExternalItem::notes)
                ),
                'licenceRenewalDate'        => $dsExternalItem->getValue(DBEExternalItem::licenceRenewalDate),
                'licenceRenewalDateMessage' => Controller::htmlDisplayText(
                    $dsExternalItem->getMessage(DBEExternalItem::licenceRenewalDate)
                ),
                'urlUpdate'                 => $urlUpdate,
                'urlDelete'                 => $urlDelete,
                'txtDelete'                 => $txtDelete,
                'urlDisplayCustomer'        => $urlDisplayCustomer
            )
        );
        $this->itemTypeDropdown(
            'ExternalItemEdit',
            $dsExternalItem->getValue(DBEExternalItem::itemTypeID)
        );

        $this->template->parse(
            'CONTENTS',
            'ExternalItemEdit',
            true
        );
        $this->parsePage();
    }// end function editFurther Action()

    function itemTypeDropdown($templateName,
                              $itemTypeID
    )
    {
        $dbeItemType = new DBEItemType($this);

        $dbeItemType->getRows();
        $this->template->set_block(
            $templateName,
            'itemTypeBlock',
            'itemTypes'
        );

        while ($dbeItemType->fetchNext()) {
            $selected = ($itemTypeID == $dbeItemType->getValue(DBEItemType::itemTypeID)) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'itemTypeSelected'    => $selected,
                    'itemTypeID'          => $dbeItemType->getValue(DBEItemType::itemTypeID),
                    'itemTypeDescription' => $dbeItemType->getValue(DBEItemType::description)
                )
            );
            $this->template->parse(
                'itemTypes',
                'itemTypeBlock',
                true
            );
        }
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        $dsExternalItem = new DataSet($this);
        $this->buExternalItem->getExternalItemByID(
            $this->getParam('externalItemID'),
            $dsExternalItem
        );

        if (!$this->buExternalItem->deleteExternalItem($this->getParam('externalItemID'))) {
            $this->displayFatalError('Cannot delete this external item');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    'RenewalReport.php',
                    array(
                        'customerID' => $dsExternalItem->getValue(DBEExternalItem::customerID),
                        'action'     => 'produceReport'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * @throws Exception
     */
    function popup()
    {
        $dsExternalItem = new DataSet($this);
        $this->buExternalItem->getExternalItemByID(
            $this->getParam('externalItemID'),
            $dsExternalItem
        );
        $this->setPageTitle(
            'ExternalItem: ' . Controller::htmlDisplayText($dsExternalItem->getValue(DBEExternalItem::description))
        );
        $this->setTemplateFiles(
            array('ExternalItemPopup' => 'ExternalItemPopup.inc')
        );
        $this->template->set_var(
            array(
                'notes'              => Controller::htmlDisplayText(
                    $dsExternalItem->getValue(DBEExternalItem::notes),
                    1
                ),
                'licenceRenewalDate' => Controller::dateYMDtoDMY(
                    $dsExternalItem->getValue(DBEExternalItem::licenceRenewalDate)
                ),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ExternalItemPopup',
            true
        );
        $this->parsePage();

    }

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsExternalItem->populateFromArray($this->getParam('externalItem')));
        if ($this->formError) {
            $this->setAction(CREXTERNALITEM_ACT_ACT);
            if (!$this->dsExternalItem->getValue(
                DBEExternalItem::externalItemID
            )) {                    // attempt to insert
                $this->setAction(CREXTERNALITEM_ACT_EDIT);
            }
            $this->edit();
            exit;
        }

        $this->buExternalItem->updateExternalItem($this->dsExternalItem);

        $urlNext =
            Controller::buildLink(
                'RenewalReport.php',
                array(
                    'customerID' => $this->dsExternalItem->getValue(DBEExternalItem::customerID),
                    'action'     => 'produceReport'
                )
            );
        header('Location: ' . $urlNext);
    }
}
