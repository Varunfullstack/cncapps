<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLDTO;

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
    /** @var ChargeableWorkCustomerRequestAdditionalHoursRequested */
    private $additionalHoursRequested;
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
     * @param ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested
     * @param ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime
     * @param ChargeableWorkCustomerRequestRequesterId $requesterId
     */
    private function __construct(ChargeableWorkCustomerRequestTokenId $id,
                                 \DateTimeImmutable $createdAt,
                                 ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                 ChargeableWorkCustomerRequestRequesteeId $requestee,
                                 ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested,
                                 ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime,
                                 ChargeableWorkCustomerRequestRequesterId $requesterId
    )
    {
        $this->id                      = $id;
        $this->createdAt               = $createdAt;
        $this->serviceRequestId        = $serviceRequestId;
        $this->requesteeId             = $requestee;
        $this->additionalHoursRequested = $additionalTimeRequested;
        $this->processedDateTime       = $processedDateTime;
        $this->requesterId             = $requesterId;
    }


    public static function create(ChargeableWorkCustomerRequestTokenId $id,
                                  \DateTimeImmutable $createdAt,
                                  ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                  ChargeableWorkCustomerRequestRequesteeId $requesteeId,
                                  ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested,
                                  ChargeableWorkCustomerRequestProcessedDateTime $processedDateTime,
                                  ChargeableWorkCustomerRequestRequesterId $requesterId
    ): ChargeableWorkCustomerRequest
    {
        return new self(
            $id, $createdAt, $serviceRequestId, $requesteeId, $additionalTimeRequested, $processedDateTime, $requesterId
        );
    }

    public static function fromMySQLDTO(ChargeableWorkCustomerRequestMySQLDTO $dto): ChargeableWorkCustomerRequest
    {
        $id                      = new ChargeableWorkCustomerRequestTokenId($dto->getId());
        $createdAt               = \DateTimeImmutable::createFromFormat(DATE_MYSQL_DATETIME, $dto->getCreatedAt());
        $serviceRequestId        = new ChargeableWorkCustomerRequestServiceRequestId($dto->getServiceRequestId());
        $requesteeId             = new ChargeableWorkCustomerRequestRequesteeId($dto->getRequesteeId());
        $additionalTimeRequested = new ChargeableWorkCustomerRequestAdditionalHoursRequested(
            $dto->getAdditionalTimeRequested()
        );
        $processedDateTime       = new ChargeableWorkCustomerRequestProcessedDateTime($dto->getProcessedDateTime());
        $requesterId             = new ChargeableWorkCustomerRequestRequesterId($dto->getRequesterId());
        return self::create(
            $id,
            $createdAt,
            $serviceRequestId,
            $requesteeId,
            $additionalTimeRequested,
            $processedDateTime,
            $requesterId
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
     * @return ChargeableWorkCustomerRequestAdditionalHoursRequested
     */
    public function getAdditionalHoursRequested(): ChargeableWorkCustomerRequestAdditionalHoursRequested
    {
        return $this->additionalHoursRequested;
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

    public function approve()
    {
        $this->processedDateTime = new ChargeableWorkCustomerRequestProcessedDateTime(new \DateTimeImmutable());
    }
}