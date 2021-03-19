<?php

namespace CNCLTD\TwigDTOs;
class ChargeableWorkCustomerRequestEmailDTO
{
    public $approvalURL;
    public $denialURL;
    public $extraTimeRequested;
    public $requesteeFirstName;
    public $serviceRequestId;
    public $htmlReason;
    public $requesterFullName;
    public $hourlyRate;

    /**
     * ChargeableWorkCustomerRequestEmailDTO constructor.
     * @param $approvalURL
     * @param $denialURL
     * @param $extraTimeRequested
     * @param $requesteeFirstName
     * @param $serviceRequestId
     * @param $htmlReason
     * @param $requesterFullName
     * @param $hourlyRate
     */
    public function __construct($approvalURL,
                                $denialURL,
                                $extraTimeRequested,
                                $requesteeFirstName,
                                $serviceRequestId,
                                $htmlReason,
                                $requesterFullName,
                                $hourlyRate
    )
    {
        $this->approvalURL        = $approvalURL;
        $this->denialURL          = $denialURL;
        $this->extraTimeRequested = $extraTimeRequested;
        $this->requesteeFirstName = $requesteeFirstName;
        $this->serviceRequestId   = $serviceRequestId;
        $this->htmlReason         = $htmlReason;
        $this->requesterFullName  = $requesterFullName;
        $this->hourlyRate         = $hourlyRate;
    }
}