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
define('CREXTERNALITEM_ACT_DISPLAY_LIST', 'external itemList');
define('CREXTERNALITEM_ACT_ACT', 'add');
define('CREXTERNALITEM_ACT_EDIT', 'edit');
define('CREXTERNALITEM_ACT_DELETE', 'delete');
define('CREXTERNALITEM_ACT_UPDATE', 'update');

class CTExternalItem extends CTCNC
{
    var $dsExternalItem = '';
    var $buExternalItem = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
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
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
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
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsExternalItem = &$this->dsExternalItem; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CREXTERNALITEM_ACT_EDIT) {
                $this->buExternalItem->getExternalItemByID($_REQUEST['externalItemID'], $dsExternalItem);
                $externalItemID = $_REQUEST['externalItemID'];
            } else {                                                                    // creating new
                $dsExternalItem->initialise();
                $dsExternalItem->setValue('externalItemID', '0');
                $dsExternalItem->setValue('customerID', $_REQUEST['customerID']);
                $externalItemID = '0';
            }
        } else {                                                                        // form validation error
            $dsExternalItem->initialise();
            $dsExternalItem->fetchNext();
            $externalItemID = $dsExternalItem->getValue('externalItemID');
        }
        if ($_REQUEST['action'] == CREXTERNALITEM_ACT_EDIT && $this->buExternalItem->canDelete($_REQUEST['externalItemID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CREXTERNALITEM_ACT_DELETE,
                        'externalItemID' => $externalItemID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CREXTERNALITEM_ACT_UPDATE,
                    'externalItemID' => $externalItemID
                )
            );
        $urlDisplayCustomer =
            $this->buildLink(
                'RenewalReport.php',
                array(
                    'customerID' => $this->dsExternalItem->getValue('customerID'),
                    'action' => 'produceReport'
                )
            );
        $this->setPageTitle('Edit External Item');
        $this->setTemplateFiles(
            array('ExternalItemEdit' => 'ExternalItemEdit.inc')
        );
        $this->template->set_var(
            array(
                'customerID' => $dsExternalItem->getValue('customerID'),
                'externalItemID' => $externalItemID,
                'description' => Controller::htmlInputText($dsExternalItem->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsExternalItem->getMessage('description')),
                'notes' => Controller::htmlInputText($dsExternalItem->getValue('notes')),
                'notesMessage' => Controller::htmlDisplayText($dsExternalItem->getMessage('notes')),
                'licenceRenewalDate' => Controller::dateYMDtoDMY($dsExternalItem->getValue('licenceRenewalDate')),
                'licenceRenewalDateMessage' => Controller::htmlDisplayText($dsExternalItem->getMessage('licenceRenewalDate')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayCustomer' => $urlDisplayCustomer
            )
        );
        $this->itemTypeDropdown('ExternalItemEdit', $dsExternalItem->getValue('itemTypeID'));

        $this->template->parse('CONTENTS', 'ExternalItemEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsExternalItem = &$this->dsExternalItem;
        $this->formError = (!$this->dsExternalItem->populateFromArray($_REQUEST['externalItem']));
        if ($this->formError) {
            if ($this->dsExternalItem->getValue('externalItemID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CREXTERNALITEM_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CREXTERNALITEM_ACT_ACT;
            }
            $this->edit();
            exit;
        }

        $this->buExternalItem->updateExternalItem($this->dsExternalItem);

        $urlNext =
            $this->buildLink(
                'RenewalReport.php',
                array(
                    'customerID' => $this->dsExternalItem->getValue('customerID'),
                    'action' => 'produceReport'
                )
            );
        header('Location: ' . $urlNext);
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

        $this->buExternalItem->getExternalItemByID($_REQUEST['externalItemID'], $dsExternalItem);

        if (!$this->buExternalItem->deleteExternalItem($_REQUEST['externalItemID'])) {
            $this->displayFatalError('Cannot delete this external item');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    'RenewalReport.php',
                    array(
                        'customerID' => $dsExternalItem->getValue('customerID'),
                        'action' => 'produceReport'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    function itemTypeDropdown($templateName, $itemTypeID)
    {
        $dbeItemType = new DBEItemType($this);

        $dbeItemType->getRows();
        $this->template->set_block($templateName, 'itemTypeBlock', 'itemTypes');

        while ($dbeItemType->fetchNext()) {
            $selected = ($itemTypeID == $dbeItemType->getValue("itemTypeID")) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'itemTypeSelected' => $selected,
                    'itemTypeID' => $dbeItemType->getValue("itemTypeID"),
                    'itemTypeDescription' => $dbeItemType->getValue("description")
                )
            );
            $this->template->parse('itemTypes', 'itemTypeBlock', true);
        }
    }

    function popup()
    {
        $this->buExternalItem->getExternalItemByID($_REQUEST['externalItemID'], $dsExternalItem);
        $this->setPageTitle('ExternalItem: ' . Controller::htmlDisplayText($dsExternalItem->getValue('description')));
        $this->setTemplateFiles(
            array('ExternalItemPopup' => 'ExternalItemPopup.inc')
        );
        $this->template->set_var(
            array(
                'notes' => Controller::htmlDisplayText($dsExternalItem->getValue('notes'), 1),
                'licenceRenewalDate' => Controller::dateYMDtoDMY($dsExternalItem->getValue('licenceRenewalDate')),
            )
        );
        $this->template->parse('CONTENTS', 'ExternalItemPopup', true);
        $this->parsePage();

    }
}// end of class
?>