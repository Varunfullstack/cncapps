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
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEReceipt.php');

class CTReceipt extends CTCNC
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
            "technical",
            "sales"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'show':
                $receiptID = $this->getParam('receiptID');
                if (!$receiptID) {
                    $this->displayFatalError('Receipt ID required');
                }

                $dbeReceipt = new DBEReceipt($this);
                $dbeReceipt->getRow($receiptID);
                $expenseID = $dbeReceipt->getValue(DBEReceipt::expenseId);
                $dbeExpense = new DBEExpense($this);
                $dbeExpense->getRow($expenseID);
                $relatedActivityID = $dbeExpense->getValue(DBEExpense::callActivityID);
                $dbeCallActivity = new DBECallActivity($this);
                $dbeCallActivity->getRow($relatedActivityID);
                if (!$this->dbeUser->getValue(DBEUser::isExpenseApprover) && $dbeCallActivity->getValue(
                        DBECallActivity::userID
                    ) != $this->userID) {
                    $this->displayFatalError('You cannot access this receipt');
                }

                // all good ...so we should show the file
                header("Content-Type: " . $dbeReceipt->getValue(DBEReceipt::fileMIMEType));
                header("Content-Disposition: inline;");
                readfile(RECEIPT_PATH . $dbeReceipt->getValue(DBEReceipt::filePath));
                break;
            case 'upload':
                $expenseID = $this->getParam('expenseID');
                try {

                    // Undefined | Multiple Files | $_FILES Corruption Attack
                    // If this request falls under any of them, treat it invalid.
                    if (
                        !isset($_FILES['upfile']['error']) ||
                        is_array($_FILES['upfile']['error'])
                    ) {
                        throw new RuntimeException('Invalid parameters.');
                    }

                    // Check $_FILES['upfile']['error'] value.
                    switch ($_FILES['upfile']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new RuntimeException('Exceeded filesize limit.');
                        default:
                            throw new RuntimeException('Unknown errors.');
                    }

                    // You should also check filesize here.
                    if ($_FILES['upfile']['size'] > 1000000) {
                        throw new RuntimeException('Exceeded filesize limit.');
                    }

                    $this->upload($expenseID, $_FILES['upfile']['tmp_name']);
                    $response = ["status" => "ok"];
                } catch (Exception $exception) {
                    $response = ["error" => $exception->getMessage()];
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            default:
                $this->displayFatalError('No valid action passed');
                exit;
                break;
        }
    }

    function upload($expenseID, $fileUploaded)
    {
        if (!$expenseID) {
            throw new RuntimeException('Expense ID required');
        }

        if (!$fileUploaded) {
            throw new RuntimeException('File is required');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->file($fileUploaded);
        if (false === $ext = array_search(
                $mimeType,
                array(
                    'jpg' => 'image/jpeg',
                    'pdf' => 'application/pdf'
                ),
                true
            )) {
            throw new RuntimeException('Invalid type of file');
        }

        if ($ext === "pdf") {
            if (filesize($fileUploaded) > 500000) {
                throw new RuntimeException('The file is too big, max 500KB');
            }
        }
        if ($ext === 'jpg') {
            $image = \Intervention\Image\ImageManagerStatic::make($fileUploaded);
            $image->resize(800, 800);
            $image->save($fileUploaded);
        }
        $filePath = RECEIPT_PATH . uniqid('receipt') . "." . $ext;
        rename($fileUploaded, RECEIPT_PATH . uniqid('receipt') . "." . $ext);

        $dbeReceipt = new DBEReceipt($this);
        $dbeReceipt->setValue(DBEReceipt::fileMIMEType, $mimeType);
        $dbeReceipt->setValue(DBEReceipt::filePath, $filePath);
        $dbeReceipt->setValue(DBEReceipt::expenseId, $expenseID);
        $dbeReceipt->insertRow();
        return $dbeReceipt->getPK();
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

        $buActivity = new BUActivity($this);
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );

        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTEXPENSE_ACT_CREATE_EXPENSE,
                    'callActivityID' => $this->getParam('callActivityID')
                )
            );

        $urlCallActivity =
            Controller::buildLink(
                'Activity.php',
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
                $expenseID = $dsExpense->getValue(DBEJExpense::expenseID);
                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTEXPENSE_ACT_EDIT_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                $txtEdit = '[edit]';
                $urlDelete =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTEXPENSE_ACT_DELETE_EXPENSE,
                            'expenseID' => $expenseID
                        )
                    );
                $txtDelete = '[delete]';

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
                        'txtDelete'      => $txtDelete
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
        } else {
            $callActivityID = $this->buExpense->deleteExpense($this->getParam('expenseID'));
        }
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTCNC_ACT_VIEW,
                    'callActivityID' => $callActivityID
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Update expense details
     * @access private
     * @throws Exception
     */
    function updateExpense()
    {
        $this->setMethodName('updateExpense');
        $this->formError = (!$this->dsExpense->populateFromArray($this->getParam('expense')));


        if ($this->formError) {
            $this->editExpense();
            exit;
        }

        $this->buExpense->updateExpense($this->dsExpense);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $this->dsExpense->getValue(DBEJExpense::callActivityID),
                    'action'         => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }// end function editExpense()

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
        $buActivity = new BUActivity($this);
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $urlUpdateExpense =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTEXPENSE_ACT_UPDATE_EXPENSE,
                    'expenseID' => $expenseID
                )
            );
        $urlDisplayExpenses =
            Controller::buildLink(
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
                'userID'              => $dsExpense->getValue(DBEJExpense::userID), // hidden field on form
                'activityUserName'    => $dsCallActivity->getValue(DBEJCallActivity::userName),
                'urlUpdateExpense'    => $urlUpdateExpense,
                'exportedFlag'        => $dsExpense->getValue(DBEJExpense::exportedFlag),
                'urlDisplayExpenses'  => $urlDisplayExpenses
            )
        );
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRows();
        $this->template->set_block(
            'ExpenseEdit',
            'expenseTypeBlock',
            'expenseTypes'
        );
        $buExpenseType = new BUExpenseType($this);
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
                    'expenseTypeDesc'     => $dbeExpenseType->getValue(DBEExpenseType::description)
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

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'expenseID' => $expenseID,
                    'action'    => CTEXPENSE_ACT_EDIT_EXPENSE
                )
            );
        header('Location: ' . $urlNext);
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
            $overtimeExported = $this->buExpense->exportEngineerOvertime(
                $this->dsExpenseExport,
                $this->getParam('exportType')
            );
            $expensesExported = $this->buExpense->exportEngineerExpenses(
                $this->dsExpenseExport,
                $this->getParam('exportType')
            );

            if ($this->getParam('exportType') == 'Export') {

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
                'endDate'        => Controller::dateYMDtoDMY(
                    $this->dsExpenseExport->getValue(BUExpense::exportDataSetEndDate)
                ),
                'endDateMessage' => Controller::dateYMDtoDMY(
                    $this->dsExpenseExport->getMessage(BUExpense::exportDataSetEndDate)
                ),
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
}
