<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOneSpecificRateForCustomer;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\Shared\Domain\Bus\Query;

class GetOneSpecificRateForCustomerQuery implements Query
{
    /**
     * @var CustomerId
     */
    private $customerId;
    /**
     * @var AdditionalChargeRateId
     */
    private $additionalChargeRateId;

    /**
     * GetRatesForCustomerQuery constructor.
     */
    public function __construct(CustomerId $customerId, AdditionalChargeRateId $additionalChargeRateId)
    {
        $this->customerId             = $customerId;
        $this->additionalChargeRateId = $additionalChargeRateId;
    }

    /**
     * @return CustomerId
     */
    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return AdditionalChargeRateId
     */
    public function additionalChargeRateId(): AdditionalChargeRateId
    {
        return $this->additionalChargeRateId;
    }


}