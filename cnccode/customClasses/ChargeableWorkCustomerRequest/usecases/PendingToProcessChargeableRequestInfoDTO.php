<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;
class PendingToProcessChargeableRequestInfoDTO implements \JsonSerializable
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

    /**
     * PendingToProcessChargeableRequestInfoDTO constructor.
     * @param string $tokenId
     * @param int $serviceRequestId
     * @param string $serviceRequestEmailSummarySubject
     * @param string $contactName
     * @param int $additionalTimeRequested
     */
    public function __construct(string $tokenId,
                                int $serviceRequestId,
                                string $serviceRequestEmailSummarySubject,
                                string $contactName,
                                int $additionalTimeRequested
    )
    {
        $this->tokenId                           = $tokenId;
        $this->serviceRequestId                  = $serviceRequestId;
        $this->serviceRequestEmailSummarySubject = $serviceRequestEmailSummarySubject;
        $this->contactName                       = $contactName;
        $this->additionalTimeRequested           = $additionalTimeRequested;
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}