<?php

namespace CNCLTD\SDManagerDashboard;

use BUActivity;
use CNCLTD\Utils;
use Controller;
use DBEJProblem;

class ServiceRequestSummaryDTO implements \JsonSerializable
{
    /**
     * @var bool|float|int|string|null
     */
    private $hoursRemainingForSLA;
    /**
     * @var bool
     */
    private $isBeingWorkedOn;
    /**
     * @var bool|float|int|string|null
     */
    private $status;
    /**
     * @var bool
     */
    private $isSLABreached;
    /**
     * @var bool|float|int|string|null
     */
    private $totalActivityDurationHours;
    /**
     * @var bool
     */
    private $awaitingCustomerResponse;
    /**
     * @var bool|float|int|string|null
     */
    private $time;
    /**
     * @var \DateTimeInterface|null
     */
    private $dateTime;
    /**
     * @var bool|float|int|string|null
     */
    private $date;
    /**
     * @var bool|float|int|string|null
     */
    private $problemID;
    /**
     * @var false|string
     */
    private $reason;
    /**
     * @var mixed|string
     */
    private $urlProblemHistoryPopup;
    /**
     * @var bool|float|int|string|null
     */
    private $engineerName;
    /**
     * @var bool|float|int|string|null
     */
    private $customerID;
    /**
     * @var bool|float|int|string|null
     */
    private $customerName;
    /**
     * @var bool
     */
    private $specialAttentionCustomer;
    /**
     * @var bool|float|int|string|null
     */
    private $slaResponseHours;
    /**
     * @var bool|float|int|string|null
     */
    private $priority;
    private $activityCount;
    /**
     * @var \DateTimeInterface|null
     */
    private $alarmDateTime;
    /**
     * @var bool|float|int|string|null
     */
    private $teamID;
    /**
     * @var bool|float|int|string|null
     */
    private $engineerId;
    /**
     * @var bool
     */
    private $workHidden;
    /**
     * @var bool|float|int|string|null
     */
    private $lastCallActTypeID;
    /**
     * @var bool|float|int|string|null
     */
    private $callActivityID;
    /**
     * @var bool|float|int|mixed|string
     */
    private $minutesRemaining;
    /**
     * @var bool|float|int|string|null
     */
    private $queueTeamId;
    /**
     * @var bool|float|int|string|null
     */
    private $fixedDate;
    /**
     * @var bool|float|int|string|null
     */
    private $engineerFixedName;
    /**
     * @var bool|float|int|string|null
     */
    private $fixedTeamId;
    /**
     * @var bool|float|int|string|null
     */
    private $queueNo;

    /**
     * @var bool
     */
    private $isFixSLABreached;


    /**
     * ServiceRequestSummaryDTO constructor.
     */
    public function __construct() { }

