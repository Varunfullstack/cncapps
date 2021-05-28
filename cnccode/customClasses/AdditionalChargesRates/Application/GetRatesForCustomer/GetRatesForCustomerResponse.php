<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetRatesForCustomer;

use CNCLTD\Shared\Domain\Bus\Response;

class GetRatesForCustomerResponse implements Response, \JsonSerializable
{
    private $prices = [];

    public static function fromRawData($customerPrices): self
    {
        $instance = new self();
        foreach ($customerPrices as $customerPriceRaw) {
            $instance->prices[] = new CustomerPriceResponse(
                $customerPriceRaw['description'], $customerPriceRaw['salePrice'], $customerPriceRaw['timeBudgetMinutes']
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