<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\DTO;

use JsonSerializable;

class PendingToProcessChargeableRequestInfoDTO implements JsonSerializable
{
    /** @var string */
    public $tokenId;
    /** @var int */
    public $serviceRequestId;
    /** @var string */
    public $serviceRequestEmailSummarySubject;
    /** @var string */
    public $contactName;
    /** @var int */
    public $additionalTimeRequested;
    /** @var string */
    public $reason;

    /**
     * PendingToProcessChargeableRequestInfoDTO constructor.
     * @param string $tokenId
     * @param int $serviceRequestId
     * @param string $serviceRequestEmailSummarySubject
     * @param string $contactName
     * @param int $additionalTimeRequested
     * @param string $reason
     */
    public function __construct(string $tokenId,
                                int $serviceRequestId,
                                string $serviceRequestEmailSummarySubject,
                                string $contactName,
                                int $additionalTimeRequested,
                                string $reason
    )
    {
        $this->tokenId                           = $tokenId;
        $this->serviceRequestId                  = $serviceRequestId;
        $this->serviceRequestEmailSummarySubject = $serviceRequestEmailSummarySubject;
        $this->contactName                       = $contactName;
        $this->additionalTimeRequested           = $additionalTimeRequested;
        $this->reason                            = $reason;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}