<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg ["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBERootCause.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJProblem.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUProblemSLA extends Business
{

//    const SYSTEM_INSTALLED_DATE = '2010-03-24';
    const DAYS_UNTIL_COMPLETE = 3;
    const hour = 3600;          // hour in seconds
    const halfHour = 1800;      // half-hour in seconds
    const day = 43200;     // one day in seconds
    const quarterHour = 900;
    const twoMinutes = 120;
    const fiveMinutes = 300;
    const thirtySeconds = 30;
    const tenMinutes = 600;
    const workingHoursAlertLimit = 40;
    const special_attention_customer_alert_days = 3;

    private $dateFourWeekAgo = '';
    private $startSupportTime = '';
    private $endSupportTime = '';
    private $workingHoursInDay = 0;

    private $hoursCalculated = 0;

    private $awaitingCustomerResponseFlag = false;

    private $ukBankHolidays;
    private $buActivity;
    private $buCustomerItem;
    private $dbeProblem;
    private $dbeJProblem;
    private $dbeJCallActivityFix;
    private $dbeCallActivity;
    private $dbeJCallActivity;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        // build a list of UK Bank Holidays in over 3 years
        $lastYearBH = common_getUkBankHolidays(date('Y') - 1);
        $thisYearBH = common_getUkBankHolidays(date('Y'));
        $nextYearBH = common_getUkBankHolidays(date('Y') + 1);
        $this->ukBankHolidays = array_merge($lastYearBH, $thisYearBH, $nextYearBH);

        $this->buCustomerItem = new BUCustomerItem($this);

        $this->buActivity = new BUActivity ($this);


        $this->dateFourWeeksAgo = date('Y-m-d', strtotime(date('Y-m-d') . ' -4 WEEKS'));

        $buHeader = new BUHeader ($this);
        $buHeader->getHeader($dsHeader);

        $this->startSupportTime = $dsHeader->getValue('billingStartTime');

        $this->endSupportTime = $dsHeader->getValue('billingEndTime');

        $this->srAutocompleteThresholdHours = $dsHeader->getValue('srAutocompleteThresholdHours');

        $this->workingHoursInDay =
            common_convertHHMMToDecimal($this->endSupportTime) -
            common_convertHHMMToDecimal($this->startSupportTime);

        $this->dbeJCallActivityFix = new DBEJCallActivity($this);

        $this->dbeCallActivity = new DBECallActivity($this);
        $this->dbeProblem = new DBEProblem($this);

        $this->dbeJCallActivity = new DBEJCallActivity($this);
        $this->dbeJProblem = new DBEJProblem($this);
    }

    function monitor()
    {
        $this->buActivity->getProblemsByStatus('I', $dsResults); // initial status


        while ($dsResults->fetchNext()) {


            $this->dbeProblem->getRow($dsResults->getValue('problemID'));

            $workingHours = $this->getWorkingHours($dsResults->getValue('problemID'));


            $hoursToSLA = $dsResults->getValue('slaResponseHours') - $workingHours;

            /*
            Send an alert email to managers if within 20 minutes of SLA response hours and not priority 4 or 5
            */
            if (
                $hoursToSLA <= .3 &&                    // within one third of time to SLA

                $this->dbeProblem->getValue('sentSlaAlertFlag') == 'N' &&          // hasn't already been sent
                $this->dbeProblem->getValue('userID') != '' &&                     // is asssigned
                $this->dbeProblem->getValue('userID') != USER_SYSTEM &&
                $dsResults->getValue('priority') < 5

            ) {

                $this->sendSlaAlertEmail($dsResults->getValue('problemID'), $percentageSLA);

                $this->dbeProblem->setValue('sentSlaAlertFlag', 'Y');

            }

            $this->dbeProblem->setValue('awaitingCustomerResponseFlag', $this->awaitingCustomerResponseFlag);
            $this->dbeProblem->setValue('workingHours', $workingHours);

            if ($this->hoursCalculated) {
                $this->dbeProblem->setValue('workingHoursCalculatedToTime', date(CONFIG_MYSQL_DATETIME));
            }

            echo $this->dbeProblem->getValue('problemID') . ': ' . $workingHours . '<BR/>';


            $this->dbeProblem->updateRow();

        }

        echo "<H2>In Progress</H2>";

        $this->buActivity->getProblemsByStatus('P', $dsResults); // in progress status

        while ($dsResults->fetchNext()) {

            $responseHours = $this->buCustomerItem->getMinumumContractResponseHours($dsResults->getValue('customerID'));

            $workingHours = $this->getWorkingHours($dsResults->getValue('problemID'));

            $this->dbeProblem->getRow($dsResults->getValue('problemID'));

            $this->dbeProblem->setValue('workingHours', $workingHours);

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue('workingHoursCalculatedToTime', date(CONFIG_MYSQL_DATETIME));

            }

            $this->dbeProblem->setValue('awaitingCustomerResponseFlag', $this->awaitingCustomerResponseFlag);
            $this->dbeProblem->updateRow();

            echo $this->dbeProblem->getValue('problemID') . ': ' . $workingHours . '<BR/>';

        }

        $this->buActivity->getProblemsByStatus('P', $dsResults, true); // in progress future alarm date status

        while ($dsResults->fetchNext()) {

            $responseHours = $this->buCustomerItem->getMinumumContractResponseHours($dsResults->getValue('customerID'));

            $workingHours = $this->getWorkingHours($dsResults->getValue('problemID'));

            $this->dbeProblem->getRow($dsResults->getValue('problemID'));

            $this->dbeProblem->setValue('workingHours', $workingHours);

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue('workingHoursCalculatedToTime', date(CONFIG_MYSQL_DATETIME));

            }

            $this->dbeProblem->setValue('awaitingCustomerResponseFlag', $this->awaitingCustomerResponseFlag);
            $this->dbeProblem->updateRow();

            echo $this->dbeProblem->getValue('problemID') . ': ' . $workingHours . '<BR/>';

        }

    } // end function monitor

    function autoCompletion()
    {
        $dbeCustomer = new DBECustomer($this);

        $dbeCallActivity = new DBECallActivity($this);

        $this->buActivity->getProblemsByStatus('F', $dsResults, true); // fixed status

        while ($dsResults->fetchNext()) {

            $problemID = $dsResults->getValue('problemID');

            $dbeCallActivity = $this->buActivity->getLastActivityInProblem($problemID);

            if ($dbeCallActivity) {

                $this->dbeProblem->getRow($dsResults->getValue('problemID'));

                $hoursUntilComplete =
                    $this->getWorkingHoursBetweenUnixDates(

                        date('U'),
                        // from now
                        strtotime($this->dbeProblem->getValue('completeDate') . ' ' . $dbeCallActivity->getValue('endTime')),
                        // time on completion date
                        false                                                           // no pauses
                    );
                /*
                Autocomplete NON-T&M SRs that have activity duration of less than one hour and have reached their complete date
                */
                if (
                    $this->dbeProblem->getValue('contractCustomerItemID') != 0 &&

                    $hoursUntilComplete <= 0 &
                    $this->dbeProblem->getValue('totalActivityDurationHours') <= $this->srAutocompleteThresholdHours
                ) {

                    $this->buActivity->setProblemToCompleted($problemID);

                } else {
                    /*
                    if within 2 working days of complete date send an email up to maximum 2 emails.
                    */
                    if (
                        $hoursUntilComplete <= ($this->workingHoursInDay * 2) &&
                        $this->dbeProblem->getValue('completionAlertCount') < 2
                    ) {
                        $this->dbeProblem->setValue('completionAlertCount',
                                                    $this->dbeProblem->getValue('completionAlertCount') + 1);
                        $this->dbeProblem->updateRow();

                        $dbeCustomer->getRow($this->dbeProblem->getValue('customerID'));

                        if (
                            $this->dbeProblem->getValue('hideFromCustomerFlag') == 'N'
                        ) {

                            $this->sendCompletionAlertEmail(
                                $problemID,
                                $this->dbeProblem->getValue('completeDate')
                            );
                        } // end if hours until complete


                    }// end if last activity = true

                }

            } // end older than 4 weeks check

        }    // end while fetch next

    } // end function autoCompletion

    /**
     * Delete any fixed SRs that had no human intervention.
     *
     * Only this year
     *
     */
    function deleteNonHumanServiceRequests()
    {
        $SQL =
            "SELECT
          DISTINCT pro_problemno
        FROM
          problem
        WHERE
          (
            SELECT
              COUNT(*)
            FROM
              callactivity
            WHERE
              caa_problemno = pro_problemno
              AND caa_consno <> " . USER_SYSTEM .
            " AND caa_callacttypeno NOT IN( "
            . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . ',' .
            CONFIG_COMPLETED_ACTIVITY_TYPE_ID . ')' .
            " ) = 0
          AND pro_status IN ('F', 'C')
          AND pro_date_raised >= '2013-01-01'";

        $results = $this->db->query($SQL);

        $ids = array();

        while ($row = $results->fetch_object()) {
            $ids[] = $row->pro_problemno;
        }

        if (count($ids)) {

            $SQL =
                "DELETE FROM
            callactivity
          WHERE
            caa_problemno IN (" . implode(',', $ids) . ")";

            $this->db->query($SQL);

            $SQL =
                "DELETE FROM
            problem
          WHERE
            pro_problemno IN (" . implode(',', $ids) . ")";

            $this->db->query($SQL);
        }

        return (count($ids));

    }


    /**
     * Send an email to managers alerting them to impending end of special attention customer periods
     *
     */
    function specialAttentionEmailAlert()
    {
        /* get a list of special attention customers */
        $SQL = "
        SELECT
          cus_name,
          cus_custno,
          DATE_FORMAT(cus_special_attention_end_date, '%e/%c/%Y' )  AS cus_special_attention_end_date
        FROM
          customer
        WHERE
          cus_special_attention_flag = 'Y'
          AND DATEDIFF( cus_special_attention_end_date, DATE( NOW() ) ) <= " . self::special_attention_customer_alert_days .
            " AND DATE( NOW() ) < cus_special_attention_end_date ";

        $results = $this->db->query($SQL);

        while ($row = $results->fetch_object()) {
            $this->sendSpecialAttentionEmailAlert($row);
        }
    }

    function sendSpecialAttentionEmailAlert($customer)
    {
        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = 'specialattentionends@' . CONFIG_PUBLIC_DOMAIN;

        $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
        $template->set_file('page', 'SpecialAttentionAlertEmail.inc.html');

        $template->setVar(
            array(
                'customerName'            => $customer->cus_name,
                'specialAttentionEndDate' => $customer->cus_special_attention_end_date,
            )
        );

        $template->parse('output', 'page', true);

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'Subject'      => 'Special Attention Period Ends Soon For ' . $customer->cus_name,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body,
            true
        );
    }

    /**
     * Sends email to managers when request is near SLA
     *
     * @param mixed $callActivityID
     */
    function sendSlaAlertEmail(
        $problemID,
        $percentage
    )
    {
        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        if ($dbeJCallActivity = $this->buActivity->getFirstActivityInProblem($problemID)) {

            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $senderName = 'CNC Support Department';

            if ($dbeJProblem->getValue('engineerLogname')) {
                $toEmail = $dbeJProblem->getValue('engineerLogname') . '@' . CONFIG_PUBLIC_DOMAIN;
            } else {
                $toEmail = false;
            }

            if ($toEmail) {
                $toEmail .= ',';
            }
            $toEmail .= 'slabreachalert@' . CONFIG_PUBLIC_DOMAIN;

            $activityRef = $dbeJCallActivity->getValue('problemID');

            $buCustomer = new BUCustomer ($this);

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
            $template->set_file('page', 'SlaAlertEmail.inc.html');

            $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue();

            $template->setVar(
                array(
                    'urlActivity'                 => $urlActivity,
                    'customerName'                => $dbeJProblem->getValue('customerName'),
                    'activityRef'                 => $activityRef,
                    'reason'                      => $dbeJCallActivity->getValue('reason'),
                    'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC,
                    'percentage'                  => number_format($percentage, 0)
                )
            );

            $template->parse('output', 'page', true);

            $body = $template->get_var('output');

            $hdrs = array(
                'To'           => $toEmail,
                'From'         => $senderEmail,
                'Subject'      => 'WARNING - SR for ' . $dbeJProblem->getValue('customerName') . 'assigned to ' . $dbeJProblem->getValue('engineerName') . ' close to breaching SLA',
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body,
                true
            );

        } // end if ( $dbeJCallActivity = $this->buActivity->getFirstActivityInProblem( $problemID ) ){

    }

    /**
     * Send Service Completion Alert Email
     *
     * @param mixed $problemID
     * @param $completeDate
     */
    function sendCompletionAlertEmail($problemID, $completeDate)
    {
        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $dbeJCallActivity = $this->buActivity->getFirstActivityInProblem($problemID);

        $dbeJLastCallActivity = $this->buActivity->getLastActivityInProblem($problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $activityRef = $problemID;

        $dbeCustomer = new DBECustomer ($this);
        $dbeCustomer->getRow($dbeJProblem->getValue('customerID'));
        /*
        do we copy in main contact?
        */
        $copyEmailToMainContact = true;

        /*
        do we send to first activity contact?
        */
        if (
            $dbeJCallActivity->getValue('autoCloseEmailFlag') == 'N'
        ) {
            $sendEmailToFirstActivityContact = false;
        } else {
            $sendEmailToFirstActivityContact = true;
        }


        if ($sendEmailToFirstActivityContact) {
            $toEmail = $dbeJCallActivity->getValue('contactEmail');
            $toName = $dbeJCallActivity->getValue('contactName');
        }

        /*
        Send the email to all the main support email addresses at the client but exclude them if they were the reporting contact or don't want to get them.
        */

                $dbeContact = new DBEContact($this);

        $dbeContact->getMainSupportRowsByCustomerID($dbeJProblem->getValue('customerID'));

        while ($dbeContact->fetchNext()) {
            if ($dbeContact->getValue(DBEContact::othersEmailFlag) == 'Y' &&
                $dbeContact->getValue(DBEContact::othersAutoCloseEmailFlag)) {
                if ($toEmail) {
                    $toEmail .= ",";
                }

                $toEmail .= $dbeContact->getValue(DBEContact::Email);
            }
        }

        if (!$toEmail) {       // no recipients so no email
            return;
        }

        $fixedUserID = $dbeJProblem->getValue('fixedUserID');

        $dbeFixedUser = new DBEUser($this);
        $dbeFixedUser->getRow($fixedUserID);

        $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
        $template->set_file('page', 'ServiceCompletionAlertEmail.inc.html');

        if ($dbeJProblem->getValue('rootCauseID')) {
            $dbeRootCause = new DBERootCause($this);
            $dbeRootCause->getRow($dbeJProblem->getValue('rootCauseID'));
            $rootCause = $dbeRootCause->getValue('description');
        } else {
            $rootCause = 'Unknown';
        }

        $template->setVar(
            array(
                'contactFirstName'            => $dbeJCallActivity->getValue('contactFirstName'),
                'activityRef'                 => $activityRef,
                'reason'                      => $dbeJCallActivity->getValue('reason'),
                'lastActivityReason'          => $dbeJLastCallActivity->getValue('reason'),
                'rootCause'                   => $rootCause,
                'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC,
                'completeDate'                => Controller::dateYMDtoDMY($completeDate),
                'resolvedEngineerName'        => $dbeFixedUser->getValue('firstName') . ' ' . $dbeFixedUser->getValue('lastName')

            )
        );

        $template->parse('output', 'page', true);

        $body = $template->get_var('output');

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => CONFIG_SERVICE_REQUEST_DESC . ' ' . $dbeJCallActivity->getValue('problemID') . ' - Pending Closure on ' . Controller::dateYMDtoDMY($completeDate),
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    } // end sendServiceCompletedEmail

    /**
     * Calculate number of working hours for a problem
     *
     * @param integer $problemID
     */
    function getWorkingHours($problemID)
    {
        $this->dbeJProblem->getRow($problemID);

        $this->dbeJCallActivity->getRowsByProblemID($problemID, false);

        $utProblemStart = strtotime($this->dbeJProblem->getValue('dateRaised'));  // unix date start

        $utNow = date('U');                                                         // unix date now
        /*
        Build an array of pauses for the problem
        i.e. activities with awaitingCustomer
        */
        $this->awaitingCustomerResponseFlag = false;

        $pauseStart = false;

        $this->dbeJCallActivity->fetchNext();


        while ($this->dbeJCallActivity->fetchNext()) {

            if ($this->dbeJCallActivity->getValue('awaitingCustomerResponseFlag') == 'Y') {

                if (!$utPauseStart) {  // if not already paused

                    $pauseStart = strtotime($this->dbeJCallActivity->getValue('date') . ' ' . $this->dbeJCallActivity->getValue('startTime'));

                }

            } else {

                if ($pauseStart) {   // currently paused so record begining and end

                    $pauseArray[$pauseStart] = strtotime($this->dbeJCallActivity->getValue('date') . ' ' . $this->dbeJCallActivity->getValue('startTime'));

                    $pauseStart = false;

                }

            }

            $this->awaitingCustomerResponseFlag = $this->dbeJCallActivity->getValue('awaitingCustomerResponseFlag');
        } // end while callactivity loop

        // There wasn't an activity after the start pause so set end of the open pause to now
        if ($pauseStart) {
            $pauseArray[$pauseStart] = $utNow;

        }
        /*
        This field is an optomisation to avoid always counting through from the start of
        the problem. The field is reset when a Request is amended to force a recalculation from
        the start of the problem raised date.
        */
        if ($this->dbeJProblem->getValue('workingHoursCalculatedToTime') > '0000-00-00 00:00:00') {

            $addHoursSinceLastCalculation = true;

            $utCalculationStart = strtotime($this->dbeJProblem->getValue('workingHoursCalculatedToTime'));

        } else {

            $addHoursSinceLastCalculation = false;

            $utCalculationStart = strtotime($this->dbeJProblem->getValue('dateRaised'));

        }

        $this->hoursCalculated = $this->getWorkingHoursBetweenUnixDates(
            $utCalculationStart,
            $utNow,
            $pauseArray
        );

        if ($addHoursSinceLastCalculation) {
            $returnHours = $this->dbeJProblem->getValue('workingHours') + $this->hoursCalculated;
        } else {
            $returnHours = $this->hoursCalculated;
        }

        return $returnHours;

    } // end of function

    /**
     * This is to fix the problem where the working hours were being updated
     * when the request was completed
     *
     */
    function updateFixDurations()
    {
        $dbeJCallActivity = new DBEJCallActivity($this);

        $this->buActivity->getProblemsByStatus('C', $dsResults); // completed

        while ($dsResults->fetchNext()) {

            $workingHours = $this->getWorkingHours($dsResults->getValue('problemID'), true, $fixDate);

            $this->dbeProblem->getRow($dsResults->getValue('problemID'));

            $this->dbeProblem->setValue('workingHours', $workingHours);

            $this->dbeProblem->setValue('fixedDate', $fixDate);

            $this->dbeProblem->updateRow();

        }
    }

    function getWorkingHoursBetweenUnixDates($utStart, $utEnd, $pauseArray)
    {
        /*
        Step through in 5 minute intervals ignoring weekends and holidays
        */
        $utCounter = $utStart;
        $includedSeconds = 0;

        if ($pauseArray) {
            $pauseEnd = current($pauseArray);                     // the value is the end
            $pauseStart = key($pauseArray);                       // the key is the start
        }

        /*
        if ( ( $utEnd - $utCounter ) <= self::fiveMinutes ){

          return 0;

        }
        */
        //$utEnd = $utEnd - self::fiveMinutes; test

        while ($utCounter < $utEnd) {

            $dateAll = date('Y-m-d H:i N', $utCounter);

            $dateYMD = substr($dateAll, 0, 10);
            $time = substr($dateAll, 11, 5);
            $dayOfWeek = substr($dateAll, 17, 1);

            if (
                $dayOfWeek > 5 ||                                   // if weekend
                in_array($dateYMD, $this->ukBankHolidays) ||      // or bank holiday
                $time > $this->endSupportTime                       // or after office end
            ) {                                                    // then skip to start of next day
                $utCounter = strtotime($dateYMD . ' ' . $this->startSupportTime . ' + 1 DAY');;            // skip counter forward by one day
                continue;
            }

            if ($time < $this->startSupportTime) {               // before office start time

                $utCounter = strtotime($dateYMD . ' ' . $this->startSupportTime); // skip to start of this working day
                continue;

            }

            if (!$pauseStart) {                                  // no pauses left

                $includedSeconds += self::thirtySeconds;
                $utCounter += self::thirtySeconds;
                continue;

            }

            if ($utCounter <= $pauseStart) {                     // havent reached start of next pause
                $includedSeconds += self::thirtySeconds;
                $utCounter += self::thirtySeconds;
                continue;
            }

            if ($utCounter <= $pauseEnd) {                       // still within a pause
                $utCounter += self::thirtySeconds;
                continue;
            } else {                                                 // reached end of current pause so load next
                // get Next pause. returns false if none left
                $pauseEnd = next($pauseArray);
                $pauseStart = key($pauseArray);
            }

        }

        return ($includedSeconds / self::hour);
    } // end  getWorkingHoursBetweenDates

    /**
     * Calculate 3 working days in the future skipping non-work days
     */
    function getCompleteDate()
    {

        $utNowDate = date('U');                                                        // unix date now
        $ymdNowDate = date('Y-m-d', $utNowDate);

        $addDays = 1;

        $totalDays = 0;

        while ($totalDays < self::DAYS_UNTIL_COMPLETE) {

            $utTestDate = strtotime($nowDateYMD . ' + ' . $addDays . ' days');

            if (
                date('N', $utTestDate) < 6 &&                                 // not weekend
                !in_array(date('Y-m-d', $utTestDate), $this->ukBankHolidays)  // and not bank holiday
            ) {                                                        // then add another day
                $totalDays++;        // skip counter forward by one day
            }

            $addDays++;

        }

        return date('Y-m-d', $utTestDate);

    }

} // End of class
?>
