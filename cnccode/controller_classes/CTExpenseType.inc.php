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
    /** @var DSForm */
    public $dsExpenseType;
    /** @var BUExpenseType */
    public $buExpenseType;

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
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($this->getAction()) {
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
     * Edit/Add Expense Type
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsExpenseType = &$this->dsExpenseType; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTEXPENSETYPE_ACT_EDIT) {
                $this->buExpenseType->getExpenseTypeByID($this->getParam('expenseTypeID'), $dsExpenseType);
                $expenseTypeID = $this->getParam('expenseTypeID');
            } else {                                                                    // creating new
                $dsExpenseType->initialise();
                $dsExpenseType->setValue(DBEExpenseType::expenseTypeID, null);
                $expenseTypeID = null;
            }
        } else {                                                                        // form validation error
            $dsExpenseType->initialise();
            $dsExpenseType->fetchNext();
            $expenseTypeID = $dsExpenseType->getValue(DBEExpenseType::expenseTypeID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTEXPENSETYPE_ACT_EDIT && $this->buExpenseType->canDeleteExpenseType(
                $this->getParam('expenseTypeID')
            )) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'        => CTEXPENSETYPE_ACT_DELETE,
                    'expenseTypeID' => $expenseTypeID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'        => CTEXPENSETYPE_ACT_UPDATE,
                    'expenseTypeID' => $expenseTypeID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
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
                'expenseTypeID'      => $dsExpenseType->getValue(DBEExpenseType::expenseTypeID),
                'description'        => Controller::htmlInputText(
                    $dsExpenseType->getValue(DBEExpenseType::description)
                ),
                'descriptionMessage' => Controller::htmlDisplayText(
                    $dsExpenseType->getMessage(DBEExpenseType::description)
                ),
                'taxableChecked'     => $dsExpenseType->getValue(DBEExpenseType::taxable) ? 'checked' : null,
                'approvalRequiredChecked' => $dsExpenseType->getValue(DBEExpenseType::approvalRequired) ? 'checked' : null,
                'mileageFlagChecked' => Controller::htmlChecked($dsExpenseType->getValue(DBEExpenseType::mileageFlag)),
                'vatFlagChecked'     => Controller::htmlChecked($dsExpenseType->getValue(DBEExpenseType::vatFlag)),
                'urlUpdate'          => $urlUpdate,
                'urlDelete'          => $urlDelete,
                'txtDelete'          => $txtDelete,
                'urlDisplayList'     => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'ExpenseTypeEdit', true);
        $this->parsePage();
    }

    /**
     * Delete Expense Type
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buExpenseType->deleteExpenseType($this->getParam('expenseTypeID'))) {
            $this->displayFatalError('Cannot delete this expense type');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTEXPENSETYPE_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }// end function editExpense Type()

    /**
     * Update call expense type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        print_r($this->getParam('expenseType'));
        $this->formError = (!$this->dsExpenseType->populateFromArray($this->getParam('expenseType')));
        if ($this->formError) {
            if (!$this->dsExpenseType->getValue(DBEExpenseType::expenseTypeID)) {
                $this->setAction(CTEXPENSETYPE_ACT_EDIT);
            } else {
                $this->setAction(CTEXPENSETYPE_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buExpenseType->updateExpenseType($this->dsExpenseType);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'expenseTypeID' => $this->dsExpenseType->getValue(DBEExpenseType::expenseTypeID),
                    'action'        => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Expense Types');
        $this->setTemplateFiles(
            array('ExpenseTypeList' => 'ExpenseTypeList.inc')
        );
        $dsExpenseType = new DataSet($this);

        $this->buExpenseType->getAllTypes($dsExpenseType);

        $urlCreate = Controller::buildLink(
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
                $expenseTypeID = $dsExpenseType->getValue(DBEExpenseType::expenseTypeID);
                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'        => CTEXPENSETYPE_ACT_EDIT,
                            'expenseTypeID' => $expenseTypeID
                        )
                    );
                $txtEdit = '[edit]';

                $this->template->set_var(
                    array(
                        'expenseTypeID' => $expenseTypeID,
                        'description'   => Controller::htmlDisplayText(
                            $dsExpenseType->getValue(DBEExpenseType::description)
                        ),
                        'urlEdit'       => $urlEdit,
                        'txtEdit'       => $txtEdit
                    )
                );
                $this->template->parse('types', 'typeBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'ExpenseTypeList', true);
        $this->parsePage();
    }
}
