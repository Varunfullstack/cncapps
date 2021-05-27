<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetAll;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\Shared\Domain\Bus\Response;

class GetAllAdditionalChargeRatesResponse implements Response
{

    private $additionalChargesRates = [];

    /**
     * GetAllAdditionalChargeRatesResponse constructor.
     * @param AdditionalChargeRate[] $additionalChargesRates
     */
    public function __construct(array $additionalChargesRates)
    {
        foreach ($additionalChargesRates as $additionalChargesRate) {
            $this->additionalChargesRates[] = new GetAllAdditionalChargeRateResponse(
                $additionalChargesRate->id()->value(),
                $additionalChargesRate->description()->value(),
                $additionalChargesRate->salePrice()->value(),
                $additionalChargesRate->notes()->value(),
                $additionalChargesRate->canDelete()
            );
        }
    }

    /**
     * @return array
     */
    public function additionalChargesRates(): array
    {
        return $this->additionalChargesRates;
    }
}