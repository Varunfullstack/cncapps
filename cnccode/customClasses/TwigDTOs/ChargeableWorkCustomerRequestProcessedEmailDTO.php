<?php

namespace CNCLTD\TwigDTOs;
class ChargeableWorkCustomerRequestProcessedEmailDTO
{
    private $requesteeName;
    private $status;
    private $additionalHoursRequested;
    private $urlService;
    private $serviceRequestId;

    /**
     * ChargeableWorkCustomerRequestProcessedEmailDTO constructor.
     * @param $requesteeName
     * @param $status
     * @param $additionalHoursRequested
     * @param $urlService
     * @param $serviceRequestId
     */
    public function __construct($requesteeName, $status, $additionalHoursRequested, $urlService, $serviceRequestId)
    {
        $this->requesteeName            = $requesteeName;
        $this->status                   = $status;
        $this->additionalHoursRequested = $additionalHoursRequested;
        $this->urlService               = $urlService;
        $this->serviceRequestId         = $serviceRequestId;
    }


    /**
     * @return mixed
     */
    public function getRequesteeName()
    {
        return $this->requesteeName;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getAdditionalHoursRequested()
    {
        return $this->additionalHoursRequested;
    }

    /**
     * @return mixed
     */
    public function getUrlService()
    {
        return $this->urlService;
    }

    /**
     * @return mixed
     */
    public function getServiceRequestId()
    {
        return $this->serviceRequestId;
    }


}