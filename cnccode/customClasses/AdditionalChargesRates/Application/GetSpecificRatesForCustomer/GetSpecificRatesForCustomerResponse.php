<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetSpecificRatesForCustomer;

use CNCLTD\Shared\Domain\Bus\Response;
use JsonSerializable;

class GetSpecificRatesForCustomerResponse implements Response, JsonSerializable
{
    private $prices = [];

    public static function fromRawData($customerPrices): self
    {
        $instance = new self();
        foreach ($customerPrices as $customerPriceRaw) {
            $instance->prices[] = new SpecificCustomerPriceResponse(
                $customerPriceRaw['id'],
                $customerPriceRaw['description'],
                $customerPriceRaw['salePrice'],
                $customerPriceRaw['timeBudgetMinutes']
            );
        }
        return $instance;
    }

    public function jsonSerialize(): array
    {
        return $this->prices();
    }

    /**
     * @return array
     */
    public function prices(): array
    {
        return $this->prices;
    }

}