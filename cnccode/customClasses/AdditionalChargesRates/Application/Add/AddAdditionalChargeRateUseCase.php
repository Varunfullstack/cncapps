<?php


namespace CNCLTD\AdditionalChargesRates\Application\Add;


use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;

class AddAdditionalChargeRateUseCase
{
    /**
     * @var AdditionalChargeRateRepository
     */
    private $repository;

    /**
     * AddAdditionalChargeRate constructor.
     */
    public function __construct(AdditionalChargeRateRepository $repository) {
        $this->repository = $repository;

    }

    public function __invoke(AddAdditionalChargeRateRequest $addAdditionalChargeRateRequest)
    {
        // TODO: Implement __invoke() method.
    }


}