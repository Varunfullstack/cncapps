<?php
/**
 * Expense Type controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTEXPENSETYPE_ACT_DISPLAY_LIST', 'expenseTypeList');
define('CTEXPENSETYPE_ACT_CREATE', 'createExpenseType');
define('CTEXPENSETYPE_ACT_EDIT', 'editExpenseType');
define('CTEXPENSETYPE_ACT_DELETE', 'deleteExpenseType');
define('CTEXPENSETYPE_ACT_UPDATE', 'updateExpenseType');

class CTExpenseType extends CTCNC
{
    var $dsExpenseType = '';
    var $buExpenseType = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buExpenseType = new BUExpenseType($this);
        $this->dsExpenseType = new DSForm($this);
        $this->dsExpenseType->copyColumnsFrom($this->buExpenseType->dbeExpenseType);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTEXPENSETYPE_ACT_EDIT:
            case CTEXPENSETYPE_ACT_CREATE:
                $this->edit();
                break;
            case CTEXPENSETYPE_ACT_DELETE:
                $this->delete();
                break;
            case CTEXPENSETYPE_ACT_UPDATE:
                $this->update();
                break;
            case CTEXPENSETYPE_ACT_DISPLAY_LIST:
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
        $this->setPageTitle('Expense Types');
        $this->setTemplateFiles(
            array('ExpenseTypeList' => 'ExpenseTypeList.inc')
        );

        $this->buExpenseType->getAllTypes($dsExpenseType);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTEXPENSETYPE_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsExpenseType->rowCount() > 0) {
            $this->template->set_block('ExpenseTypeList', 'typeBlock', 'types');
            while ($dsExpenseType->fetchNext()) {
                $expenseTypeID = $dsExpenseType->getValue('expenseTypeID');
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTEXPENSETYPE_ACT_EDIT,
                            'expenseTypeID' => $expenseTypeID
                        )
                    );
                $txtEdit = '[edit]';
                /*
                                $urlDelete =
                                    $this->buildLink(
                                        $_SERVER['PHP_SELF'],
                                        array(
                                            'action'				=>	CTEXPENSETYPE_ACT_DELETE,
                                            'expenseTypeID'	=>	$expenseTypeID
                                        )
                                    );
                                $txtDelete = '[delete]';
                    */
                $this->template->set_var(
                    array(
                        'expenseTypeID' => $expenseTypeID,
                        'description' => Controller::htmlDisplayText($dsExpenseType->getValue('description')),
                        'urlEdit' => $urlEdit,
//						'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit
//						'txtDelete' => $txtDelete
                    )
                );
                $this->template->parse('types', 'typeBlock', true);
            }//while $dsExpenseType->fetchNext()
        }
        $this->template->parse('CONTENTS', 'ExpenseTypeList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Expense Type
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsExpenseType = &$this->dsExpenseType; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTEXPENSETYPE_ACT_EDIT) {
                $this->buExpenseType->getExpenseTypeByID($_REQUEST['expenseTypeID'], $dsExpenseType);
                $expenseTypeID = $_REQUEST['expenseTypeID'];
            } else {                                                                    // creating new
                $dsExpenseType->initialise();
                $dsExpenseType->setValue('expenseTypeID', '0');
                $expenseTypeID = '0';
            }
        } else {                                                                        // form validation error
            $dsExpenseType->initialise();
            $dsExpenseType->fetchNext();
            $expenseTypeID = $dsExpenseType->getValue('expenseTypeID');
        }
        if ($_REQUEST['action'] == CTEXPENSETYPE_ACT_EDIT && $this->buExpenseType->canDeleteExpenseType($_REQUEST['expenseTypeID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTEXPENSETYPE_ACT_DELETE,
                        'expenseTypeID' => $expenseTypeID
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
                    'action' => CTEXPENSETYPE_ACT_UPDATE,
                    'expenseTypeID' => $expenseTypeID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTEXPENSETYPE_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Expense Type');
        $this->setTemplateFiles(
            array('ExpenseTypeEdit' => 'ExpenseTypeEdit.inc')
        );
        $this->template->set_var(
            array(
                'expenseTypeID' => $dsExpenseType->getValue('expenseTypeID'),
                'description' => Controller::htmlInputText($dsExpenseType->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsExpenseType->getMessage('description')),
                'mileageFlagChecked' => Controller::htmlChecked($dsExpenseType->getValue('mileageFlag')),
                'vatFlagChecked' => Controller::htmlChecked($dsExpenseType->getValue('vatFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'ExpenseTypeEdit', true);
        $this->parsePage();
    }// end function editExpense Type()

    /**
     * Update call expense type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsExpenseType = &$this->dsExpenseType;
        print_r($_REQUEST['expenseType']);
        $this->formError = (!$this->dsExpenseType->populateFromArray($_REQUEST['expenseType']));
        if ($this->formError) {
            if ($this->dsExpenseType->getValue('expenseTypeID') == '0') {                    // attempt to insert
                $_REQUEST['action'] = CTEXPENSETYPE_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTEXPENSETYPE_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buExpenseType->updateExpenseType($this->dsExpenseType);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'expenseTypeID' => $this->dsExpenseType->getValue('expenseTypeID'),
                                 'action' => CTCNC_ACT_VIEW
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Expense Type
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buExpenseType->deleteExpenseType($_REQUEST['expenseTypeID'])) {
            $this->displayFatalError('Cannot delete this expense type');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTEXPENSETYPE_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>