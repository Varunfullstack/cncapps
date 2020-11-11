<?php


namespace CNCLTD\TwigDTOs;


class ServiceRequestLoggedDTO
{

    public $serviceRequestId;
    public $contactFirstName;
    public $reason;
    public $priority;
    public $serviceRequestStatus;
    public $SLA;
    public $isLoggedOutOfHours;
    public $hasServiceDesk;
    public $support24;
    public $raisedAutomatically;


    /**
     * ServiceRequestLogged constructor.
     * @param $serviceRequestId
     * @param $contactFirstName
     * @param $reason
     * @param $priority
     * @param $serviceRequestStatus
     * @param $SLA
     * @param $isLoggedOutOfHours
     * @param $hasServiceDesk
     * @param $support24
     * @param $raisedAutomatically
     */
    public function __construct($serviceRequestId,
                                $contactFirstName,
                                $reason,
                                $priority,
                                $serviceRequestStatus,
                                $SLA,
                                $isLoggedOutOfHours,
                                $hasServiceDesk,
                                $support24,
                                $raisedAutomatically
    )
    {
        $this->serviceRequestId = $serviceRequestId;
        $this->contactFirstName = $contactFirstName;
        $this->reason = $reason;
        $this->priority = $priority;
        $this->serviceRequestStatus = $serviceRequestStatus;
        $this->SLA = $SLA;
        $this->isLoggedOutOfHours = $isLoggedOutOfHours;
        $this->hasServiceDesk = $hasServiceDesk;
        $this->support24 = $support24;
        $this->raisedAutomatically = $raisedAutomatically;
    }


}