<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
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
     * @var bool
     */
    private $debug;

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

    function monitor($dryRun = false, $problemID = null, $debug = false)
    {
        $dsProblems = new DataSet($this);
        $this->debug = $debug;
        $this->buActivity->getProblemsByStatus(
            'I',
            $dsProblems
        ); // initial status

        $percentageSLA = 0;
        while ($dsProblems->fetchNext()) {
            if ($debug) {
                echo '<div>looking at SR: ' . $dsProblems->getValue(DBEJProblem::problemID) . '</div>';
            }
            if ($problemID && $dsProblems->getValue(DBEJProblem::problemID) != $problemID) {
                if ($debug) {
                    echo '<div>The current SR does not match the one provided by URL - ignoring</div>';
                }
                continue;
            } else {
                if ($debug) {
                    echo '<h1>Continuing!</h1>';
                }
            }
            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));
            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));
            $hoursToSLA = $dsProblems->getValue(DBEProblem::slaResponseHours) - $workingHours;
            /*
            Send an alert email to managers if within 20 minutes of SLA response hours and not priority 4 or 5
            */
            if (
                $hoursToSLA <= .3 &&                    // within one third of time to SLA
                $this->dbeProblem->getValue(DBEProblem::sentSlaAlertFlag) == 'N' &&          // hasn't already been sent
                $this->dbeProblem->getValue(DBEProblem::userID) != null &&                     // is asssigned
                $this->dbeProblem->getValue(DBEProblem::userID) != USER_SYSTEM &&
                $dsProblems->getValue(DBEProblem::priority) < 5

            ) {
                if (!$dryRun) {
                    $this->sendSlaAlertEmail(
                        $dsProblems->getValue(DBEProblem::problemID),
                        $percentageSLA
                    );

                    $this->dbeProblem->setValue(
                        DBEProblem::sentSlaAlertFlag,
                        'Y'
                    );
                }
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
                    date(DATE_MYSQL_DATETIME)
                );
            }

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';

            if (!$dryRun) {
                $this->dbeProblem->updateRow();
            }

            if ($problemID) {
                exit;
            }

        }

        echo "<H2>In Progress</H2>";

        $this->buActivity->getProblemsByStatus(
            'P',
            $dsProblems
        ); // in progress status

        while ($dsProblems->fetchNext()) {
            if ($problemID && $dsProblems->getValue(DBEJProblem::problemID) != $problemID) {
                continue;
            }
            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue(
                    DBEProblem::workingHoursCalculatedToTime,
                    date(DATE_MYSQL_DATETIME)
                );

            }

            $this->dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->awaitingCustomerResponseFlag
            );
            if (!$dryRun) {
                $this->dbeProblem->updateRow();
            }

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';
            if ($problemID) {
                exit;
            }
        }

        $this->buActivity->getProblemsByStatus(
            'P',
            $dsProblems,
            true
        ); // in progress future alarm date status

        while ($dsProblems->fetchNext()) {
            if ($problemID && $dsProblems->getValue(DBEJProblem::problemID) != $problemID) {
                continue;
            }
            $workingHours = $this->getWorkingHours($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->getRow($dsProblems->getValue(DBEProblem::problemID));

            $this->dbeProblem->setValue(
                DBEProblem::workingHours,
                $workingHours
            );

            if ($this->hoursCalculated) {

                $this->dbeProblem->setValue(
                    DBEProblem::workingHoursCalculatedToTime,
                    date(DATE_MYSQL_DATETIME)
                );

            }

            $this->dbeProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->awaitingCustomerResponseFlag
            );
            if (!$dryRun) {
                $this->dbeProblem->updateRow();
            }

            echo $this->dbeProblem->getValue(DBEProblem::problemID) . ': ' . $workingHours . '<BR/>';
            if ($problemID) {
                exit;
            }
        }

    } // end function monitor

    /**
     * Calculate number of working hours for a problem
     *
     * @param integer $problemID
     * @return bool|float|int|string
     */
    function getWorkingHours($problemID)
    {
        $this->dbeJProblem->getRow($problemID);

        if ($this->debug) {
            $this->dbeJCallActivity->setShowSQLOn();
        }
        $this->dbeJCallActivity->getRowsByProblemID(
            $problemID,
            false
        );

        $utNow = date('U');

        if ($this->debug) {
            echo '<div>Calculation Start: ' . $utNow . '</div>';
        }

        // unix date now
        /*
        Build an array of pauses for the problem
        i.e. activities with awaitingCustomer
        */
        $this->awaitingCustomerResponseFlag = false;

        $pauseStart = false;
        $pauseArray = [];
        if ($this->debug) {
            echo '<div>We have ' . $this->dbeJCallActivity->rowCount . ' activities to look at </div>';
        }
        while ($this->dbeJCallActivity->fetchNext()) {
            if ($this->dbeJCallActivity->getValue(DBEJCallActivity::awaitingCustomerResponseFlag) == 'Y') {
                if ($this->debug) {
                    echo '<div>Activity with AwaitingCustomerResponseFlag<div>';
                }
                if (!$pauseStart) {  // if not already paused
                    $pauseStart = strtotime(
                        $this->dbeJCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $this->dbeJCallActivity->getValue(DBEJCallActivity::startTime)
                    );
                    if ($this->debug) {
                        echo '<div>New PauseStart Value: ' . $pauseStart . '</div>';
                    }
                }
            } else {
                if ($this->debug) {
                    echo '<div>Activity without AwaitingCustomerResponseFlag<div>';
                }
                if ($pauseStart) {   // currently paused so record beginning and end
                    if ($this->debug) {
                        echo '<div>We had a pause start, so we need to record the end of it: ' . $pauseStart . '</div>';
                    }
                    $pauseArray[$pauseStart] = strtotime(
                        $this->dbeJCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $this->dbeJCallActivity->getValue(DBEJCallActivity::startTime)
                    );

                    if ($this->debug) {
                        echo '<div>' . json_encode($pauseArray) . '</div>';
                    }
                    $pauseStart = false;
                }

            }

            $this->awaitingCustomerResponseFlag = $this->dbeJCallActivity->getValue(
                DBEJCallActivity::awaitingCustomerResponseFlag
            );
        } // end while callactivity loop


        // There wasn't an activity after the start pause so set end of the open pause to now
        if ($pauseStart) {
            if ($this->debug) {
                echo '<div>We could not find an activity that closed the pause..so we use the current time as closing: ' . $utNow . ' </div>';
            }
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
            if ($this->debug) {
                echo '<div>This SR does have calculated time already: ' . print_r(
                        $utCalculationStart
                    ) . ', workingHoursCalculatedToTime: ' . $this->dbeJProblem->getValue(
                        DBEProblem::workingHoursCalculatedToTime
                    ) . '</div>';
            }

        } else {
            $addHoursSinceLastCalculation = false;
            $utCalculationStart = strtotime($this->dbeJProblem->getValue(DBEProblem::dateRaised));
            if ($this->debug) {
                echo '<div>This SR does NOT have calculated time already, so we look at the date raised: ' . $this->dbeJProblem->getValue(
                        DBEProblem::dateRaised
                    ) . ' -> calculationStart: ' . $utCalculationStart . '</div>';
            }
        }

        if ($this->debug) {
            echo '<div> calculationStart: ' . $utCalculationStart . ", now: " . $utNow . ", pauseArray ";
            var_dump($pauseArray);
            echo "</div>";
        }

        $this->hoursCalculated = $this->getWorkingHoursBetweenUnixDates(
            $utCalculationStart,
            $utNow,
            $pauseArray
        );

        if ($this->debug) {
            echo '<div>Calculated hours: ' . $this->hoursCalculated . '</div>';
        }
        if ($addHoursSinceLastCalculation) {
            if ($this->debug) {
                echo '<div>current Working Hours: ' . $this->dbeJProblem->getValue(DBEProblem::workingHours) . '</div>';
            }
            $returnHours = $this->dbeJProblem->getValue(DBEProblem::workingHours) + $this->hoursCalculated;
        } else {
            $returnHours = $this->hoursCalculated;
        }

        return round($returnHours, 2);

    } // end function autoCompletion

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
            if ($this->debug) {
                echo "<div>currentTime: " . date('Y-m-d H:i:s', $utCounter) . ", end: " . date(
                        'Y-m-d H:i:s',
                        $utEnd
                    ) . " pauseStart: " . date('Y-m-d H:i:s', $pauseStart) . ", pauseEnd: " . date(
                        'Y-m-d H:i:s',
                        $pauseEnd
                    ) . " </div>";

            }
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


            $isWeekend = $dayOfWeek > 5;
            $isBankHolidays = in_array($dateYMD, $this->ukBankHolidays);
            $isAfterHours = $time > $this->endSupportTime;

            if ($this->debug) {
                echo "<div> isWeekend: " . ($isWeekend ? 'True' : 'False') . " isHolidays: " . ($isBankHolidays ? "True" : "False") . " isAfterHours: " . ($isAfterHours ? 'True' : 'False') . "</div>";
            }

            if (
                $isWeekend ||
                $isBankHolidays ||
                $isAfterHours
            ) {
                // then skip to start of next day
                if ($this->debug) {
                    echo '<div>Skip day!</div>';
                }
                $utCounter = strtotime(
                    $dateYMD . ' ' . $this->startSupportTime . ' + 1 DAY'
                );          // skip counter forward by one day
                continue;
            }

            if ($time < $this->startSupportTime) {               // before office start time
                if ($this->debug) {
                    echo '<div>Current time before company open hours</div>';
                }
                $utCounter = strtotime($dateYMD . ' ' . $this->startSupportTime); // skip to start of this working day
                continue;

            }

            if (!$pauseStart) {
                // no pauses left
                if ($this->debug) {
                    echo '<div>There are no pauses left, add thirty seconds</div>';
                }
                $includedSeconds += self::thirtySeconds;
                $utCounter += self::thirtySeconds;
                continue;

            }

            if ($utCounter <= $pauseStart) {                     // havent reached start of next pause
                if ($this->debug) {
                    echo '<div>We haven not reached the next pause ..so add 30 seconds</div>';
                }
                $includedSeconds += self::thirtySeconds;
                $utCounter += self::thirtySeconds;
                continue;
            }

            if ($utCounter <= $pauseEnd) {                       // still within a pause
                if ($this->debug) {
                    echo '<div>we are still within the pause</div>';
                }
                $utCounter += self::thirtySeconds;
                continue;
            } else {                                                 // reached end of current pause so load next
                // get Next pause. returns false if none left
                $pauseEnd = next($pauseArray);
                $pauseStart = key($pauseArray);
            }
            if ($this->debug) {
                echo '<div>utCounter: ' . $utCounter . ', includedSeconds: ' . $includedSeconds . '</div>';
            }
        }

        return ($includedSeconds / self::hour);
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

            $urlActivity = SITE_URL . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
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
                $body
            );

        } // end if ( $dbeJCallActivity = $this->buActivity->getFirstActivityInProblem( $problemID ) ){

    }

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

                ?>
                <div>
                Problem: <?= $problemID ?>
                <?php
                $buActivity = new BUActivity($this);
                $fixedActivity = $buActivity->getFixedActivityInProblem($problemID);
                if (!$fixedActivity) {
                    ?>
                    <h2>This SR doesn't have a fixed activity!!</h2>
                    </div>
                    <?php

                    $this->sendNoFixedActivityAlert($problemID);
                    continue;
                }

                $this->dbeProblem->getRow($problemID);

                $fixedDate = strtotime($this->dbeProblem->getValue(DBEProblem::completeDate));

                $hoursUntilComplete =
                    $this->getWorkingHoursBetweenUnixDates(
                        date('U'),
                        // from now
                        strtotime(
                            $this->dbeProblem->getValue(
                                DBEProblem::completeDate
                            ) . ' ' . $dbeCallActivity->getValue(
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

    }

    private function sendNoFixedActivityAlert($serviceRequestId)
    {
        $dbeActivity = new DBEJCallActivity($this);
        $dbeActivity = $dbeActivity->getLastActionableActivityByProblemID($serviceRequestId);
        if (!$dbeActivity) {
            throw new UnexpectedValueException("No last activity was found for this SR: " . $serviceRequestId);
        }

        $createdByUserID = $dbeActivity->getValue(DBECallActivity::userID);

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($createdByUserID);
        $teamId = $dbeUser->getValue(DBEUser::teamID);
        $dbeTeam = new DBETeam($this);
        $dbeTeam->getRow($teamId);
        $managerId = $dbeTeam->getValue(DBETeam::leaderId);
        $manager = new DBEUser($this);
        $manager->getRow($managerId);

        $activityURL = SITE_URL . Controller::formatForHTML(
                '/Activity.php?action=displayLastActivity&problemID=' . $serviceRequestId,
                1
            );

        $subject = "To be Closed service request missing fixed activity";

        global $twig;

        $body = $twig->render(
            '@internal/toBeClosedSRMissingFixedEmail.html.twig',
            [
                "serviceRequestLink" => $activityURL,
                "serviceRequestId"   => $serviceRequestId
            ]
        );
        $emailTo = $manager->getEmail();

        $hdrs = array(
            'From'         => CONFIG_SUPPORT_EMAIL,
            'To'           => $emailTo,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $mime = new Mail_mime();

        $mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $mime->get($mime_params);

        $hdrs = $mime->headers($hdrs);

        $buMail = new BUMail($this);

        $buMail->putInQueue(
            CONFIG_SUPPORT_EMAIL,
            $emailTo,
            $hdrs,
            $body
        );

    }

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
    } // end of function

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
            'To'           => $toEmail,
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
    }

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
