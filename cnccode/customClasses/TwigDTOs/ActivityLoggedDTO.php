<?php


namespace CNCLTD\TwigDTOs;


class ActivityLoggedDTO
{
    public $contactFirstName;
    public $lastActivityCustomerNotes;
    public $engineerFullName;
    public $serviceRequestId;
    public $serviceRequestStatus;

    /**
     * ActivityLoggedDTO constructor.
     * @param $contactFirstName
     * @param $lastActivityCustomerNotes
     * @param $engineerFullName
     * @param $serviceRequestId
     * @param $serviceRequestStatus
     */
    public function __construct($contactFirstName,
                                $lastActivityCustomerNotes,
                                $engineerFullName,
                                $serviceRequestId,
                                $serviceRequestStatus
    )
    {
        $this->contactFirstName = $contactFirstName;
        $this->lastActivityCustomerNotes = $lastActivityCustomerNotes;
        $this->engineerFullName = $engineerFullName;
        $this->serviceRequestId = $serviceRequestId;
        $this->serviceRequestStatus = $serviceRequestStatus;
    }

}