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
    /** @var string */
    private $requesterFullName;
    /**
     * @var string
     */
    private $requestedAt;

    /**
     * PendingToProcessChargeableRequestInfoDTO constructor.
     * @param string $tokenId
     * @param int $serviceRequestId
     * @param string $serviceRequestEmailSummarySubject
     * @param string $contactName
     * @param int $additionalTimeRequested
     * @param string $reason
     * @param string $requesterFullName
     * @param string $requestedAt
     */
    public function __construct(string $tokenId,
                                int $serviceRequestId,
                                string $serviceRequestEmailSummarySubject,
                                string $contactName,
                                int $additionalTimeRequested,
                                string $reason,
                                string $requesterFullName,
                                string $requestedAt
    )
    {
        $this->tokenId                           = $tokenId;
        $this->serviceRequestId                  = $serviceRequestId;
        $this->serviceRequestEmailSummarySubject = $serviceRequestEmailSummarySubject;
        $this->contactName                       = $contactName;
        $this->additionalTimeRequested           = $additionalTimeRequested;
        $this->reason                            = $reason;
        $this->requesterFullName                 = $requesterFullName;
        $this->requestedAt                       = $requestedAt;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}