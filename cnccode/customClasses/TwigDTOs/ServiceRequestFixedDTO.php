<?php


namespace CNCLTD\TwigDTOs;


class ServiceRequestFixedDTO
{
    public $contactFirstName;
    public $firstActivityReason;
    public $lastActivityReason;
    public $serviceRequestId;

    /**
     * ServiceRequestFixedDTO constructor.
     * @param $contactFirstName
     * @param $firstActivityReason
     * @param $lastActivityReason
     * @param $serviceRequestId
     */
    public function __construct($contactFirstName, $firstActivityReason, $lastActivityReason, $serviceRequestId)
    {
        $this->contactFirstName = $contactFirstName;
        $this->firstActivityReason = $firstActivityReason;
        $this->lastActivityReason = $lastActivityReason;
        $this->serviceRequestId = $serviceRequestId;
    }


}