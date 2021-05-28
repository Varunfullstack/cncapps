<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetRatesForCustomer;

use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\Shared\Domain\Bus\Query;

class GetRatesForCustomerQuery implements Query
{
    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * GetRatesForCustomerQuery constructor.
     */
    public function __construct(CustomerId $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerId
     */
    public function customerId(): CustomerId
    {
        return $this->customerId;
    }
}