<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\infra;
class ChargeableWorkCustomerRequestMySQLDTO
{
    private $id;
    private $createdAt;
    private $serviceRequestId;
    private $requesteeId;
    private $additionalHoursRequested;
    private $requesterId;
    private $reason;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getServiceRequestId()
    {
        return $this->serviceRequestId;
    }

    /**
     * @return mixed
     */
    public function getRequesteeId()
    {
        return $this->requesteeId;
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
    public function getRequesterId()
    {
        return $this->requesterId;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}