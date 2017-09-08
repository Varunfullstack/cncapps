<?php /**
 * Expense business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");
require_once($cfg["path_dbe"] . "/DBEVat.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpenseType.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg["path_func"] . "/activity.inc.php");

class BUExpense extends Business
{
    public $dbeJExpense;

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJExpense = new DBEJExpense($this);
    }

    function getExpenseByID($expenseID, &$dsResults)
    {
        $this->dbeJExpense->setPKValue($expenseID);
        $this->dbeJExpense->getRow();
        return ($this->getData($this->dbeJExpense, $dsResults));
    }

    function getExpensesByCallActivityID($callActivityID, &$dsResults)
    {
        $this->dbeJExpense->getRowsByCallActivityID($callActivityID);
        return ($this->getData($this->dbeJExpense, $dsResults));
    }

    function createExpenseFromCallActivityID($callActivityID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRow(2);                                        // default to mileage

        $dbeExpense = new DBEExpense($this);
        $dbeExpense->setValue('expenseID', 0);
        $dbeExpense->setValue('callActivityID', $callActivityID);
        $dbeExpense->setValue('expenseTypeID', 2);
        $dbeExpense->setValue('mileage', 0);
        $dbeExpense->setValue('exportedFlag', 'N');
        $dbeExpense->setValue('value', 0);
        $dbeExpense->setValue('vatFlag', $dbeExpenseType->getValue('vatFlag'));            // default for this expense type
        $dbeExpense->insertRow();

        $expenseID = $dbeExpense->getPKValue();

        return ($expenseID);
    }

    /**
     *    canDeleteExpense
     * Only allowed if not exported
     */
    function canDeleteExpense($expenseID)
    {
        $this->setMethodName('deleteExpense');
        // get the call activity no to return
        $dbeExpense = new DBEExpense($this);
        $dbeExpense->getRow($expenseID);

        if ($dbeExpense->getValue('exportedFlag') == 'Y') {
            return false;
        } else {
            return true;
        }

    }

    /**
     * deleteExpense
     *
     * Deletes an expense row and returns the calllactivityNo
     *
     */
    function deleteExpense($expenseID)
    {
        $this->setMethodName('deleteExpense');
        // get the call activity no to return
        $dbeJExpense = new DBEJExpense($this);
        $dbeJExpense->getRow($expenseID);
        $callActivityID = $dbeJExpense->getValue('callActivityID');
        // Delete the expense row
        $dbeExpense = new DBEExpense($this);
        $dbeExpense->setPKValue($expenseID);
        $dbeExpense->deleteRow();
        return ($callActivityID);
    }

    function updateExpense(&$dsExpense)
    {
        $this->setMethodName('updateExpense');
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRow($dsExpense->getValue('expenseTypeID'));
        // if mileage then calculate mileage value from employee milage rate * mileage and set value to that
        if ($dbeExpenseType->getValue('mileageFlag') == 'Y') {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($dsExpense->getValue('userID'));
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue('value', $dbeUser->getValue('petrolRate') * $dsExpense->getValue('mileage'));
            $dsExpense->post();
        } else {
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue('mileage', '');
            $dsExpense->post();
        }
        if ($dsExpense->getValue('vatFlag') != 'Y') {
            $dsExpense->setUpdateModeUpdate();
            $dsExpense->setValue('vatFlag', 'N');
            $dsExpense->post();
        }
        $dbeExpense = new DBEExpense($this);
        $this->updateDataaccessObject($dsExpense, $dbeExpense);
        return TRUE;
    }

    /**
     * initialise values for input of date range
     * @return DataSet &$dsData results
     * @access public
     */
    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn('endDate', DA_DATE, DA_ALLOW_NULL);
    }

    /**
     * Export engineer expenses to file
     */
    function exportEngineerExpenses(&$dsData, $runType)
    {
        GLOBAL $db;

        $this->setMethodName('exportEngineerExpenses');

        if ($runType == 'Export') {
            $dbUpdate = new dbSweetcode;                        // database connection for update query
        }


        // get VAT rate
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $stdVatCode = $dsHeader->getValue('stdVATCode');

        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$stdVatCode[1]);

        /*
        start writing to summary file
        */
        $summaryFileName =
            SAGE_EXPORT_DIR .
            '/EXPENSE-SUMMARY-' . $dsData->getValue('endDate') . '.csv';

        $summaryFileHandle = fopen($summaryFileName, 'wb');

        $queryString = "
        SELECT
        DATE_FORMAT(callactivity.caa_date, '%e/%c/%Y') AS activityDate,
        callactivity.caa_callactivityno,
        customer.cus_name,
        expense.exp_expenseno,
        expense.exp_mileage,
        expensetype.ext_desc,
        expense.exp_value,
        expense.exp_vat_flag,
        consultant.cns_name,
        consultant.cns_logname,
        callactivity.caa_status
        FROM
        expense
        INNER JOIN callactivity ON exp_callactivityno = caa_callactivityno
        JOIN problem ON pro_problemno = caa_problemno
        INNER JOIN customer ON problem.pro_custno = customer.cus_custno
        INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno
        INNER JOIN expensetype ON expense.exp_expensetypeno = expensetype.ext_expensetypeno
        WHERE
        expense.exp_exported_flag <> 'Y'
        AND callactivity.caa_date <= '" . $dsData->getValue('endDate') . "'" .
            " AND callactivity.caa_status IN ('C','A')
        ORDER BY cns_name, caa_date, caa_starttime";

        $db->query($queryString);

        $month_year = substr($dsData->getValue('endDate'), 5, 2) . '/' . substr($dsData->getValue('endDate'), 0, 4);

        if ($db->next_record()) {
            $lastEngineer = 'FIRST';
            do {

                if ($runType == 'Export') {

                    // if this is a new engineer:
                    if ($db->Record['cns_name'] != $lastEngineer) {
                        if ($fileHandle) {
                            fclose($fileHandle);                                                                // close the last engineer file if open

                            $this->sendExpensesEmail(
                                $email_to,
                                $email_body,
                                $grandNetValue,
                                $grandVatValue,
                                $grandValue
                            );

                            $email_body = '';

                        }
                        /*
                        start writing to new engineer file
                        */
                        $fileName =
                            SAGE_EXPORT_DIR .
                            '/EXP-' . str_replace(' ', '', $db->Record['cns_name']) .
                            $dsData->getValue('endDate') . '.csv';
                        $fileHandle = fopen($fileName, 'wb');
                        if (!$fileHandle) {
                            $this->raiseError("Unable to open file " . $fileName);
                        }
                        /*
                        start new email
                        */
                        if ($GLOBALS['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
                            $email_to = CONFIG_SALES_MANAGER_EMAIL;
                        } else {
                            $email_to = $db->Record['cns_logname'] . '@cnc-ltd.co.uk';
                        }

                        $grandNetValue = 0;
                        $grandVatValue = 0;
                        $grandValue = 0;

                        $email_body =
                            '<HTML style="font:Arial, Helvetica, sans-serif">
                        <P><strong>Expenses for ' . $month_year . ' </strong></P>
                        <TABLE>
                        <TR>
                        <TD>
                        <strong>Date</strong>
                        </TD>
                        <TD>
                        <strong>Customer</strong>
                        </TD>
                        <TD>
                        <strong>Activity</strong>
                        </TD>
                        <TD align="right">
                        <strong>Miles</strong>
                        </TD>
                        <TD>
                        <strong>Type</strong>
                        </TD>
                        <TD align="right">
                        <strong>Net</strong>
                        </TD>
                        <TD align="right">
                        <strong>VAT</strong>
                        </TD>
                        <TD align="right">
                        <strong>Total</strong>
                        </TD>
                        </TR>
                        <TR>
                        <TD colspan="8">&nbsp;</TD>
                        </TR>';

                    } // end if ( $db->Record['cns_name'] != $lastEngineer )			


                    $lastEngineer = $db->Record['cns_name'];

                } // end if ( $runType == 'Export' ) {

                if ($db->Record['exp_vat_flag'] == 'Y') {
                    $vatValue = $db->Record['exp_value'] * ($vatRate / 100);
                    $totalValue = $db->Record['exp_value'];
                    $netValue = $db->Record['exp_value'] - $vatValue;
                } else {
                    $netValue = $db->Record['exp_value'];
                    $vatValue = 0;
                    $totalValue = $netValue;
                }

                $file_line =
                    "\"" . $db->Record['activityDate'] . "\"," .
                    "\"" . addslashes($db->Record['cus_name']) . "/" . $db->Record['caa_callactivityno'] . "\"," .
                    "\"" . $db->Record['exp_mileage'] . "\"," .
                    "\"" . $db->Record['ext_desc'] . "\"," .
                    "\"" . Controller::formatNumber($netValue) . "\"," .
                    "\"" . Controller::formatNumber($vatValue) . "\"," .
                    "\"" . Controller::formatNumber($totalValue) . "\"" .
                    "\r\n";

                if ($runType == 'Export') {
                    fwrite(
                        $fileHandle,
                        $file_line
                    );
                }

                fwrite(
                    $summaryFileHandle,
                    "\"" . $db->Record['cns_name'] . "\"," .
                    $file_line
                );

                $grandNetValue += $netValue;
                $grandVatValue += $vatValue;
                $grandValue += $totalValue;

                $email_body .=
                    '<TR>
                <TD>' . $db->Record['activityDate'] . '</TD>
                <TD>' . $db->Record['cus_name'] . '</TD>
                <TD>' . $db->Record['caa_callactivityno'] . '</TD>
                <TD align="right">' . $db->Record['exp_mileage'] . '</TD>
                <TD>' . $db->Record['ext_desc'] . '</TD>
                <TD align="right">' . Controller::formatNumber($netValue) . '</TD>
                <TD align="right">' . Controller::formatNumber($vatValue) . '</TD>
                <TD align="right">' . Controller::formatNumber($totalValue) . '</TD>
                </TR>';

                if ($runType == 'Export') {
                    // update exported flag
                    $queryString =
                        "UPDATE expense SET exp_exported_flag = 'Y'
                    WHERE exp_expenseno = " . $db->Record['exp_expenseno'];

                    $dbUpdate->query($queryString);
                }

            } while ($db->next_record());

            fclose($fileHandle);

            fclose($summaryFileHandle);

            if ($runType == 'Export') {

                $this->sendExpensesEmail(
                    $email_to,
                    $email_body,
                    $grandNetValue,
                    $grandVatValue,
                    $grandValue
                );

            }

            $this->sendSummaryEmail($summaryFileName, 'Expenses summary file attached');

            //			$db->query($queryString);

            return TRUE;
        } // end if ( $db->next_record() )
        else {
            return FALSE;
        }
    }

    function sendExpensesEmail(
        $email_to,
        $email_body,
        $grandNetValue,
        $grandVatValue,
        $grandValue
    )
    {
        require_once("Mail.php");

        $mail = Mail::factory('smtp', $GLOBALS['mail_options']);

        if ($GLOBALS['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
            $email_to = CONFIG_SALES_MANAGER_EMAIL;
        }

        $email_body .=
            '<TR>
        <TD colspan="8">&nbsp;</TD>
        </TR>
        <TR>
        <TD colspan="4">
        &nbsp;
        </TD>
        <TD>
        <strong>Totals</strong>
        </TD>
        <TD align="right">
        <strong>' . Controller::formatNumber($grandNetValue) . '</strong>
        </TD>
        <TD align="right">
        <strong>' . Controller::formatNumber($grandVatValue) . '</strong>
        </TD>
        <TD align="right">
        <strong>' . Controller::formatNumber($grandValue) . '</strong>
        </TD>
        </TR>
        </TABLE>
        </HTML>';

        $hdrs = array(
            'From' => 'grahaml@cnc-ltd.co.uk',
            'To' => $email_to,
            'Subject' => 'Your Expenses'
        );

        $crlf = "\r\n";
        $mime = new Mail_mime($crlf);
        $mime->setHTMLBody($email_body);

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $result = $mail->send($email_to, $hdrs, $body);
    }

    /*
    * Export engineer overtime to file
    *
    * $runType controls whether this is a trial only. If runType = CTEXPENSE_ACT_EXPORT_GENERATE then
    * we send out individual emails to each engineer and update the exported flag.
    *
    * if $runType = CTEXPENSE_ACT_EXPORT_TRIAL then we only send the summary email and don't update exported flags 
    */
    function exportEngineerOvertime(&$dsData, $runType)
    {

        GLOBAL $db;
        $this->setMethodName('exportEngineerOvertime');

        if ($runType == 'Export') {
            $dbUpdate = new dbSweetcode;                        // database connection for update query
        }

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        /*
        start writing to summary file
        */
        $summaryFileName =
            SAGE_EXPORT_DIR .
            '/OVERTIME-SUMMARY-' . $dsData->getValue('endDate') . '.csv';

        $summaryFileHandle = fopen($summaryFileName, 'wb');
        /*
        get all activity rows that have not been exported and are at the weekend or before/after
        hours.
        */

        //   $otAdjustHourHHMM = common_convertDecimalToHHMM( $dsHeader->getValue('otAdjustHour') );

        $queryString = "
    SELECT 
    UNIX_TIMESTAMP(caa_date) AS 'date_ts',
    DATE_FORMAT(caa_date, '%w') AS 'weekday',
    cus_name,
    caa_callactivityno,
    caa_starttime,
    caa_endtime,
    cns_name,
    cns_logname,
    cns_helpdesk_flag
    FROM callactivity
    JOIN problem ON pro_problemno = caa_problemno
    JOIN callacttype ON caa_callacttypeno = cat_callacttypeno
    JOIN customer ON pro_custno = cus_custno
    JOIN consultant ON caa_consno = cns_consno
    WHERE caa_date <= '" . $dsData->getValue('endDate') . "'" .
            " AND caa_date >= '2008-01-15'" .
            " AND (caa_status = 'C' OR caa_status = 'A' )
    AND caa_ot_exp_flag = 'N'
    AND ( 
            ( weekdayOvertimeFlag = 'Y' AND DATE_FORMAT(caa_date, '%w') IN (0,1,2,3,4,5,6) )
            OR
            ( weekdayOvertimeFlag = 'N' AND DATE_FORMAT(caa_date, '%w') IN (0,6) )
    )
    AND (
    (caa_endtime > '" . $dsHeader->getValue('projectEndTime') . "' OR TIME(caa_starttime) < '" . $dsHeader->getValue('projectStartTime') . "' OR DATE_FORMAT(caa_date, '%w')IN(0,6))
    OR (caa_endtime > '" . $dsHeader->getValue('helpdeskEndTime') . "' OR TIME(caa_starttime) < '" . $dsHeader->getValue('helpdeskStartTime') . "' OR DATE_FORMAT(caa_date, '%w') IN(0,6) )
    )
    AND( caa_endtime <> caa_starttime )
    AND callacttype.engineerOvertimeFlag = 'Y'
    ORDER BY cns_name, caa_date";

        /*
        use the system overtime to get staff overtime limits. This is used to
        make the overtime start time earlier than the office start time
        */
        $projectStartTime = common_convertHHMMToDecimal($dsHeader->getValue('projectStartTime'));
        $projectEndTime = common_convertHHMMToDecimal($dsHeader->getValue('projectEndTime'));
        $helpdeskStartTime = common_convertHHMMToDecimal($dsHeader->getValue('helpdeskStartTime'));
        $helpdeskEndTime = common_convertHHMMToDecimal($dsHeader->getValue('helpdeskEndTime'));


        $db->query($queryString);

        $month_year = substr($dsData->getValue('endDate'), 5, 2) . '/' . substr($dsData->getValue('endDate'), 0, 4);

        if ($db->next_record()) {

            $lastEngineer = 'FIRST';

            do {

                if ($runType == 'Export') {

                    // if this is a new engineer:
                    if ($db->Record['cns_name'] != $lastEngineer) {

                        if ($fileHandle) {
                            fclose($fileHandle);                                                                // close the last file if open
                            $this->sendOvertimeEmail(
                                $email_to,
                                $email_body,
                                $grandOvertime
                            );

                            $email_body = '';

                        }
                        // start writing to new overtime file
                        $fileName =
                            SAGE_EXPORT_DIR .
                            '/OT-' . str_replace(' ', '', $db->Record['cns_name']) .
                            $dsData->getValue('endDate') . '.csv';
                        $fileHandle = fopen($fileName, 'wb');
                        if (!$fileHandle) {
                            $this->raiseError("Unable to open file " . $fileName);
                        }

                        // start new email
                        $grandOvertime = 0;

                        if ($GLOBALS['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
                            $email_to = CONFIG_SALES_MANAGER_EMAIL;
                        } else {
                            $email_to = $db->Record['cns_logname'] . '@cnc-ltd.co.uk';
                        }

                        $email_body =
                            '<P><strong>Overtime for ' . $month_year . ' </strong></P>
                <TABLE>
                <TR>
                <TD>
                <strong>Date</strong>
                </TD>
                <TD>
                <strong>Start</strong>
                </TD>
                <TD>
                <strong>End</strong>
                </TD>
                <TD>
                <strong>Customer</strong>
                </TD>
                <TD>
                <strong>Activity</strong>
                </TD>
                <TD align="right">
                <strong>Hours</strong>
                </TD>
                </TR>
                <TR>
                <TD colspan="4">&nbsp;</TD>
                </TR>';


                    } // end if ( $db->Record['cns_name'] != $lastEngineer )
                    $lastEngineer = $db->Record['cns_name'];

                }// end if ( $runType == 'Export' )

                $startTime = common_convertHHMMToDecimal($db->Record['caa_starttime']);
                $endTime = common_convertHHMMToDecimal($db->Record['caa_endtime']);

                /*
                if this is a weekend day then the whole lot is overtime else work out how many hours
                are out of office hours
                */
                if ($db->Record['weekday'] == 0 OR $db->Record['weekday'] == 6) {
                    $overtime = $endTime - $startTime;
                } else {
                    /*
                    If this is a helpdesk staff then evening overtime is only allowed on activities that start after office end time
                    */
                    // overtime is hours before and after this engineer's office hours
                    if ($db->Record['cns_helpdesk_flag'] == 'Y') {
                        $officeStartTime = $helpdeskStartTime;
                        $officeEndTime = $helpdeskEndTime;
                        $overtime = 0;
                        if ($startTime < $officeStartTime) {
                            if ($endTime < $officeStartTime) {
                                $overtime = $endTime - $startTime;
                            } else {
                                $overtime = $officeStartTime - $startTime;
                            }
                        }
                        if ($endTime > $officeEndTime) {
                            if ($startTime >= $officeEndTime) {
                                $overtime += $endTime - $startTime;
                            }
                        }
                    } else {
                        /*
                        non-helpdesk engineers get any time spent after office end hours irrespective of start time
                        */
                        $officeStartTime = $projectStartTime;
                        $officeEndTime = $projectEndTime;
                        $overtime = 0;
                        if ($startTime < $officeStartTime) {
                            if ($endTime < $officeStartTime) {
                                $overtime = $endTime - $startTime;
                            } else {
                                $overtime = $officeStartTime - $startTime;
                            }
                        }
                        if ($endTime > $officeEndTime) {
                            if ($startTime > $officeEndTime) {
                                $overtime += $endTime - $startTime;
                            } else {
                                $overtime += $endTime - $officeEndTime;
                            }
                        }
                    }
                }

                if ($overtime) {

                    $file_line =
                        "\"" . date('d/m/Y', $db->Record['date_ts']) . "\"," .
                        "\"" . $db->Record['caa_starttime'] . "\"," .
                        "\"" . $db->Record['caa_endtime'] . "\"," .
                        "\"" . addslashes($db->Record['cus_name']) . "/" . $db->Record['caa_callactivityno'] . "\"," .
                        "\"" . Controller::formatNumber($overtime, 2, '', false) . "\"" .
                        "\r\n";

                    if ($runType == 'Export') {

                        fwrite(
                            $fileHandle,
                            $file_line
                        );

                    }

                    fwrite(
                        $summaryFileHandle,
                        "\"" . $db->Record['cns_name'] . "\"," .
                        $file_line
                    );

                    $grandOvertime += $overtime;

                    $email_body .=
                        '<TR>
                <TD>' . date('d/m/Y', $db->Record['date_ts']) . '</TD>
                <TD>' . $db->Record['caa_starttime'] . '</TD>
                <TD>' . $db->Record['caa_endtime'] . '</TD>
                <TD>' . $db->Record['cus_name'] . '</TD>
                <TD>' . $db->Record['caa_callactivityno'] . '</TD>
                <TD align="right">' . Controller::formatNumber($overtime) . '</TD>
                </TR>';

                    if ($runType == 'Export') {

                        // update exported flag
                        $queryString =
                            "UPDATE callactivity SET caa_ot_exp_flag = 'Y'
                    WHERE caa_callactivityno = " . $db->Record['caa_callactivityno'];

                        $dbUpdate->query($queryString);

                    }

                }


            } while ($db->next_record());

            if ($runType == 'Export') {

                fclose($fileHandle);

                $this->sendOvertimeEmail(
                    $email_to,
                    $email_body,
                    $grandOvertime
                );

            }

            fclose($summaryFileHandle);

            $this->sendSummaryEmail($summaryFileName, 'Overtime summary file attached');

            return TRUE;
        } // end if ( $db->next_record() )
        else {

            return FALSE;
        }
    }

    function sendOvertimeEmail(
        $email_to,
        $email_body,
        $grandOvertime
    )
    {
        require_once("Mail.php");

        $mail = Mail::factory('smtp', $GLOBALS['mail_options']);

        if ($GLOBALS['server_type'] != MAIN_CONFIG_SERVER_TYPE_LIVE) {
            $email_to = CONFIG_SALES_MANAGER_EMAIL;
        }

        $email_body .=
            '<TR>
        <TD colspan="4">&nbsp;</TD>
        </TR>
        <TR>
        <TD colspan="2">
        &nbsp;
        </TD>
        <TD>
        <strong>Total</strong>
        </TD>
        <TD align="right">
        <strong>' . Controller::formatNumber($grandOvertime) . '</strong>
        </TD>
        </TR>
        </TABLE>
        </HTML>';

        $hdrs = array(
            'From' => 'grahaml@cnc-ltd.co.uk',
            'To' => $email_to,
            'Subject' => 'Your Overtime'
        );


        $crlf = "\r\n";
        $mime = new Mail_mime($crlf);
        $mime->setHTMLBody($email_body);

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        $result = $mail->send($email_to, $hdrs, $body);
    }

    function sendSummaryEmail(
        $filename,
        $email_body
    )
    {
        require_once("Mail.php");

        $mail = Mail::factory('smtp', $GLOBALS['mail_options']);

        $hdrs = array(
            'From' => 'grahaml@cnc-ltd.co.uk',
            'To' => CONFIG_SALES_MANAGER_EMAIL,
            'Subject' => $email_body
        );

        $crlf = "\r\n";

        $mime = new Mail_mime($crlf);

        $mime->setTXTBody($email_body);

        $mime->addAttachment($filename);

        $body = $mime->get();

        $hdrs = $mime->headers($hdrs);

        $result = $mail->send(CONFIG_SALES_MANAGER_EMAIL, $hdrs, $body);
    }

}// End of class
?>