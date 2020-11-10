<?php


namespace CNCLTD\TwigDTOs;


class PriorityChangedDTO
{
    public $serviceRequestId;
    public $lastActivityReason;
    public $contactFirstName;
    public $oldPriority;
    public $newPriority;
    public $SLA;
    public $serviceRequestStatus;

    /**
     * PriorityChangedDTO constructor.
     * @param $serviceRequestId
     * @param $lastActivityReason
     * @param $contactFirstName
     * @param $oldPriority
     * @param $newPriority
     * @param $SLA
     * @param $serviceRequestStatus
     */
    public function __construct($serviceRequestId,
                                $lastActivityReason,
                                $contactFirstName,
                                $oldPriority,
                                $newPriority,
                                $SLA,
                                $serviceRequestStatus
    )
    {
        $this->serviceRequestId = $serviceRequestId;
        $this->lastActivityReason = $lastActivityReason;
        $this->contactFirstName = $contactFirstName;
        $this->oldPriority = $oldPriority;
        $this->newPriority = $newPriority;
        $this->SLA = $SLA;
        $this->serviceRequestStatus = $serviceRequestStatus;
    }

}