    public static function fromDBEJProblem(\DBEJProblem $problem, $withActivityCount = false)
    {

        $instance         = new self();
        $activityCount    = 0;
        $stuff            = null;
        $serviceRequestId = $problem->getValue(DBEJProblem::problemID);
        if ($withActivityCount) {
            $buActivity    = new BUActivity($stuff);
            $activityCount = $buActivity->getActivityCount($serviceRequestId);
        }
        $buActivity      = new BUActivity($stuff);
        $usedMinutes     = 0;
        $assignedMinutes = 0;
        switch ($problem->getValue(DBEJProblem::QUEUE_TEAM_ID)) {
            case 1:
            {
                $usedMinutes     = $buActivity->getHDTeamUsedTime($serviceRequestId);
                $assignedMinutes = $problem->getValue(DBEJProblem::hdLimitMinutes);
                break;
            }
            case 2:
            {
                $usedMinutes     = $buActivity->getESTeamUsedTime($serviceRequestId);
                $assignedMinutes = $problem->getValue(DBEJProblem::esLimitMinutes);
                break;
            }
            case 4:
            {
                $usedMinutes     = $buActivity->getSPTeamUsedTime($serviceRequestId);
                $assignedMinutes = $problem->getValue(DBEJProblem::smallProjectsTeamLimitMinutes);
                break;
            }
            case 5:
            {
                $usedMinutes     = $buActivity->getUsedTimeForProblemAndTeam($serviceRequestId, 5);
                $assignedMinutes = $problem->getValue(DBEJProblem::projectTeamLimitMinutes);
                break;
            }
            default:
                break;
        }
        $minutesRemaining                     = $assignedMinutes - $usedMinutes;
        $instance->hoursRemainingForSLA       = $problem->getValue(DBEJProblem::hoursRemainingForSLA);
        $instance->isBeingWorkedOn            = $problem->isRequestBeingWorkedOn();
        $instance->status                     = $problem->getValue(DBEJProblem::status);
        $instance->isSLABreached              = $problem->isSLABreached();
        $instance->totalActivityDurationHours = $problem->getValue(DBEJProblem::totalActivityDurationHours);
        $instance->awaitingCustomerResponse   = $problem->isOnHold();
        $instance->time                       = $problem->getValue(DBEJProblem::lastStartTime);
        $instance->date                       = $problem->getValue(DBEJProblem::lastDate);
        $instance->dateTime                   = $problem->getDateTime();
        $instance->problemID                  = $serviceRequestId;
        $instance->reason                     = Utils::truncate($problem->getValue(DBEJProblem::reason), 150);
        $instance->urlProblemHistoryPopup     = self::getProblemHistoryURL($serviceRequestId);
        $instance->engineerName               = $problem->getValue(DBEJProblem::engineerName);
        $instance->customerID                 = $problem->getValue(DBEJProblem::customerID);
        $instance->customerName               = $problem->getValue(DBEJProblem::customerName);
        $instance->specialAttentionCustomer   = $problem->isSpecialAttention();
        $instance->slaResponseHours           = $problem->getValue(DBEJProblem::slaResponseHours);
        $instance->priority                   = $problem->getValue(DBEJProblem::priority);
        $instance->alarmDateTime              = $problem->alarmDateTime();
        $instance->activityCount              = $activityCount;
        $instance->teamID                     = $problem->getValue(DBEJProblem::teamID);
        $instance->engineerId                 = $problem->getValue(DBEJProblem::userID);
        $instance->workHidden                 = $problem->isWorkHidden();
        $instance->lastCallActTypeID          = $problem->getValue(DBEJProblem::lastCallActTypeID);
        $instance->callActivityID             = $problem->getValue(DBEJProblem::callActivityID);
        $instance->minutesRemaining           = $minutesRemaining;
        $instance->queueTeamId                = $problem->getValue(DBEJProblem::QUEUE_TEAM_ID);
        $instance->fixedDate                  = $problem->getValue(DBEJProblem::FIXED_DATE);
        $instance->engineerFixedName          = $problem->getValue(DBEJProblem::ENGINEER_FIXED_NAME);
        $instance->fixedTeamId                = $problem->getValue(DBEJProblem::FIXED_TEAM_ID);
        $instance->queueNo                    = $problem->getValue(DBEJProblem::queueNo);
        $instance->isFixSLABreached           = $problem->getValue(DBEJProblem::IS_FIX_SLA_BREACHED);
        return $instance;
    }

    private static function getProblemHistoryURL($problemId)
    {
        return Controller::buildLink(
            'Activity.php',
            [
                'action'    => 'problemHistoryPopup',
                'problemID' => $problemId,
                'htmlFmt'   => CT_HTML_FMT_POPUP
            ]
        );
    }

    public function jsonSerialize()
    {
        return [
            "hoursRemainingForSLA"       => $this->hoursRemainingForSLA,
            "isBeingWorkedOn"            => $this->isBeingWorkedOn,
            "status"                     => $this->status,
            "isSLABreached"              => $this->isSLABreached,
            "totalActivityDurationHours" => $this->totalActivityDurationHours,
            "awaitingCustomerResponse"   => $this->awaitingCustomerResponse,
            "time"                       => $this->time,
            "date"                       => $this->date,
            "dateTime"                   => Utils::dateTimeToString($this->dateTime),
            "problemID"                  => $this->problemID,
            "reason"                     => $this->reason,
            "urlProblemHistoryPopup"     => $this->urlProblemHistoryPopup,
            "engineerName"               => $this->engineerName,
            "customerID"                 => $this->customerID,
            "customerName"               => $this->customerName,
            "specialAttentionCustomer"   => $this->specialAttentionCustomer,
            "slaResponseHours"           => $this->slaResponseHours,
            "priority"                   => $this->priority,
            "alarmDateTime"              => Utils::dateTimeToString($this->alarmDateTime),
            "activityCount"              => $this->activityCount,
            "teamID"                     => $this->teamID,
            "engineerId"                 => $this->engineerId,
            "workHidden"                 => $this->workHidden,
            "lastCallActTypeID"          => $this->lastCallActTypeID,
            "callActivityID"             => $this->callActivityID,
            "minutesRemaining"           => $this->minutesRemaining,
            "queueTeamId"                => $this->queueTeamId,
            "fixedDate"                  => $this->fixedDate,
            "engineerFixedName"          => $this->engineerFixedName,
            "fixedTeamId"                => $this->fixedTeamId,
            "queueNo"                    => $this->queueNo,
            "isFixedSLABreached"         => $this->isFixSLABreached,
        ];
    }

}