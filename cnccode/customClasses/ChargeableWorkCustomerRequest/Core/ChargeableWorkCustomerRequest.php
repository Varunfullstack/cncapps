<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;
class ChargeableWorkCustomerRequest
{
    /** @var ChargeableWorkCustomerRequestTokenId */
    private $id;
    /** @var \DateTimeImmutable */
    private $createdAt;
    /** @var ChargeableWorkCustomerRequestServiceRequestId */
    private $serviceRequestId;
    /** @var ChargeableWorkCustomerRequestRequesteeId */
    private $requesteeId;
    /** @var ChargeableWorkCustomerRequestAdditionalTimeRequested */
    private $additionalTimeRequested;
    /** @var ChargeableWorkCustomerRequestProcessedDateTime */
    private $processedDateTime;
    /** @var ChargeableWorkCustomerRequestRequesterId */
    private $requesterId;

    /**
     * ChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @param \DateTimeImmutable $createdAt
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     * @param ChargeableWorkCustomerRequestRequesteeId $requestee
     * @param ChargeableWorkCustomerRequestAdditionalTimeRequested $additionalTimeRequested
     * @param ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime
     * @param ChargeableWorkCustomerRequestRequesterId $requesterId
     */
    private function __construct(ChargeableWorkCustomerRequestTokenId $id,
                                 \DateTimeImmutable $createdAt,
                                 ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                 ChargeableWorkCustomerRequestRequesteeId $requestee,
                                 ChargeableWorkCustomerRequestAdditionalTimeRequested $additionalTimeRequested,
                                 ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime,
                                 ChargeableWorkCustomerRequestRequesterId $requesterId
    )
    {
        $this->id                      = $id;
        $this->createdAt               = $createdAt;
        $this->serviceRequestId        = $serviceRequestId;
        $this->requesteeId               = $requestee;
        $this->additionalTimeRequested = $additionalTimeRequested;
        $this->processedDateTime       = $processedDateTime;
        $this->requesterId             = $requesterId;
    }


    public static function create(ChargeableWorkCustomerRequestTokenId $id,
                                  \DateTimeImmutable $createdAt,
                                  ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                  ChargeableWorkCustomerRequestRequesteeId $requesteeId,
                                  ChargeableWorkCustomerRequestAdditionalTimeRequested $additionalTimeRequested,
                                  ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime,
                                  ChargeableWorkCustomerRequestRequesterId $requesterId
    ): ChargeableWorkCustomerRequest
    {
        return new self(
            $id, $createdAt, $serviceRequestId, $requesteeId, $additionalTimeRequested, $processedDateTime, $requesterId
        );
    }

    /**
     * @return ChargeableWorkCustomerRequestTokenId
     */
    public function getId(): ChargeableWorkCustomerRequestTokenId
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return ChargeableWorkCustomerRequestServiceRequestId
     */
    public function getServiceRequestId(): ChargeableWorkCustomerRequestServiceRequestId
    {
        return $this->serviceRequestId;
    }

    /**
     * @return ChargeableWorkCustomerRequestRequesteeId
     */
    public function getRequesteeId(): ChargeableWorkCustomerRequestRequesteeId
    {
        return $this->requesteeId;
    }

    /**
     * @return ChargeableWorkCustomerRequestAdditionalTimeRequested
     */
    public function getAdditionalTimeRequested(): ChargeableWorkCustomerRequestAdditionalTimeRequested
    {
        return $this->additionalTimeRequested;
    }

    /**
     * @return ChargeableWorkCustomerRequestProcessedDateTime
     */
    public function getProcessedDateTime(): ChargeableWorkCustomerRequestProcessedDateTime
    {
        return $this->processedDateTime;
    }

    /**
     * @return ChargeableWorkCustomerRequestRequesterId
     */
    public function getRequesterId(): ChargeableWorkCustomerRequestRequesterId
    {
        return $this->requesterId;
    }
}