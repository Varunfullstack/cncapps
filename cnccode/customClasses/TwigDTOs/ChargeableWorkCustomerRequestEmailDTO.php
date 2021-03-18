<?php

namespace CNCLTD\TwigDTOs;
class ChargeableWorkCustomerRequestEmailDTO
{
    public $approvalURL;
    public $denialURL;
    public $extraTimeRequested;
    public $requesteeFirstName;
    public $serviceRequestId;
    public $emailSubjectSummary;

    /**
     * ChargeableWorkCustomerRequestEmailDTO constructor.
     * @param $approvalURL
     * @param $denialURL
     * @param $extraTimeRequested
     * @param $requesteeFirstName
     * @param $serviceRequestId
     * @param $emailSubjectSummary
     */
    public function __construct($approvalURL,
                                $denialURL,
                                $extraTimeRequested,
                                $requesteeFirstName,
                                $serviceRequestId,
                                $emailSubjectSummary
    )
    {
        $this->approvalURL         = $approvalURL;
        $this->denialURL           = $denialURL;
        $this->extraTimeRequested  = $extraTimeRequested;
        $this->requesteeFirstName  = $requesteeFirstName;
        $this->serviceRequestId    = $serviceRequestId;
        $this->emailSubjectSummary = $emailSubjectSummary;
    }
}