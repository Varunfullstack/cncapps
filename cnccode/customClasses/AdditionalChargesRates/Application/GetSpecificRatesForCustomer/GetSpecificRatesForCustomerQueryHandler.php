<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetSpecificRatesForCustomer;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPricesGetter;
use CNCLTD\Shared\Domain\Bus\QueryHandler;

class GetSpecificRatesForCustomerQueryHandler implements QueryHandler
{
    /**
     * @var CustomerSpecificPricesGetter
     */
    private $customerPricesGetter;

    /**
     * GetRatesForCustomerQueryHandler constructor.
     */
    public function __construct(CustomerSpecificPricesGetter $customerPricesGetter)
    {

        $this->customerPricesGetter = $customerPricesGetter;
    }

    public function __invoke(GetSpecificRatesForCustomerQuery $query): GetSpecificRatesForCustomerResponse
    {
        $customerPrices = $this->customerPricesGetter->getSpecificPricesForCustomer($query->customerId()->value());
        return GetSpecificRatesForCustomerResponse::fromRawData($customerPrices);
    }

}