<?php


namespace CNCLTD\TwigDTOs;


class SalesOrderServiceRequestCreatedDTO
{

    public $contactFirstName;
    public $serviceRequestId;
    public $reason;
    public $teamName;
    public $serviceRequestStatus;

    /**
     * SalesOrderServiceRequestCreatedDTO constructor.
     * @param $contactFirstName
     * @param $serviceRequestId
     * @param $reason
     * @param $teamName
     * @param $serviceRequestStatus
     */
    public function __construct($contactFirstName, $serviceRequestId, $reason, $teamName, $serviceRequestStatus)
    {
        $this->contactFirstName = $contactFirstName;
        $this->serviceRequestId = $serviceRequestId;
        $this->reason = $reason;
        $this->teamName = $teamName;
        $this->serviceRequestStatus = $serviceRequestStatus;
    }


}