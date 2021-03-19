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
     * @param $acceptURL
     * @param $rejectURL
     * @param $extraTimeRequested
     * @param $requesteeFirstName
     * @param $serviceRequestId
     * @param $htmlReason
     * @param $requesterFullName
     * @param $hourlyRate
     */
    public function __construct($acceptURL,
                                $rejectURL,
                                $extraTimeRequested,
                                $requesteeFirstName,
                                $serviceRequestId,
                                $htmlReason,
                                $requesterFullName,
                                $hourlyRate
    )
    {
        $this->approvalURL        = $acceptURL;
        $this->denialURL          = $rejectURL;
        $this->extraTimeRequested = $extraTimeRequested;
        $this->requesteeFirstName = $requesteeFirstName;
        $this->serviceRequestId   = $serviceRequestId;
        $this->htmlReason         = $htmlReason;
        $this->requesterFullName  = $requesterFullName;
        $this->hourlyRate         = $hourlyRate;
    }
}