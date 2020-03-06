<?php


namespace CNCLTD;


class ExpenseOvertimeEngineerExport
{
    /** @var ExpenseExportItem[] */
    public $expenses = [];
    public $expenseNetTotal = 0;
    public $expenseVATTotal = 0;
    public $expenseGrossTotal = 0;
    public $summaryGrossTotal = 0;
    public $payeTotal = 0;
    /** @var OvertimeExportItem[] */
    public $overtimeActivities = [];
    public $overtimeTotal = 0;
    public $employeeNumber;
    public $userName;
    public $firstName;
    public $lastName;
    public $monthYear;

    public function __construct($monthYear)
    {
        $this->monthYear = $monthYear;
    }
}