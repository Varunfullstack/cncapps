<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOneSpecificRateForCustomer;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPriceGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPricesGetter;
use CNCLTD\Shared\Domain\Bus\QueryHandler;

class GetOneSpecificRateForCustomerQueryHandler implements QueryHandler
{
    /**
     * @var CustomerSpecificPriceGetter
     */
    private $customerPricesGetter;

    /**
     * GetRatesForCustomerQueryHandler constructor.
     */
    public function __construct(CustomerSpecificPriceGetter $customerPriceGetter)
    {

        $this->customerPricesGetter = $customerPriceGetter;
    }

    public function __invoke(GetOneSpecificRateForCustomerQuery $query): GetOneSpecificRateForCustomerResponse
    {
        $customerPrice = $this->customerPricesGetter->getSpecificPriceForCustomer(
            $query->customerId()->value(),
            $query->additionalChargeRateId()->value()
        );
        return GetOneSpecificRateForCustomerResponse::fromRawData($customerPrice);
    }

}