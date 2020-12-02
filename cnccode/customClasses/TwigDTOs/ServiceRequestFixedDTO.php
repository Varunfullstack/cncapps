<?php

namespace CNCLTD\TwigDTOs;
class ServiceRequestFixedDTO
{
    public $contactFirstName;
    public $firstActivityReason;
    public $lastActivityReason;
    public $serviceRequestId;
    public $feedbackToken;

    /**
     * ServiceRequestFixedDTO constructor.
     * @param $contactFirstName
     * @param $firstActivityReason
     * @param $lastActivityReason
     * @param $serviceRequestId
     * @param $feedbackToken
     */
    public function __construct($contactFirstName,
                                $firstActivityReason,
                                $lastActivityReason,
                                $serviceRequestId,
                                $feedbackToken
    )
    {
        $this->contactFirstName = $contactFirstName;
        $this->firstActivityReason = $firstActivityReason;
        $this->lastActivityReason = $lastActivityReason;
        $this->serviceRequestId = $serviceRequestId;
        $this->feedbackToken = $feedbackToken;
    }


}