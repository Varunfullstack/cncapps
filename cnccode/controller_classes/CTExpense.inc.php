<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTEXPENSE_ACT_EDIT_EXPENSE', 'editExpense');
define('CTEXPENSE_ACT_UPDATE_EXPENSE', 'updateExpense');
define('CTEXPENSE_ACT_CREATE_EXPENSE', 'createExpense');
define('CTEXPENSE_ACT_DELETE_EXPENSE', 'deleteExpense');
define('CTACTIVITY_ACT_EDIT_CALL', 'editCall');
define('CTEXPENSE_ACT_EXPORT_FORM', 'exportForm');
define('CTEXPENSE_ACT_EXPORT_GENERATE', 'exportGenerate');
define('CTEXPENSE_ACT_EXPORT_TRIAL', 'exportTrial');

class CTExpense extends CTCNC
{
    var $dsExpenseExport = '';
    var $dsSearchForm = '';
    var $dsSearchResults = '';
    var $buExpense = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buExpense = new BUExpense($this);
        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchResults = new DSForm($this);
        $this->dsExpense = new DSForm($this);
        $this->dsExpense->copyColumnsFrom($this->buExpense->dbeJExpense);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
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
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->exportExpenseForm();
                break;
            case CTEXPENSE_ACT_EXPORT_GENERATE:
            case CTEXPENSE_ACT_EXPORT_TRIAL:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->exportExpenseGenerate();
                break;
            default:
                $this->displayFatalError('No valid action passed');
                exit;
                break;
        }
    }

    /**
     * Create new call activity
     * inserts a new activity to the DB then displays it
     * @access private
     */
    function createExpense()
    {
        $this->setMethodName('createExpense');
        if ($_REQUEST['callActivityID'] == '') {
            $this->displayError('no callActivityID passed');
            exit;
        }
        $expenseID = $this->buExpense->createExpenseFromCallActivityID($_REQUEST['callActivityID']);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'expenseID' => $expenseID,
                                 'action' => CTEXPENSE_ACT_EDIT_EXPENSE
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Display list of expenses for given callActivity
     * @access private
     */
    function displayExpenses()
    {
        $this->setMethodName('displayExpenses');
        if ($_REQUEST['callActivityID'] == '') {
            $this->displayFatalError('no callActivityID passed');
            exit;
        }

        $this->setPageTitle('Expenses');
        $this->setTemplateFiles(
            array('ExpenseList' => 'ExpenseList.inc')
        );

        $buActivity = new BUActivity($this);
        $buActivity->getActivityByID($_REQUEST['callActivityID'], $dsCallActivity);
//		$buActivity->getCallByID($dsCallActivity->getValue('callID'), $dsCall);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTEXPENSE_ACT_CREATE_EXPENSE,
                    'callActivityID' => $_REQUEST['callActivityID']
                )
            );

        $urlCallActivity =
            $this->buildLink(
                'Activity.php',
                array(
                    'action' => 'displayActivity',
                    'callActivityID' => $dsCallActivity->getValue('callActivityID')
                )
            );

        $this->template->set_var(
            array(
                'callActivityID' => $dsCallActivity->getValue('callActivityID'),
//				'ordheadID' => $dsCall->getValue('ordheadID'),
                'date' => Controller::dateYMDtoDMY($dsCallActivity->getValue('date')),
                'activityType' => Controller::htmlDisplayText($dsCallActivity->getValue('activityType')),
                'customerName' => Controller::htmlDisplayText($dsCallActivity->getValue('customerName')),
                'siteDesc' => Controller::htmlDisplayText($dsCallActivity->getValue('siteDesc')),
                'activityUserName' => Controller::htmlDisplayText($dsCallActivity->getValue('userName')),
                'contractDescription' => Controller::htmlDisplayText($dsCallActivity->getValue('contractDescription')),
                'urlCallActivity' => $urlCallActivity,
                'urlCreate' => $urlCreate
            )
        );

        $this->buExpense->getExpensesByCallActivityID($_REQUEST['callActivityID'], $dsExpense);
        if ($dsExpense->rowCount() > 0) {
            $totalValue = 0;
            $this->template->set_block('ExpenseList', 'expenseBlock', 'expenses');
            while ($dsExpense->fetchNext()) {
                $expenseID = $dsExpense->getValue('expenseID');
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTEXPENSE_ACT_EDIT_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                $txtEdit = '[edit]';
                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTEXPENSE_ACT_DELETE_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'expenseID' => $expenseID,
                        'callActivityID' => $_REQUEST['callActivityID'],
                        'expenseType' => Controller::htmlDisplayText($dsExpense->getValue('expenseType')),
                        'mileage' => Controller::htmlDisplayText($dsExpense->getValue('mileage')),
                        'value' => Controller::formatNumber($dsExpense->getValue('value')),
                        'vatFlag' => Controller::htmlDisplayText($dsExpense->getValue('vatFlag')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );

                $totalValue += $dsExpense->getValue('value');

                $this->template->parse('expenses', 'expenseBlock', true);
            }//while $dsExpense->fetchNext()
            $this->template->set_var('totalValue', Controller::formatNumber($totalValue));
        }
        $this->template->parse('CONTENTS', 'ExpenseList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Expense
     * @access private
     */
    function editExpense()
    {
        $this->setMethodName('editExpense');
        $dsExpense = &$this->dsExpense; // ref to class var
        if (!$this->getFormError()) {
            $this->buExpense->getExpenseByID($_REQUEST['expenseID'], $dsExpense);
            $expenseID = $_REQUEST['expenseID'];
        } else {
            $expenseID = $dsExpense->getValue('expenseID');
        }
        // get the activity and call records
        $callActivityID = $dsExpense->getValue('callActivityID');
        $buActivity = new BUActivity($this);
        $buActivity->getActivityByID($callActivityID, $dsCallActivity);
//		$buActivity->getCallByID($dsCallActivity->getValue('callID'), $dsCall);

        $urlUpdateExpense =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTEXPENSE_ACT_UPDATE_EXPENSE,
                    'expenseID' => $expenseID
                )
            );
        $urlDisplayExpenses =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_VIEW,
                    'callActivityID' => $callActivityID
                )
            );
        $this->setPageTitle('Expense');
        $this->setTemplateFiles(
            array('ExpenseEdit' => 'ExpenseEdit.inc')
        );
        $this->template->set_var(
            array(
                'expenseID' => $_REQUEST['expenseID'],
//				'callID' => $dsCall->getValue('callID'),
//				'callActEngineerID' => $dsExpense->getValue('callActEngineerID'),
                'callActivityID' => $dsExpense->getValue('callActivityID'),
                //			'callID' => $dsCall->getValue('callID'),
//				'ordheadID' => $dsCall->getValue('ordheadID'),
                'date' => Controller::dateYMDtoDMY($dsCallActivity->getValue('date')),
                'activityType' => Controller::htmlDisplayText($dsCallActivity->getValue('activityType')),
                'customerName' => Controller::htmlDisplayText($dsCallActivity->getValue('customerName')),
                'siteDesc' => Controller::htmlDisplayText($dsCallActivity->getValue('siteDesc')),
                'contractDescription' => Controller::htmlDisplayText($dsCallActivity->getValue('contractDescription')),
                'mileage' => Controller::htmlInputText($dsExpense->getValue('mileage')),
                'mileageMessage' => Controller::htmlDisplayText($dsExpense->getMessage('mileage')),
                'value' => Controller::htmlInputText($dsExpense->getValue('value')),
                'valueMessage' => Controller::htmlDisplayText($dsExpense->getMessage('value')),
                'vatFlagChecked' => Controller::htmlChecked($dsExpense->getValue('vatFlag')),
                'userID' => $dsExpense->getValue('userID'), // hidden field on form
                'activityUserName' => $dsCallActivity->getValue('userName'),
                'value' => Controller::htmlInputText($dsExpense->getValue('value')),
                'urlUpdateExpense' => $urlUpdateExpense,
                'exportedFlag' => $dsExpense->getValue('exportedFlag'),
                'urlDisplayExpenses' => $urlDisplayExpenses
            )
        );
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRows();
        $this->template->set_block('ExpenseEdit', 'expenseTypeBlock', 'expenseTypes');
        while ($dbeExpenseType->fetchNext()) {
            $expenseTypeSelected = ($dsExpense->getValue("expenseTypeID") == $dbeExpenseType->getValue("expenseTypeID")) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'expenseTypeSelected' => $expenseTypeSelected,
                    'expenseTypeID' => $dbeExpenseType->getValue("expenseTypeID"),
                    'expenseTypeDesc' => $dbeExpenseType->getValue("description")
                )
            );
            $this->template->parse('expenseTypes', 'expenseTypeBlock', true);
        }
        $this->template->parse('CONTENTS', 'ExpenseEdit', true);
        $this->parsePage();
    }// end function editExpense()

    /**
     * Update expense details
     * @access private
     */
    function updateExpense()
    {
        $this->setMethodName('updateExpense');
        $dsExpense = &$this->dsExpense;
        $this->formError = (!$this->dsExpense->populateFromArray($_REQUEST['expense']));


        if ($this->formError) {
            $this->editExpense();
            exit;
        }

        $this->buExpense->updateExpense($this->dsExpense);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'callActivityID' => $this->dsExpense->getValue('callActivityID'),
                                 'action' => CTCNC_ACT_VIEW
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Expense
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteExpense()
    {
        $this->setMethodName('deleteExpense');
        $this->buExpense->getExpenseByID($_REQUEST['expenseID'], $dsExpense);
        $buActivity = new BUActivity($this);
        if (!$this->buExpense->canDeleteExpense($_REQUEST['expenseID'])) {
            $this->displayFatalError('Cannot delete expense - already exported');
            exit;
        } else {
            $callActivityID = $this->buExpense->deleteExpense($_REQUEST['expenseID']);
        }
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_VIEW,
                    'callActivityID' => $callActivityID
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     */
    function exportExpenseForm()
    {
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTEXPENSE_ACT_EXPORT_GENERATE
            )
        );
        $this->setPageTitle('Export Expenses and Overtime');
        $this->setTemplateFiles('ExpenseExport', 'ExpenseExport.inc');
        if (!$this->getFormError()) {
            $this->buExpense->initialiseExportDataset($this->dsExpenseExport); // we reuse this form
        }
        $this->template->set_var(
            array(
                'endDate' => Controller::dateYMDtoDMY($this->dsExpenseExport->getValue('endDate')),
                'endDateMessage' => Controller::dateYMDtoDMY($this->dsExpenseExport->getMessage('endDate')),
                'urlSubmit' => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'ExpenseExport', true);
        $this->parsePage();
    }

    function exportExpenseGenerate()
    {
        $this->setMethodName('exportExpenseGenerate');
        $this->buExpense->initialiseExportDataset($this->dsExpenseExport);
        if (!$this->dsExpenseExport->populateFromArray($_REQUEST['expenseExport'])) {
            $this->setFormErrorOn();
            $this->exportExpenseForm(); //redisplay with errors
        } else {
            // do export
            $overtimeExported = $this->buExpense->exportEngineerOvertime($this->dsExpenseExport,
                                                                         $_REQUEST['exportType']);
            $expensesExported = $this->buExpense->exportEngineerExpenses($this->dsExpenseExport,
                                                                         $_REQUEST['exportType']);

            if ($_REQUEST['exportType'] == 'Export') {

                if ($overtimeExported OR $expensesExported) {
                    $this->setFormErrorMessage('Export files created and emails sent');
                } else {
                    $this->setFormErrorMessage('No data to export for this date');
                }
            } else { // trial

                if ($overtimeExported OR $expensesExported) {
                    $this->setFormErrorMessage('There are records to export. Email sent to the sales manager');
                } else {
                    $this->setFormErrorMessage('No data to export for this date');
                }

            }

            $this->exportExpenseForm();
        }
        // to display
    }
}// end of class
?>