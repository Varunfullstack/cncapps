<?php

namespace CNCLTD\AdditionalChargesRates\Application\Delete;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateNotFoundException;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;
use CNCLTD\AdditionalChargesRates\Domain\CannotDeleteAdditionalChargeRateException;

class DeleteAdditionalChargeRateUseCase
{
    /**
     * @var AdditionalChargeRateRepository
     */
    private $repository;

    /**
     * DeleteAdditionalChargeRateUseCase constructor.
     */
    public function __construct(AdditionalChargeRateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param AdditionalChargeRateId $additionalChargeRateId
     * @throws AdditionalChargeRateNotFoundException
     * @throws CannotDeleteAdditionalChargeRateException
     */
    public function __invoke(AdditionalChargeRateId $additionalChargeRateId)
    {
        $additionalChargeRate = $this->repository->ofId($additionalChargeRateId);
        if (!$additionalChargeRate) {
            throw new AdditionalChargeRateNotFoundException($additionalChargeRateId);
        }
        if (!$additionalChargeRate->canDelete()) {
            throw new CannotDeleteAdditionalChargeRateException($additionalChargeRateId);
        }
        $this->repository->delete($additionalChargeRate);
    }
}