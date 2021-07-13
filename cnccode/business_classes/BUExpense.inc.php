<?php /**
 * Expense business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\ExpenseExportItem;
use CNCLTD\ExpenseOvertimeEngineerExport;
use CNCLTD\OvertimeExportItem;
use Twig\Environment;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");
require_once($cfg["path_dbe"] . "/DBEJExpense.inc.php");
require_once($cfg["path_dbe"] . "/DBEVat.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpenseType.inc.php");
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg["path_func"] . "/activity.inc.php");


class BUExpense extends Business
{
    const exportDataSetEndDate       = 'endDate';
    const expensesNextProcessingDate = "expensesNextProcessingDate";
    public $dbeJExpense;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJExpense = new DBEJExpense($this);
    }

    function getExpenseByID($expenseID,
                            &$dsResults
    )
    {
        $this->dbeJExpense->setPKValue($expenseID);
        $this->dbeJExpense->getRow();
        return ($this->getData(
            $this->dbeJExpense,
            $dsResults
        ));
    }

    function getExpensesByCallActivityID($callActivityID,
                                         &$dsResults
    )
    {
        $this->dbeJExpense->getRowsByCallActivityID($callActivityID);
        return ($this->getData(
            $this->dbeJExpense,
            $dsResults
        ));
    }

    function createExpenseFromCallActivityID($callActivityID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRow(2);                                        // default to mileage
        $dbeExpense = new DBEExpense($this);
        $dbeExpense->setValue(
            DBEExpense::expenseID,
            0
        );
        $dbeExpense->setValue(
            DBEExpense::callActivityID,
            $callActivityID
        );
        $dbeExpense->setValue(
            DBEExpense::expenseTypeID,
            2
        );
        $dbeExpense->setValue(
            DBEExpense::mileage,
            0
        );
        $dbeExpense->setValue(
            DBEExpense::exportedFlag,
            'N'
        );
        $dbeExpense->setValue(
            DBEExpense::value,
            0
        );
        $dbeExpense->setValue(
            DBEExpense::vatFlag,
            $dbeExpenseType->getValue(DBEExpenseType::vatFlag)
        );            // default for this expense type
        $dbeExpense->setValue(
            DBEExpense::dateSubmitted,
            date('d/m/Y H:i:s')
        );
        $dbeExpense->setValue(DBEExpense::deniedReason, null);
        $dbeExpense->insertRow();
        $expenseID = $dbeExpense->getPKValue();
        return ($expenseID);
    }

    /**
     *    canDeleteExpense
     * Only allowed if not exported
     * @param $expenseID
     * @return bool
     */
    function canDeleteExpense($expenseID)
    {
        $this->setMethodName('deleteExpense');
        // get the call activity no to return
        $dbeExpense = new DBEExpense($this);
        $dbeExpense->getRow($expenseID);
        if ($dbeExpense->getValue(DBEExpense::exportedFlag) == 'Y' || $dbeExpense->getValue(
                DBEExpense::deniedReason
            ) || $dbeExpense->getValue(DBEExpense::approvedBy)) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * deleteExpense
     *
     * Deletes an expense row and returns the calllactivityNo
     * @param $expenseID
     * @return bool|float|int|string
     */
    function deleteExpense($expenseID)
    {
        $this->setMethodName('deleteExpense');
        // get the call activity no to return
        $dbeJExpense = new DBEJExpense($this);
        $dbeJExpense->getRow($expenseID);
        $callActivityID = $dbeJExpense->getValue(DBEJExpense::callActivityID);
        // Delete the expense row
        $dbeExpense = new DBEExpense($this);
        $dbeExpense->setPKValue($expenseID);
        $dbeExpense->deleteRow();
        return ($callActivityID);
    }

    /**
     * @param DataSet $dsExpense
     * @return bool
     */
    function updateExpense(&$dsExpense)
    {
        $this->setMethodName('updateExpense');
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRow($dsExpense->getValue(DBEJExpense::expenseTypeID));
        // if mileage then calculate mileage value from employee milage rate * mileage and set value to that
        if ($dbeExpenseType->getValue(DBEExpenseType::mileageFlag) == 'Y') {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($dsExpense->getValue(DBEJExpense::userID));
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue(
                DBEJExpense::value,
                $dbeUser->getValue(DBEUser::petrolRate) * $dsExpense->getValue(DBEJExpense::mileage)
            );
            $dsExpense->post();
        } else {
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue(
                DBEJExpense::mileage,
                null
            );
            $dsExpense->post();
        }
        if ($dsExpense->getValue(DBEJExpense::vatFlag) != 'Y') {
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue(
                DBEJExpense::vatFlag,
                'N'
            );
            $dsExpense->post();
        }
        $dbeExpense = new DBEExpense($this);
        $this->updateDataAccessObject(
            $dsExpense,
            $dbeExpense
        );
        return TRUE;
    }

    /**
     * initialise values for input of date range
     * @param DataSet $dsData
     * @return void $dsData results
     * @access public
     */
    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::exportDataSetEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::expensesNextProcessingDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
    }

    /**
     * Export engineer expenses to file
     * @param DataSet $dsData
     * @param string $runType
     * @param DBEUser $dbeUser
     * @return bool
     */
    function exportOvertimeAndExpenses(&$dsData,
                                       $runType,
                                       $dbeUser
    )
    {
        /** @var dbSweetcode $db */ global $db;
        $this->setMethodName('exportEngineerExpenses');
        $date                     = DateTime::createFromFormat(
            DATE_MYSQL_DATE,
            $dsData->getValue(self::exportDataSetEndDate)
        );
        $nextProcessingDate       = DateTime::createFromFormat(
            DATE_MYSQL_DATE,
            $dsData->getValue(self::expensesNextProcessingDate)
        );
        $expenses                 = $this->getExpenseToDateExportData($date);
        $overtimeActivities       = $this->getOvertimeToDateExportData($date);
        $engineersData            = [];
        $monthYear                = substr(
                $dsData->getValue(self::exportDataSetEndDate),
                5,
                2
            ) . '/' . substr(
                $dsData->getValue(self::exportDataSetEndDate),
                0,
                4
            );
        $overtimeToFlagAsExported = [];
        $expensesToFlagAsExported = [];
        foreach ($expenses as $expenseExportItem) {

            if ($expenseExportItem->isDenied) {
                $expensesToFlagAsExported[] = $expenseExportItem;
                continue;
            }
            if (!isset($engineersData[$expenseExportItem->engineerName])) {
                $engineersData[$expenseExportItem->engineerName] = new ExpenseOvertimeEngineerExport(
                    $monthYear
                );
            }
            $engineersData[$expenseExportItem->engineerName]->expenses[]        = $expenseExportItem;
            $engineersData[$expenseExportItem->engineerName]->userName          = $expenseExportItem->engineerUserName;
            $engineersData[$expenseExportItem->engineerName]->firstName         = $expenseExportItem->engineerFirstName;
            $engineersData[$expenseExportItem->engineerName]->lastName          = $expenseExportItem->engineerLastName;
            $engineersData[$expenseExportItem->engineerName]->expenseNetTotal   += $expenseExportItem->netValue;
            $engineersData[$expenseExportItem->engineerName]->expenseVATTotal   += $expenseExportItem->VATValue;
            $engineersData[$expenseExportItem->engineerName]->expenseGrossTotal += $expenseExportItem->grossValue;
            if ($expenseExportItem->payeTaxable) {
                $engineersData[$expenseExportItem->engineerName]->payeTotal += $expenseExportItem->grossValue;
            } else {
                $engineersData[$expenseExportItem->engineerName]->summaryGrossTotal += $expenseExportItem->grossValue;
            }
            $engineersData[$expenseExportItem->engineerName]->employeeNumber = $expenseExportItem->employeeNumber;
        }
        $buHeader  = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);
        foreach ($overtimeActivities as $overtimeExportItem) {

            if ($overtimeExportItem->belowThreshold || $overtimeExportItem->isDenied) {
                $overtimeToFlagAsExported[] = $overtimeExportItem;
                continue;
            }
            if (!isset($engineersData[$overtimeExportItem->engineerName])) {
                $engineersData[$overtimeExportItem->engineerName] = new ExpenseOvertimeEngineerExport(
                    $monthYear
                );
            }
            $engineersData[$overtimeExportItem->engineerName]->overtimeActivities[] = $overtimeExportItem;
            $engineersData[$overtimeExportItem->engineerName]->userName             = $overtimeExportItem->engineerUserName;
            $engineersData[$overtimeExportItem->engineerName]->firstName            = $overtimeExportItem->engineerFirstName;
            $engineersData[$overtimeExportItem->engineerName]->lastName             = $overtimeExportItem->engineerLastName;
            $engineersData[$overtimeExportItem->engineerName]->employeeNumber       = $overtimeExportItem->employeeNumber;
            $engineersData[$overtimeExportItem->engineerName]->overtimeTotal        += $overtimeExportItem->overtimeValue;

        }
        $expenseJournalCSVData = [];
        $summaryReportCSVData  = [];
        if ($runType == 'Export') {

            /** @var OvertimeExportItem $toIgnoreOvertimeActivity */
            foreach ($overtimeToFlagAsExported as $toIgnoreOvertimeActivity) {
                $queryString = "UPDATE callactivity SET caa_ot_exp_flag = 'Y'
                    WHERE caa_callactivityno = ?";
                $db->preparedQuery($queryString, [["type" => "i", "value" => $toIgnoreOvertimeActivity->activityId]]);
            }
            /** @var ExpenseExportItem $toIgnoreExpense */
            foreach ($expensesToFlagAsExported as $toIgnoreExpense) {
                $queryString = "UPDATE expense SET exp_exported_flag = 'Y'
                    WHERE exp_expenseno = ?";
                $db->preparedQuery($queryString, [["type" => "i", "value" => $toIgnoreExpense->expenseId]]);
            }
        }
        if (!count($engineersData)) {
            return false;
        }
        /**
         * @var string $engineerName
         * @var ExpenseOvertimeEngineerExport $engineersDatum
         */
        foreach ($engineersData as $engineerName => $engineersDatum) {
            if ($runType == 'Export') {
                $this->sendEngineerOvertimeExpenseSummaryEmail($engineersDatum);
                // we have to send the individual emails with the expenses and overtime data
                // we have to flag all the expenses and overtime as exported
                $queryString = "update headert set expensesNextProcessingDate = ?";
                $db->preparedQuery(
                    $queryString,
                    [["type" => "s", "value" => $nextProcessingDate->format(DATE_MYSQL_DATE)]]
                );
                foreach ($engineersDatum->expenses as $expenseExportItem) {
                    // update exported flag
                    $queryString = "UPDATE expense SET exp_exported_flag = 'Y'
                    WHERE exp_expenseno = ? ";
                    $db->preparedQuery($queryString, [["type" => "i", "value" => $expenseExportItem->expenseId]]);
                }
                /** @var OvertimeExportItem $overtimeActivity */
                foreach ($engineersDatum->overtimeActivities as $overtimeActivity) {
                    $queryString = "UPDATE callactivity SET caa_ot_exp_flag = 'Y'
                    WHERE caa_callactivityno = ?";
                    $db->preparedQuery($queryString, [["type" => "i", "value" => $overtimeActivity->activityId]]);
                }
            }
            if ($engineersDatum->expenseGrossTotal) {
                // from the total for each engineer we have to generate the expenses journal data
                $expenseJournalCSVData[] = [
                    'JC',
                    '',
                    85100,
                    0,
                    $date->format('t/m/Y'),
                    'Expenses',
                    $engineerName,
                    number_format($engineersDatum->expenseGrossTotal, 2),
                    'T9',
                    0
                ];
                $expenseJournalCSVData[] = [
                    'JD',
                    '',
                    32100,
                    0,
                    $date->format('t/m/Y'),
                    'Expenses',
                    $engineerName,
                    number_format($engineersDatum->expenseNetTotal, 2),
                    'T0',
                    0
                ];
            }
            if ($engineersDatum->expenseVATTotal) {
                $expenseJournalCSVData[] = [
                    'JD',
                    '',
                    84000,
                    0,
                    $date->format('t/m/Y'),
                    'Expenses',
                    $engineerName,
                    number_format($engineersDatum->expenseVATTotal, 2),
                    'T1',
                    0
                ];
            }
            $summaryReportCSVData[] = [
                $engineersDatum->employeeNumber,
                $engineersDatum->firstName,
                $engineersDatum->lastName,
                $engineersDatum->summaryGrossTotal,
                $engineersDatum->overtimeTotal,
                $engineersDatum->payeTotal,
            ];
        }
        if (count($summaryReportCSVData)) {
            usort(
                $summaryReportCSVData,
                function ($a, $b) {
                    return $a[0] <=> $b[0];
                }
            );
            $summaryCSV       = array_merge(
                [
                    ["Staff No.", "Forename", "Surname", "Expenses", "Overtime(Hours)", "Bonus"]
                ],
                $summaryReportCSVData
            );
            $summaryCSVString = $this->array2csv($summaryCSV);
            $journalCSVString = null;
            if (count($expenseJournalCSVData)) {
                $journalCSVString = $this->array2csv($expenseJournalCSVData);
            }
            $this->sendResultToEmail($dbeUser, $summaryCSVString, $journalCSVString);
        }
        return true;
    }

    /**
     * @param $date
     * @return ExpenseExportItem[]
     */
    private function getExpenseToDateExportData(DateTimeInterface $date)
    {
        /** @var dbSweetcode $db */ global $db;
        $queryString = "
        SELECT
  DATE_FORMAT(
    callactivity.caa_date,
    '%e/%c/%Y'
  ) AS activityDate,
  callactivity.caa_callactivityno as activityId,
  customer.cus_name as customerName,
  expense.exp_expenseno as expenseId,
  expense.exp_mileage as mileage,
  expensetype.ext_desc as description,
  expense.exp_value as grossValue,
  expense.exp_vat_flag = 'Y' as VATIncluded,
  consultant.cns_name as engineerName,
  consultant.cns_logname as engineerUserName,
               expensetype.taxable as payeTaxable,
  `cns_employee_no` as employeeNumber,
               consultant.firstName as engineerFirstName,
               consultant.lastName as engineerLastName,
               IF(
    expense.exp_vat_flag = 'Y',
    expense.exp_value - (
      expense.`exp_value` / (1 + getCurrentVatRate ())
    ),
    0
  ) AS VATValue,
  IF(
    expense.`exp_vat_flag` = 'Y',
    (
      expense.`exp_value` / (1 + getCurrentVatRate ())
    ),
    expense.`exp_value`
  ) AS netValue,
        expense.approvedBy IS NOT NULL as isApproved,
        expense.deniedReason is not null as isDenied
FROM
  expense
  INNER JOIN callactivity
    ON exp_callactivityno = caa_callactivityno
  JOIN problem
    ON pro_problemno = caa_problemno
  INNER JOIN customer
    ON problem.pro_custno = customer.cus_custno
  INNER JOIN consultant
    ON callactivity.caa_consno = consultant.cns_consno
  INNER JOIN expensetype
    ON expense.exp_expensetypeno = expensetype.ext_expensetypeno
WHERE expense.exp_exported_flag <> 'Y'
  AND callactivity.caa_date <= ?
  AND callactivity.caa_status IN ('C', 'A')
  and (expense.approvedBy is not null or expense.deniedReason is not null)
ORDER BY cns_name,
  caa_date,
  caa_starttime";
        $result      = $db->preparedQuery($queryString, [["type" => 's', "value" => $date->format(DATE_MYSQL_DATE)]]);
        $toReturn    = [];
        while ($object = $result->fetch_object(ExpenseExportItem::class)) {
            $toReturn[] = $object;
        }
        return $toReturn;
    }

    /**
     * @param DateTime $date
     * @return OvertimeExportItem[]
     */
    private function getOvertimeToDateExportData(DateTime $date)
    {
        /** @var dbSweetcode $db */ global $db;
        $queryString = "
    SELECT cus_name                                                       as customerName,
       DATE_FORMAT(
               callactivity.caa_date,
               '%e/%c/%Y'
           )                                                          AS activityDate,
       caa_starttime                                                  as activityStartTime,
       caa_endtime                                                    as activityEndTime,
       caa_callactivityno                                             as activityId,
       cns_name                                                       as engineerName,
       cns_logname                                                    as engineerUserName,
       consultant.firstName                                           as engineerFirstName,
       consultant.lastName                                            as engineerLastName,
       `cns_employee_no`                                              as employeeNumber,
       DATE_FORMAT(caa_date, '%w') IN (0, 6)                          as weekendOvertime,
       overtimeDurationApproved                                       AS overtimeValue,
       overtimeDurationApproved * 60 < minimumOvertimeMinutesRequired AS belowThreshold,
       overtimeDeniedReason is not null                               as isDenied,
       overtimeApprovedBy is not null                                 as isApproved
FROM callactivity
         JOIN problem ON pro_problemno = caa_problemno
         JOIN callacttype ON caa_callacttypeno = cat_callacttypeno
         JOIN customer ON pro_custno = cus_custno
         JOIN consultant ON caa_consno = cns_consno
         left join headert on (headerID = 1)
WHERE caa_date <= ?
  AND caa_date >= '2008-01-15'
  and submitAsOvertime
  and (overtimeApprovedBy is not null or overtimeDeniedReason is not null)
  AND (caa_status = 'C' OR caa_status = 'A')
  AND caa_ot_exp_flag = 'N'
  AND caa_endtime <> caa_starttime
  AND callacttype.engineerOvertimeFlag = 'Y'
ORDER BY cns_name, caa_date";
        $result      = $db->preparedQuery($queryString, [["type" => 's', "value" => $date->format(DATE_MYSQL_DATE)]]);
        $toReturn    = [];
        while ($object = $result->fetch_object(OvertimeExportItem::class)) {
            $toReturn[] = $object;
        }
        return $toReturn;
    }

    private function sendEngineerOvertimeExpenseSummaryEmail(ExpenseOvertimeEngineerExport $engineersDatum)
    {
        /** @var Environment $twig */ global $twig;
        $body      = $twig->render(
            '@internal/expensesOvertimeIndividualEmail.html.twig',
            [
                "data" => $engineersDatum,
            ]
        );
        $buMail    = new BUMail($this);
        $fromEmail = CONFIG_SALES_EMAIL;
        $toEmail   = $engineersDatum->userName . '@' . CONFIG_PUBLIC_DOMAIN;
        $subject   = "Overtime/Expenses for " . $engineersDatum->monthYear;
        $hdrs      = array(
            'From'    => $fromEmail,
            'To'      => $toEmail,
            'Subject' => $subject
        );
        $mime      = new Mail_mime();
        $mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $mime->get($mime_params);
        $hdrs        = $mime->headers($hdrs);
        $buMail->putInQueue(
            $fromEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /*
    * Export engineer overtime to file
    *
    * $runType controls whether this is a trial only. If runType = CTEXPENSE_ACT_EXPORT_GENERATE then
    * we send out individual emails to each engineer and update the exported flag.
    *
    * if $runType = CTEXPENSE_ACT_EXPORT_TRIAL then we only send the summary email and don't update exported flags 
    */
    private function array2csv($data, $delimiter = ",", $enclosure = '"', $escape_char = "\\")
    {
        $buffer = fopen('php://temp', 'r+');
        foreach ($data as $datum) {
            fputcsv($buffer, $datum, $delimiter, $enclosure, $escape_char);
        }
        rewind($buffer);
        $csv = "";
        while (!feof($buffer)) {
            $csv .= fgets($buffer);
        }
        fclose($buffer);
        return $csv;
    }

    /**
     * @param DBEUser $fromUser
     * @param $summaryCSVString
     * @param null $journalCSVString
     */
    function sendResultToEmail($fromUser, $summaryCSVString, $journalCSVString = null)
    {
        $buMail    = new BUMail($this);
        $toEmail   = "payroll@" . CONFIG_PUBLIC_DOMAIN;
        $fromEmail = $fromUser->getValue(DBEUser::username) . "@" . CONFIG_PUBLIC_DOMAIN;
        $hdrs      = array(
            'From'    => $fromEmail,
            'To'      => $toEmail,
            'Subject' => 'Expenses/Overtime Export'
        );
        $crlf      = "\r\n";
        $mime      = new Mail_mime($crlf);
        $mime->setTXTBody('Please find attached the expenses and overtime data.');
        $mime->addAttachment($summaryCSVString, 'application/octet-stream', 'Monthly Summary Report.csv', false);
        if ($journalCSVString) {
            $mime->addAttachment($journalCSVString, 'application/octet-stream', 'Expense-Journal.csv', false);
        }
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $mime->get($mime_params);
        $hdrs        = $mime->headers($hdrs);
        $buMail->putInQueue(
            $fromEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param $activityId
     * @return float|int The overtime calculated in decimal hours
     */
    function calculateOvertime($activityId)
    {
        $dbejCallactivity = new DBEJCallActivity($this);
        $dbejCallactivity->getRow($activityId);
        $dsHeader = new DataSet($this);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $officeStartTime = common_convertHHMMToDecimal($dsHeader->getValue(DBEHeader::overtimeStartTime));
        $officeEndTime   = common_convertHHMMToDecimal($dsHeader->getValue(DBEHeader::overtimeEndTime));
        $shiftStartTime  = common_convertHHMMToDecimal($dbejCallactivity->getValue(DBEJCallActivity::startTime));
        $shiftEndTime    = common_convertHHMMToDecimal($dbejCallactivity->getValue(DBEJCallActivity::endTime));
        $affectedUser    = new DBEUser($this);
        $affectedUser->getRow($dbejCallactivity->getValue(DBEJCallActivity::userID));
        $activityType = new DBECallActType($this);
        $activityType->getRow($dbejCallactivity->getValue(DBEJCallActivity::callActTypeID));
        if ($dbejCallactivity->getValue(DBECallActivity::overtimeDurationApproved)) {
            return $dbejCallactivity->getValue(DBECallActivity::overtimeDurationApproved);
        }
        if (!$activityType->getValue(DBECallActType::engineerOvertimeFlag) == 'Y' || !$dbejCallactivity->getValue(
                DBECallActivity::submitAsOvertime
            )) {
            return 0;
        }
        if ($activityType->getValue(DBECallActType::callActTypeID) != CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID) {
            return $shiftEndTime - $shiftStartTime;
        }
        $activityDateString = $dbejCallactivity->getValue(DBEJCallActivity::date);
        $activityDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $activityDateString);
        $dayOfTheWeek       = $activityDate->format('N');
        $bankHolidays       = common_getUKBankHolidays($activityDate->format('Y'));
        if (in_array($dayOfTheWeek, [6, 7]) || in_array($activityDateString, $bankHolidays)) {
            return $shiftEndTime - $shiftStartTime;
        }
        if ($shiftStartTime >= $officeStartTime && $shiftStartTime < $officeEndTime) {
            $shiftStartTime = $officeEndTime;
        }
        if ($shiftEndTime > $officeStartTime && $shiftEndTime < $officeEndTime) {
            $shiftEndTime = $officeStartTime;
        }
        if ($officeEndTime < $officeStartTime) {

            return 0;
        }
        return $shiftEndTime - $shiftStartTime;
    }

    public function getTotalExpensesForSalesOrder($salesOrderID)
    {
        return $this->dbeJExpense->getTotalExpensesForSalesOrder($salesOrderID);
    }

}