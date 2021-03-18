<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLDTO;
use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use DateTimeImmutable;

class ChargeableWorkCustomerRequest
{
    /** @var ChargeableWorkCustomerRequestTokenId */
    private $id;
    /** @var DateTimeImmutable */
    private $createdAt;
    /** @var ChargeableWorkCustomerRequestServiceRequestId */
    private $serviceRequestId;
    /** @var ChargeableWorkCustomerRequestRequesteeId */
    private $requesteeId;
    /** @var ChargeableWorkCustomerRequestAdditionalHoursRequested */
    private $additionalHoursRequested;
    /** @var ChargeableWorkCustomerRequestReason */
    private $reason;
    /** @var ChargeableWorkCustomerRequestRequesterId */
    private $requesterId;

    /**
     * ChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @param DateTimeImmutable $createdAt
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     * @param ChargeableWorkCustomerRequestRequesteeId $requestee
     * @param ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested
     * @param ChargeableWorkCustomerRequestRequesterId $requesterId
     * @param ChargeableWorkCustomerRequestReason $reason
     */
    private function __construct(ChargeableWorkCustomerRequestTokenId $id,
                                 DateTimeImmutable $createdAt,
                                 ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                 ChargeableWorkCustomerRequestRequesteeId $requestee,
                                 ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested,
                                 ChargeableWorkCustomerRequestRequesterId $requesterId,
                                 ChargeableWorkCustomerRequestReason $reason
    )
    {
        $this->id                       = $id;
        $this->createdAt                = $createdAt;
        $this->serviceRequestId         = $serviceRequestId;
        $this->requesteeId              = $requestee;
        $this->additionalHoursRequested = $additionalTimeRequested;
        $this->requesterId              = $requesterId;
        $this->reason                   = $reason;
    }


    public static function create(ChargeableWorkCustomerRequestTokenId $id,
                                  DateTimeImmutable $createdAt,
                                  ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId,
                                  ChargeableWorkCustomerRequestRequesteeId $requesteeId,
                                  ChargeableWorkCustomerRequestAdditionalHoursRequested $additionalTimeRequested,
                                  ChargeableWorkCustomerRequestRequesterId $requesterId,
                                  ChargeableWorkCustomerRequestReason $reason
    ): ChargeableWorkCustomerRequest
    {
        return new self(
            $id, $createdAt, $serviceRequestId, $requesteeId, $additionalTimeRequested, $requesterId, $reason
        );
    }

    /**
     * @param ChargeableWorkCustomerRequestMySQLDTO $dto
     * @return ChargeableWorkCustomerRequest
     * @throws AdditionalHoursRequestedInvalidValueException
     */
    public static function fromMySQLDTO(ChargeableWorkCustomerRequestMySQLDTO $dto): ChargeableWorkCustomerRequest
    {
        $id                      = new ChargeableWorkCustomerRequestTokenId($dto->getId());
        $createdAt               = DateTimeImmutable::createFromFormat(DATE_MYSQL_DATETIME, $dto->getCreatedAt());
        $serviceRequestId        = new ChargeableWorkCustomerRequestServiceRequestId($dto->getServiceRequestId());
        $requesteeId             = new ChargeableWorkCustomerRequestRequesteeId($dto->getRequesteeId());
        $additionalTimeRequested = new ChargeableWorkCustomerRequestAdditionalHoursRequested(
            $dto->getAdditionalTimeRequested()
        );
        $requesterId             = new ChargeableWorkCustomerRequestRequesterId($dto->getRequesterId());
        $reason                  = new ChargeableWorkCustomerRequestReason($dto->getReason());
        return self::create(
            $id,
            $createdAt,
            $serviceRequestId,
            $requesteeId,
            $additionalTimeRequested,
            $requesterId,
            $reason
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
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
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
     * @return ChargeableWorkCustomerRequestRequesterId
     */
    public function getRequesterId(): ChargeableWorkCustomerRequestRequesterId
    {
        return $this->requesterId;
    }

    /**
     * @return ChargeableWorkCustomerRequestReason
     */
    public function getReason(): ChargeableWorkCustomerRequestReason
    {
        return $this->reason;
    }
}