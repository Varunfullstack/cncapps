<?php

namespace CNCLTD\AdditionalChargesRates\Application\Update;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\AdditionalChargesRates\Domain\Description;
use CNCLTD\AdditionalChargesRates\Domain\Notes;
use CNCLTD\AdditionalChargesRates\Domain\SalePrice;
use CNCLTD\AdditionalChargesRates\Domain\SpecificCustomerPrice;
use CNCLTD\AdditionalChargesRates\Infra\Persistence\AdditionalChargeRatePDORepository;
use function Lambdish\Phunctional\map;

class UpdateAdditionalChargeRateUseCase
{
    /**
     * @var AdditionalChargeRatePDORepository
     */
    private $additionalChargeRateRepository;

    /**
     * UpdateAdditionalChargeRateUseCase constructor.
     * @param AdditionalChargeRatePDORepository $additionalChargeRateRepository
     */
    public function __construct(AdditionalChargeRatePDORepository $additionalChargeRateRepository)
    {
        $this->additionalChargeRateRepository = $additionalChargeRateRepository;
    }

    public function __invoke(UpdateAdditionalChargeRateRequest $additionalChargeRateRequest)
    {
        $existingAdditionalChargeRequest = $this->additionalChargeRateRepository->ofId(
            AdditionalChargeRateId::fromNative($additionalChargeRateRequest->id())
        );
        $existingAdditionalChargeRequest->changeDescription(
            new Description($additionalChargeRateRequest->description())
        );
        $existingAdditionalChargeRequest->changeNotes(new Notes($additionalChargeRateRequest->notes()));
        $existingAdditionalChargeRequest->changeSalePrice(new SalePrice($additionalChargeRateRequest->salePrice()));
        $existingAdditionalChargeRequest->setCustomerPrices(
            map(
                function ($customerPriceArray) {
                    return new SpecificCustomerPrice(
                        new CustomerId($customerPriceArray['customerId']),
                        new SalePrice($customerPriceArray['salePrice'])
                    );
                },
                $additionalChargeRateRequest->specificCustomerPrices()
            )
        );
        $this->additionalChargeRateRepository->save($existingAdditionalChargeRequest);
    }


}