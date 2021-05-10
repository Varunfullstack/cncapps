<?php

namespace CNCLTD\DailyStatsDashboard\Core;

use CNCLTD\Data\DBEJProblem;

class DailyStatsDashboardDTO implements \JsonSerializable
{
    /** @var string */
    private $serviceRequestId;
    /** @var string */
    private $customerName;
    /** @var string */
    private $priority;
    /** @var string */
    private $subjectSummary;
    /** @var string */
    private $assignedTeamName;
    /** @var string */
    private $assignedEngineerName;
    /**
     * @var int|null
     */
    private $teamId;

    private function __construct(?string $serviceRequestId,
                                 ?string $customerName,
                                 ?string $priority,
                                 ?string $subjectSummary,
                                 ?string $assignedTeamName,
                                 ?string $assignedEngineerName,
                                 ?int $teamId
    )
    {
        $this->serviceRequestId     = $serviceRequestId;
        $this->customerName         = $customerName;
        $this->priority             = $priority;
        $this->subjectSummary       = $subjectSummary;
        $this->assignedTeamName     = $assignedTeamName;
        $this->assignedEngineerName = $assignedEngineerName;
        $this->teamId               = $teamId;
    }

    public static function fromServiceRequestDB(DBEJProblem $serviceRequestDB): DailyStatsDashboardDTO
    {
        return new self(
            $serviceRequestDB->getValue(DBEJProblem::problemID),
            $serviceRequestDB->getValue(DBEJProblem::customerName),
            $serviceRequestDB->getValue(DBEJProblem::priority),
            $serviceRequestDB->getValue(DBEJProblem::emailSubjectSummary),
            $serviceRequestDB->getValue(DBEJProblem::QUEUE_TEAM_NAME),
            $serviceRequestDB->getValue(DBEJProblem::engineerName),
            $serviceRequestDB->getValue(DBEJProblem::teamID)
        );
    }

    /**
     * @return string
     */
    public function serviceRequestId(): ?string
    {
        return $this->serviceRequestId;
    }

    /**
     * @return string
     */
    public function customerName(): ?string
    {
        return $this->customerName;
    }

    /**
     * @return string
     */
    public function priority(): ?string
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function subjectSummary(): ?string
    {
        return $this->subjectSummary;
    }

    /**
     * @return string
     */
    public function assignedTeamName(): ?string
    {
        return $this->assignedTeamName;
    }

    /**
     * @return string
     */
    public function assignedEngineerName(): ?string
    {
        return $this->assignedEngineerName;
    }

    /**
     * @return int|null
     */
    public function teamId(): ?int
    {
        return $this->teamId;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}