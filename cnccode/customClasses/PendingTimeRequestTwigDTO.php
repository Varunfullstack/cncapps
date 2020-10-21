<?php


namespace CNCLTD;


class PendingTimeRequestTwigDTO
{
    private $customerName;
    private $srURL;
    private $notes;
    private $requestedBy;
    private $requestedDateTime;
    private $processURL;
    private $chargeableHours;
    private $timeSpentSoFar;
    private $timeLeftOnBudget;
    private $requesterTeam;
    private $approvalLevel;
    private $serviceRequestId;

    public function __construct($customerName,
                                $srURL,
                                $notes,
                                $requestedBy,
                                $requestedDateTime,
                                $processURL,
                                $chargeableHours,
                                $timeSpentSoFar,
                                $timeLeftOnBudget,
                                $requesterTeam,
                                $approvalLevel,
                                $serviceRequestId
    )
    {
        $this->customerName = $customerName;
        $this->srURL = $srURL;
        $this->notes = $notes;
        $this->requestedBy = $requestedBy;
        $this->requestedDateTime = $requestedDateTime;
        $this->processURL = $processURL;
        $this->chargeableHours = $chargeableHours;
        $this->timeSpentSoFar = $timeSpentSoFar;
        $this->timeLeftOnBudget = $timeLeftOnBudget;
        $this->requesterTeam = $requesterTeam;
        $this->approvalLevel = $approvalLevel;
        $this->serviceRequestId = $serviceRequestId;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return mixed
     */
    public function getSrURL()
    {
        return $this->srURL;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return mixed
     */
    public function getRequestedBy()
    {
        return $this->requestedBy;
    }

    /**
     * @return mixed
     */
    public function getRequestedDateTime()
    {
        return $this->requestedDateTime;
    }

    /**
     * @return mixed
     */
    public function getProcessURL()
    {
        return $this->processURL;
    }

    /**
     * @return mixed
     */
    public function getChargeableHours()
    {
        return $this->chargeableHours;
    }

    /**
     * @return mixed
     */
    public function getTimeSpentSoFar()
    {
        return $this->timeSpentSoFar;
    }

    /**
     * @return mixed
     */
    public function getTimeLeftOnBudget()
    {
        return $this->timeLeftOnBudget;
    }

    /**
     * @return mixed
     */
    public function getRequesterTeam()
    {
        return $this->requesterTeam;
    }

    /**
     * @return mixed
     */
    public function getApprovalLevel()
    {
        return $this->approvalLevel;
    }

    /**
     * @return mixed
     */
    public function getServiceRequestId()
    {
        return $this->serviceRequestId;
    }

}