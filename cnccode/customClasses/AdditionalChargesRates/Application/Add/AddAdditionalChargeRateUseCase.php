<?php

namespace CNCLTD\AdditionalChargesRates\Application\Add;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\AdditionalChargesRates\Domain\Description;
use CNCLTD\AdditionalChargesRates\Domain\Notes;
use CNCLTD\AdditionalChargesRates\Domain\SalePrice;

class AddAdditionalChargeRateUseCase
{
    /**
     * @var AdditionalChargeRateRepository
     */
    private $repository;

    /**
     * AddAdditionalChargeRate constructor.
     */
    public function __construct(AdditionalChargeRateRepository $repository)
    {
        $this->repository = $repository;

    }

    public function __invoke(AddAdditionalChargeRateRequest $addAdditionalChargeRateRequest)
    {
        $newAdditionalChargeRate = AdditionalChargeRate::create(
            new Description($addAdditionalChargeRateRequest->description()),
            new Notes($addAdditionalChargeRateRequest->notes()),
            new SalePrice($addAdditionalChargeRateRequest->salePrice())
        );
        foreach ($addAdditionalChargeRateRequest->specificCustomerPrices() as $specificCustomerPrice) {
            $newAdditionalChargeRate->addCustomerPrice(
                new CustomerId($specificCustomerPrice['customerId']),
                new SalePrice($specificCustomerPrice['salePrice'])
            );
        }
        $this->repository->save($newAdditionalChargeRate);
    }
}