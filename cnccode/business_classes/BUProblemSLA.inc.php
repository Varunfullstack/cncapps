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

    private $startSupportTime;
    private $endSupportTime;
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
    private $srAutocompleteThresholdHours;
    private $startersLeaversAutoCompleteThresholdHours;
    /**
     * @var false|string
     */
    private $dateFourWeeksAgo;

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
        $this->ukBankHolidays = array_merge(
            $lastYearBH,
            $thisYearBH,
            $nextYearBH
        );

        $this->buCustomerItem = new BUCustomerItem($this);

        $this->buActivity = new BUActivity ($this);


        $this->dateFourWeeksAgo = date(
            'Y-m-d',
            strtotime(date('Y-m-d') . ' -4 WEEKS')
        );

        $buHeader = new BUHeader ($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $this->startSupportTime = $dsHeader->getValue(DBEHeader::billingStartTime);

        $this->endSupportTime = $dsHeader->getValue(DBEHeader::billingEndTime);

        $this->srAutocompleteThresholdHours = $dsHeader->getValue(DBEHeader::srAutocompleteThresholdHours);
        $this->startersLeaversAutoCompleteThresholdHours = $dsHeader->getValue(
            DBEHeader::srStartersLeaversAutoCompleteThresholdHours
        );

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
        $dsProblems = new DataSet($this);
        $this->buActivity->getProblemsByStatus(
            'I',
            $dsProblems
        ); // initial status

        $percentageSLA = 0;
        while ($dsProblems->fetchNext()) {


            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));


            $hoursToSLA = $dsProblems->getValue(DBEProblem::slaResponseHours) - $workingHours;

            /*
            Send an alert email to managers if within 20 minutes of SLA response hours and not priority 4 or 5
            */
            if (
                $hoursToSLA <= .3 &&                    // within one third of time to SLA

                $this->dbeProblem->getValue(DBEProblem::sentSlaAlertFlag) == 'N' &&          // hasn't already been sent
                $this->dbeProblem->getValue(DBEProblem::userID) != '' &&                     // is asssigned
                $this->dbeProblem->getValue(DBEProblem::userID) != USER_SYSTEM &&
                $dsProblems->getValue(DBEProblem::priority) < 5

            ) {

                $this->sendSlaAlertEmail(
                    $dsProblems->getValue(DBEProblem::problemID),
                    $percentageSLA
                );

                $this->dbeProblem->setValue(
                    DBEProblem::sentSlaAlertFlag,
                    'Y'
                );

            }

            $this->dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->awaitingCustomerResponseFlag
            );
            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            if ($this->hoursCalculated) {
                $this->dbeProblem->setValue(
                    DBEProblem::workingHoursCalculatedToTime,
                    date(CONFIG_MYSQL_DATETIME)
                );
            }

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';


            $this->dbeProblem->updateRow();

        }

        echo "<H2>In Progress</H2>";

        $this->buActivity->getProblemsByStatus(
            'P',
            $dsProblems
        ); // in progress status

        while ($dsProblems->fetchNext()) {

            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue(
                    DBEProblem::workingHoursCalculatedToTime,
                    date(CONFIG_MYSQL_DATETIME)
                );

            }

            $this->dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->awaitingCustomerResponseFlag
            );
            $this->dbeProblem->updateRow();

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';

        }

        $this->buActivity->getProblemsByStatus(
            'P',
            $dsProblems,
            true
        ); // in progress future alarm date status

        while ($dsProblems->fetchNext()) {

            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue(
                    DBEProblem::workingHoursCalculatedToTime,
                    date(CONFIG_MYSQL_DATETIME)
                );

            }

            $this->dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->awaitingCustomerResponseFlag
            );
            $this->dbeProblem->updateRow();

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';

        }

    } // end function monitor

    /**
     * @throws Exception
     */
    function autoCompletion()
    {
        $dbeCustomer = new DBECustomer($this);
        $dsProblems = new DataSet($this);
        $this->buActivity->getProblemsByStatus(
            'F',
            $dsProblems,
            true
        ); // fixed status

        while ($dsProblems->fetchNext()) {

            $problemID = $dsProblems->getValue(DBEProblem::problemID);

            $dbeCallActivity = $this->buActivity->getLastActivityInProblem($problemID);

            if ($dbeCallActivity) {

                $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

                $fixedDate = strtotime($this->dbeProblem->getValue(DBEProblem::completeDate));

                $hoursUntilComplete =
                    $this->getWorkingHoursBetweenUnixDates(
                        date('U'),
                        // from now
                        strtotime(
                            $this->dbeProblem->getValue(DBEProblem::completeDate) . ' ' . $dbeCallActivity->getValue(
                                DBEJCallActivity::endTime
                            )
                        )
                    );
                /*
                Autocomplete NON-T&M SRs that have activity duration of less than one hour and have reached their complete date
                */

                $dbeCustomer->getRow($this->dbeProblem->getValue(DBEProblem::customerID));
                $buCustomerItem = new BUCustomerItem($this);
                $startersLeavers = [62, 58];

                $serverCareContractID = $serverCareContractID = $buCustomerItem->getValidServerCareContractID(
                    $this->dbeProblem->getValue(DBEProblem::customerID)
                );

                $thresholdCheck = $this->dbeProblem->getValue(
                        DBEProblem::totalActivityDurationHours
                    ) <= $this->startersLeaversAutoCompleteThresholdHours;

                $fixedDateCheck = $fixedDate <= time();
                $reasonCheck = in_array(
                    $this->dbeProblem->getValue(DBEProblem::rootCauseID),
                    $startersLeavers
                );

                ?>
                <div>
                    Problem: <?= $problemID ?>
                    <div>
                        Rootcause id = <?= $this->dbeProblem->getValue(DBEProblem::rootCauseID) ?>
                    </div>
                    <div>
                        Server Care Contract id = <?= $serverCareContractID ?>
                    </div>
                    <div>
                        Fixed Date = <?= $fixedDate ?>
                    </div>
                    <div>
                        Total Activity Duration Hours = <?= $this->dbeProblem->getValue(
                            DBEProblem::totalActivityDurationHours
                        ) ?>
                    </div>
                    <ul>
                        <li>
                            Server Care Check: <?= $serverCareContractID ? 'true' : 'false' ?>
                        </li>
                        <li>
                            $thresholdCheck: <?= $thresholdCheck ? 'true' : 'false' ?>
                        </li>
                        <li>
                            $fixedDateCheck: <?= $fixedDateCheck ? 'true' : 'false' ?>
                        </li>
                        <li>
                            $reasonCheck: <?= $reasonCheck ? 'true' : 'false' ?>
                        </li>
                    </ul>
                </div>
                <?php


                if ($serverCareContractID && $thresholdCheck && $fixedDateCheck && $reasonCheck) {
                    $this->dbeProblem->setValue(
                        DBEJProblem::contractCustomerItemID,
                        $serverCareContractID
                    );
                    $this->dbeProblem->updateRow();
                    $this->buActivity->setProblemToCompleted($problemID);
                    continue;
                }

                if (
                    $this->dbeProblem->getValue(DBEProblem::contractCustomerItemID) != 0 &&
                    $hoursUntilComplete <= 0 &
                    $this->dbeProblem->getValue(
                        DBEProblem::totalActivityDurationHours
                    ) <= $this->srAutocompleteThresholdHours
                ) {
                    $this->buActivity->setProblemToCompleted($problemID);
                } else {
                    /*
                    if within 2 working days of complete date send an email up to maximum 2 emails.
                    */
                    if (
                        $hoursUntilComplete <= ($this->workingHoursInDay * 2) &&
                        $this->dbeProblem->getValue(DBEProblem::completionAlertCount) < 2
                    ) {
                        $this->dbeProblem->setValue(
                            DBEProblem::completionAlertCount,
                            $this->dbeProblem->getValue(DBEProblem::completionAlertCount) + 1
                        );
                        $this->dbeProblem->updateRow();

                        $this->buActivity->sendEmailToCustomer(
                            $problemID,
                            BUActivity::PendingClosureCustomerEmailCategory
                        );

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
            caa_problemno IN (" . implode(
                    ',',
                    $ids
                ) . ")";

            $this->db->query($SQL);

            $SQL =
                "DELETE FROM
            problem
          WHERE
            pro_problemno IN (" . implode(
                    ',',
                    $ids
                ) . ")";

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

        $toEmail = 'specialattentionends@' . CONFIG_PUBLIC_DOMAIN;

        $template = new Template (
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'SpecialAttentionAlertEmail.inc.html'
        );

        $template->setVar(
            array(
                'customerName'            => $customer->cus_name,
                'specialAttentionEndDate' => $customer->cus_special_attention_end_date,
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

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
     * @param $problemID
     * @param $percentage
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

            if ($dbeJProblem->getValue(DBEJProblem::engineerLogname)) {
                $toEmail = $dbeJProblem->getValue(DBEJProblem::engineerLogname) . '@' . CONFIG_PUBLIC_DOMAIN;
            } else {
                $toEmail = false;
            }

            if ($toEmail) {
                $toEmail .= ',';
            }
            $toEmail .= 'slabreachalert@' . CONFIG_PUBLIC_DOMAIN;

            $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID);

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );
            $template->set_file(
                'page',
                'SlaAlertEmail.inc.html'
            );

            $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
                );

            $template->setVar(
                array(
                    'urlActivity'                 => $urlActivity,
                    'customerName'                => $dbeJProblem->getValue(DBEJProblem::customerName),
                    'activityRef'                 => $activityRef,
                    'reason'                      => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                    'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC,
                    'percentage'                  => number_format(
                        $percentage,
                        0
                    )
                )
            );

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $hdrs = array(
                'To'           => $toEmail,
                'From'         => $senderEmail,
                'Subject'      => 'WARNING - SR for ' . $dbeJProblem->getValue(
                        DBEJProblem::customerName
                    ) . 'assigned to ' . $dbeJProblem->getValue(DBEJProblem::engineerName) . ' close to breaching SLA',
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
     * Calculate number of working hours for a problem
     *
     * @param integer $problemID
     * @return bool|float|int|string
     */
    function getWorkingHours($problemID)
    {
        $this->dbeJProblem->getRow($problemID);

        $this->dbeJCallActivity->getRowsByProblemID(
            $problemID,
            false
        );

        $utNow = date('U');                                                         // unix date now
        /*
        Build an array of pauses for the problem
        i.e. activities with awaitingCustomer
        */
        $this->awaitingCustomerResponseFlag = false;

        $pauseStart = false;
        $pauseArray = [];
        $this->dbeJCallActivity->fetchNext();


        while ($this->dbeJCallActivity->fetchNext()) {

            if ($this->dbeJCallActivity->getValue(DBEJCallActivity::awaitingCustomerResponseFlag) == 'Y') {
                if (!$pauseStart) {  // if not already paused
                    $pauseStart = strtotime(
                        $this->dbeJCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $this->dbeJCallActivity->getValue(DBEJCallActivity::startTime)
                    );

                }
            } else {

                if ($pauseStart) {   // currently paused so record begining and end

                    $pauseArray[$pauseStart] = strtotime(
                        $this->dbeJCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $this->dbeJCallActivity->getValue(DBEJCallActivity::startTime)
                    );

                    $pauseStart = false;

                }

            }

            $this->awaitingCustomerResponseFlag = $this->dbeJCallActivity->getValue(
                DBEJCallActivity::awaitingCustomerResponseFlag
            );
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
        if ($this->dbeJProblem->getValue(DBEProblem::workingHoursCalculatedToTime)) {
            $addHoursSinceLastCalculation = true;
            $utCalculationStart = strtotime($this->dbeJProblem->getValue(DBEProblem::workingHoursCalculatedToTime));
        } else {
            $addHoursSinceLastCalculation = false;
            $utCalculationStart = strtotime($this->dbeJProblem->getValue(DBEProblem::dateRaised));
        }

        $this->hoursCalculated = $this->getWorkingHoursBetweenUnixDates(
            $utCalculationStart,
            $utNow,
            $pauseArray
        );

        if ($addHoursSinceLastCalculation) {
            $returnHours = $this->dbeJProblem->getValue(DBEProblem::workingHours) + $this->hoursCalculated;
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
        $dsProblems = new DataSet($this);
        $this->buActivity->getProblemsByStatus(
            'C',
            $dsProblems
        ); // completed

        while ($dsProblems->fetchNext()) {

            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            $this->dbeProblem->updateRow();

        }
    }

    function getWorkingHoursBetweenUnixDates($utStart,
                                             $utEnd,
                                             $pauseArray = []
    )
    {
        /*
        Step through in 5 minute intervals ignoring weekends and holidays
        */
        $utCounter = $utStart;
        $includedSeconds = 0;
        $pauseStart = null;
        $pauseEnd = null;
        if (count($pauseArray)) {
            $pauseEnd = current($pauseArray);                     // the value is the end
            $pauseStart = key($pauseArray);                       // the key is the start
        }

        while ($utCounter < $utEnd) {

            $dateAll = date(
                'Y-m-d H:i N',
                $utCounter
            );

            $dateYMD = substr(
                $dateAll,
                0,
                10
            );
            $time = substr(
                $dateAll,
                11,
                5
            );
            $dayOfWeek = substr(
                $dateAll,
                17,
                1
            );

            if (
                $dayOfWeek > 5 ||                                   // if weekend
                in_array(
                    $dateYMD,
                    $this->ukBankHolidays
                ) ||      // or bank holiday
                $time > $this->endSupportTime                       // or after office end
            ) {                                                    // then skip to start of next day
                $utCounter = strtotime(
                    $dateYMD . ' ' . $this->startSupportTime . ' + 1 DAY'
                );;            // skip counter forward by one day
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
        $ymdNowDate = date(
            'Y-m-d',
            $utNowDate
        );

        $addDays = 1;

        $totalDays = 0;

        $utTestDate = null;

        while ($totalDays < self::DAYS_UNTIL_COMPLETE) {

            $utTestDate = strtotime($ymdNowDate . ' + ' . $addDays . ' days');

            if (
                date(
                    'N',
                    $utTestDate
                ) < 6 &&                                 // not weekend
                !in_array(
                    date(
                        'Y-m-d',
                        $utTestDate
                    ),
                    $this->ukBankHolidays
                )  // and not bank holiday
            ) {                                                        // then add another day
                $totalDays++;        // skip counter forward by one day
            }

            $addDays++;

        }

        return date(
            'Y-m-d',
            $utTestDate
        );

    }

} // End of class
?>
