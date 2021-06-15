<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEReceipt.php');
// Actions
define(
    'CTEXPENSE_ACT_EDIT_EXPENSE',
    'editExpense'
);
define(
    'CTEXPENSE_ACT_UPDATE_EXPENSE',
    'updateExpense'
);
define(
    'CTEXPENSE_ACT_CREATE_EXPENSE',
    'createExpense'
);
define(
    'CTEXPENSE_ACT_DELETE_EXPENSE',
    'deleteExpense'
);
define(
    'CTACTIVITY_ACT_EDIT_CALL',
    'editCall'
);
define(
    'CTEXPENSE_ACT_EXPORT_FORM',
    'exportForm'
);
define(
    'CTEXPENSE_ACT_EXPORT_GENERATE',
    'exportGenerate'
);
define(
    'CTEXPENSE_ACT_EXPORT_TRIAL',
    'exportTrial'
);

class CTExpense extends CTCNC
{
    /** @var DSForm */
    public $dsExpenseExport;
    /** @var DSForm */
    public $dsSearchForm;
    /** @var DSForm */
    public $dsSearchResults;
    /** @var BUExpense */
    public $buExpense;
    /** @var DSForm */
    private $dsExpense;

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
            ACCOUNTS_PERMISSION,
            TECHNICAL_PERMISSION,
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(708);
        $this->buExpense       = new BUExpense($this);
        $this->dsSearchForm    = new DSForm($this);
        $this->dsSearchResults = new DSForm($this);
        $this->dsExpense       = new DSForm($this);
        $this->dsExpense->copyColumnsFrom($this->buExpense->dbeJExpense);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTCNC_ACT_VIEW:
                $this->displayExpenses();
                break;
            case CTEXPENSE_ACT_EDIT_EXPENSE:
                $this->editExpense();
                break;
            case CTEXPENSE_ACT_DELETE_EXPENSE:
                $this->deleteExpense();
                break;
            case CTEXPENSE_ACT_UPDATE_EXPENSE:
                $this->updateExpense();
                break;
            case CTEXPENSE_ACT_CREATE_EXPENSE:
                $this->createExpense();
                break;
            case CTEXPENSE_ACT_EXPORT_FORM:
                $this->checkPermissions(ACCOUNTS_PERMISSION);
                $this->exportExpenseForm();
                break;
            case CTEXPENSE_ACT_EXPORT_GENERATE:
            case CTEXPENSE_ACT_EXPORT_TRIAL:
                $this->checkPermissions(ACCOUNTS_PERMISSION);
                $this->exportExpenseGenerate();
                break;
            default:
                $this->displayFatalError('No valid action passed');
                exit;
                break;
        }
    }

    /**
     * Display list of expenses for given callActivity
     * @access private
     * @throws Exception
     */
    function displayExpenses()
    {
        $this->setMethodName('displayExpenses');
        if (!$this->getParam('callActivityID')) {
            $this->displayFatalError('no callActivityID passed');
            exit;
        }
        $this->setPageTitle('Expenses');
        $this->setTemplateFiles(
            array('ExpenseList' => 'ExpenseList.inc')
        );
        $buActivity     = new BUActivity($this);
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTEXPENSE_ACT_CREATE_EXPENSE,
                'callActivityID' => $this->getParam('callActivityID')
            )
        );
        $urlCallActivity = Controller::buildLink(
            'SRActivity.php',
            array(
                'action'         => 'displayActivity',
                'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
            )
        );
        $this->template->set_var(
            array(
                'callActivityID'      => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                'date'                => Controller::dateYMDtoDMY($dsCallActivity->getValue(DBEJCallActivity::date)),
                'activityType'        => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::activityType)
                ),
                'customerName'        => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::customerName)
                ),
                'siteDesc'            => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::siteDesc)
                ),
                'activityUserName'    => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::userName)
                ),
                'contractDescription' => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::contractDescription)
                ),
                'urlCallActivity'     => $urlCallActivity,
                'urlCreate'           => $urlCreate
            )
        );
        $dsExpense = new DataSet($this);
        $this->buExpense->getExpensesByCallActivityID(
            $this->getParam('callActivityID'),
            $dsExpense
        );
        if ($dsExpense->rowCount() > 0) {
            $totalValue = 0;
            $this->template->set_block(
                'ExpenseList',
                'expenseBlock',
                'expenses'
            );
            while ($dsExpense->fetchNext()) {
                $expenseID      = $dsExpense->getValue(DBEJExpense::expenseID);
                $expenseTypeID  = $dsExpense->getValue(DBEJExpense::expenseTypeID);
                $dbeExpenseType = new DBEExpenseType($this);
                $dbeExpenseType->getRow($expenseTypeID);
                $uploadReceipt = null;
                if ($dbeExpenseType->getValue(DBEExpenseType::receiptRequired)) {
                    $dbeReceipt = new DBEReceipt($this);
                    $dbeReceipt->getReceiptByExpenseId($expenseID);
                    if ($dbeReceipt->rowCount()) {
                        $uploadReceipt = "<a href='/Receipt.php?action=show&receiptID=" . $dbeReceipt->getValue(
                                DBEReceipt::id
                            ) . "' target='_blank'>See Receipt</a>";
                    } else {
                        $uploadReceipt = 'Upload Required <input type="file" accept="image/jpeg,application/pdf,image/png" onchange="uploadReceipt(' . $expenseID . ')" >';
                    }


                }
                $urlEdit = $urlDelete = $txtEdit = $txtDelete = null;
                if ($dsExpense->getValue(DBEExpense::exportedFlag) != 'Y' && !$dsExpense->getValue(
                        DBEExpense::deniedReason
                    ) && !$dsExpense->getValue(DBEExpense::approvedBy)) {
                    $urlEdit   = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTEXPENSE_ACT_EDIT_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                    $txtEdit   = '[edit]';
                    $urlDelete = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTEXPENSE_ACT_DELETE_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                    $txtDelete = '[delete]';
                }
                $this->template->set_var(
                    array(
                        'expenseID'      => $expenseID,
                        'callActivityID' => $this->getParam('callActivityID'),
                        'expenseType'    => Controller::htmlDisplayText($dsExpense->getValue(DBEJExpense::expenseType)),
                        'mileage'        => Controller::htmlDisplayText($dsExpense->getValue(DBEJExpense::mileage)),
                        'value'          => Controller::formatNumber($dsExpense->getValue(DBEJExpense::value)),
                        'vatFlag'        => Controller::htmlDisplayText($dsExpense->getValue(DBEJExpense::vatFlag)),
                        'urlEdit'        => $urlEdit,
                        'urlDelete'      => $urlDelete,
                        'txtEdit'        => $txtEdit,
                        'txtDelete'      => $txtDelete,
                        'receiptUpload'  => $uploadReceipt,
                        'status'         => $dsExpense->getValue(
                            DBEExpense::approvedBy
                        ) ? 'Approved' : ($dsExpense->getValue(DBEExpense::deniedReason) ? 'Denied' : 'Pending')
                    )
                );
                $totalValue += $dsExpense->getValue(DBEJExpense::value);
                $this->template->parse(
                    'expenses',
                    'expenseBlock',
                    true
                );
            }//while $dsExpense->fetchNext()
            $this->template->set_var(
                'totalValue',
                Controller::formatNumber($totalValue)
            );
        }
        $this->template->parse(
            'CONTENTS',
            'ExpenseList',
            true
        );
        $this->parsePage();
    }

    /**
     * Edit/Add Expense
     * @access private
     * @throws Exception
     */
    function editExpense()
    {
        $this->setMethodName('editExpense');
        $dsExpense = &$this->dsExpense; // ref to class var
        if (!$this->getFormError()) {
            $this->buExpense->getExpenseByID(
                $this->getParam('expenseID'),
                $dsExpense
            );
            $expenseID = $this->getParam('expenseID');
        } else {
            $expenseID = $dsExpense->getValue(DBEJExpense::expenseID);
        }
        // get the activity and call records
        $callActivityID = $dsExpense->getValue(DBEJExpense::callActivityID);
        $buActivity     = new BUActivity($this);
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $urlUpdateExpense   = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTEXPENSE_ACT_UPDATE_EXPENSE,
                'expenseID' => $expenseID
            )
        );
        $urlDisplayExpenses = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTCNC_ACT_VIEW,
                'callActivityID' => $callActivityID
            )
        );
        $this->setPageTitle('Expense');
        $this->setTemplateFiles(
            array('ExpenseEdit' => 'ExpenseEdit.inc')
        );
        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($dsExpense->getValue(DBEJExpense::userID));
        $userMileageRate = $dbeUser->getValue(DBEUser::petrolRate);
        $this->template->set_var(
            array(
                'expenseID'           => $this->getParam('expenseID'),
                'callActivityID'      => $dsExpense->getValue(DBEJExpense::callActivityID),
                'date'                => Controller::dateYMDtoDMY($dsCallActivity->getValue(DBEJCallActivity::date)),
                'activityType'        => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::activityType)
                ),
                'customerName'        => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::customerName)
                ),
                'siteDesc'            => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::siteDesc)
                ),
                'contractDescription' => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::contractDescription)
                ),
                'mileage'             => Controller::htmlInputText($dsExpense->getValue(DBEJExpense::mileage)),
                'mileageMessage'      => Controller::htmlDisplayText($dsExpense->getMessage(DBEJExpense::mileage)),
                'value'               => Controller::htmlInputText($dsExpense->getValue(DBEJExpense::value)),
                'valueMessage'        => Controller::htmlDisplayText($dsExpense->getMessage(DBEJExpense::value)),
                'vatFlagChecked'      => Controller::htmlChecked($dsExpense->getValue(DBEJExpense::vatFlag)),
                'dateSubmitted'       => $dsExpense->getValue(DBEExpense::dateSubmitted),
                'approvedDate'        => $dsExpense->getValue(DBEExpense::approvedDate),
                'approvedBy'          => $dsExpense->getValue(DBEExpense::approvedBy),
                'deniedReason'        => $dsExpense->getValue(DBEExpense::deniedReason),
                'userID'              => $dsExpense->getValue(DBEJExpense::userID), // hidden field on form
                'activityUserName'    => $dsCallActivity->getValue(DBEJCallActivity::userName),
                'urlUpdateExpense'    => $urlUpdateExpense,
                'exportedFlag'        => $dsExpense->getValue(DBEJExpense::exportedFlag),
                'urlDisplayExpenses'  => $urlDisplayExpenses,
                'userMileageRate'     => $userMileageRate
            )
        );
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRows();
        $this->template->set_block(
            'ExpenseEdit',
            'expenseTypeBlock',
            'expenseTypes'
        );
        $buExpenseType   = new BUExpenseType($this);
        $allowedExpenses = $buExpenseType->getExpenseTypesAllowedForActivityTypeID(
            $dsCallActivity->getValue(DBECallActivity::callActTypeID)
        );
        while ($dbeExpenseType->fetchNext()) {
            if (!in_array($dbeExpenseType->getValue(DBEExpenseType::expenseTypeID), $allowedExpenses)) {
                continue;
            }
            $expenseTypeSelected = ($dsExpense->getValue(DBEJExpense::expenseTypeID) == $dbeExpenseType->getValue(
                    DBEExpenseType::expenseTypeID
                )) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'expenseTypeSelected' => $expenseTypeSelected,
                    'expenseTypeID'       => $dbeExpenseType->getValue(DBEExpenseType::expenseTypeID),
                    'expenseTypeDesc'     => $dbeExpenseType->getValue(DBEExpenseType::description),
                    "isMileage"           => $dbeExpenseType->getValue(
                        DBEExpenseType::mileageFlag
                    ) == 'Y' ? 'data-is-mileage="1"' : '',
                    "allowsTax"           => $dbeExpenseType->getValue(
                        DBEExpenseType::vatFlag
                    ) == 'Y' ? 'data-allows-tax="1"' : '',
                )
            );
            $this->template->parse(
                'expenseTypes',
                'expenseTypeBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'ExpenseEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Delete Expense
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteExpense()
    {
        $this->setMethodName('deleteExpense');
        $this->buExpense->getExpenseByID(
            $this->getParam('expenseID'),
            $dsExpense
        );
        if (!$this->buExpense->canDeleteExpense($this->getParam('expenseID'))) {
            $this->displayFatalError('Cannot delete expense - already exported');
            exit;
        }
        $callActivityID = $this->buExpense->deleteExpense($this->getParam('expenseID'));
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTCNC_ACT_VIEW,
                'callActivityID' => $callActivityID
            )
        );
        header('Location: ' . $urlNext);
    }// end function editExpense()

    /**
     * Update expense details
     * @access private
     * @throws Exception
     */
    function updateExpense()
    {
        $this->setMethodName('updateExpense');
        $expenseToUpdate = $this->getParam('expense')[1];
        $expenseID       = $expenseToUpdate['expenseID'];
        $dbeExpense      = new DBEExpense($this);
        $dbeExpense->getRow($expenseID);
        $expenseToUpdate[DBEExpense::deniedReason] = $dbeExpense->getValue(DBEExpense::deniedReason);
        $this->formError                           = (!$this->dsExpense->populateFromArray([$expenseToUpdate]));
        if ($this->formError) {
            $this->editExpense();
            exit;
        }
        if ($this->getParam('submit') === 'Delete') {
            $this->buExpense->deleteExpense($this->dsExpense->getValue(DBEExpense::expenseID));
        } else {
            $this->buExpense->updateExpense($this->dsExpense);
        }
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'callActivityID' => $this->dsExpense->getValue(DBEJExpense::callActivityID),
                'action'         => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Create new call activity
     * inserts a new activity to the DB then displays it
     * @access private
     * @throws Exception
     */
    function createExpense()
    {
        $this->setMethodName('createExpense');
        if (!$this->getParam('callActivityID')) {
            throw new Exception('Call activity ID not provided');
        }
        $expenseID = $this->buExpense->createExpenseFromCallActivityID($this->getParam('callActivityID'));
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'expenseID' => $expenseID,
                'action'    => CTEXPENSE_ACT_EDIT_EXPENSE
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception
     */
    function exportExpenseForm()
    {
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTEXPENSE_ACT_EXPORT_GENERATE
            )
        );
        $this->setPageTitle('Export Expenses and Overtime');
        $this->setTemplateFiles(
            'ExpenseExport',
            'ExpenseExport.inc'
        );
        if (!$this->getFormError()) {
            $this->buExpense->initialiseExportDataset($this->dsExpenseExport); // we reuse this form
        }
        $this->template->set_var(
            array(
                'endDate'        => $this->dsExpenseExport->getValue(BUExpense::exportDataSetEndDate),
                'endDateMessage' => $this->dsExpenseExport->getMessage(BUExpense::exportDataSetEndDate),
                'urlSubmit'      => $urlSubmit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ExpenseExport',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function exportExpenseGenerate()
    {
        $this->setMethodName('exportExpenseGenerate');
        $this->buExpense->initialiseExportDataset($this->dsExpenseExport);
        if (!$this->dsExpenseExport->populateFromArray($this->getParam('expenseExport'))) {
            $this->setFormErrorOn();
            $this->exportExpenseForm(); //redisplay with errors
        } else {
            // do export
            $expensesExported = $this->buExpense->exportOvertimeAndExpenses(
                $this->dsExpenseExport,
                $this->getParam('exportType'),
                $this->dbeUser
            );
            if ($this->getParam('exportType') == 'Export') {
                $dbeHeader = new DBEHeader($this);
                $dbeHeader->getRow(1);
                $dbeHeader->setValue(
                    DBEHeader::expensesNextProcessingDate,
                    $this->dsExpenseExport->getValue(BUExpense::expensesNextProcessingDate)
                );
                $dbeHeader->updateRow();
                if ($expensesExported) {
                    $this->setFormErrorMessage('Data sent to CNC Payroll');
                } else {
                    $this->setFormErrorMessage('No data to export for this date');
                }
            } else { // trial
                if ($expensesExported) {
                    $this->setFormErrorMessage('Trial data sent to CNC Payroll');
                } else {
                    $this->setFormErrorMessage('No data to export for this date');
                }

            }
            $this->exportExpenseForm();
        }
        // to display
    }
}
