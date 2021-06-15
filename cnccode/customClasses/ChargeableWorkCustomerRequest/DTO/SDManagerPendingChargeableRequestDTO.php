<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\DTO;
class SDManagerPendingChargeableRequestDTO implements \JsonSerializable
{
    private $id;
    private $serviceRequestId;
    private $customerName;
    private $requesteeName;
    private $emailSubjectSummary;
    private $reason;
    private $createdAt;
    private $additionalHoursRequested;
    private $requesterName;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}