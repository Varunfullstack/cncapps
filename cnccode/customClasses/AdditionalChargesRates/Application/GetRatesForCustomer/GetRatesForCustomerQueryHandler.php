<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetRatesForCustomer;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use CNCLTD\Shared\Domain\Bus\QueryHandler;

class GetRatesForCustomerQueryHandler implements QueryHandler
{
    /**
     * @var CustomerPricesGetter
     */
    private $customerPricesGetter;

    /**
     * GetRatesForCustomerQueryHandler constructor.
     */
    public function __construct(CustomerPricesGetter $customerPricesGetter)
    {

        $this->customerPricesGetter = $customerPricesGetter;
    }

    public function __invoke(GetRatesForCustomerQuery $query): GetRatesForCustomerResponse
    {
        $customerPrices = $this->customerPricesGetter->getPricesForCustomer($query->customerId()->value());
        return GetRatesForCustomerResponse::fromRawData($customerPrices);
    }

}