<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
class SpecificCustomerPrice
{
    /** @var CustomerId */
    private $customerId;
    /** @var SalePrice */
    private $salePrice;

    /** @var TimeBudgetMinutes */
    private $timeBudgetMinutes;

    public function __construct(CustomerId $customerId,
                                SalePrice $salePrice,
                                TimeBudgetMinutes $timeBudgetMinutes
    )
    {
        $this->customerId        = $customerId;
        $this->salePrice         = $salePrice;
        $this->timeBudgetMinutes = $timeBudgetMinutes;
    }

    /**
     * @return CustomerId
     */
    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return SalePrice
     */
    public function salePrice(): SalePrice
    {
        return $this->salePrice;
    }

    /**
     * @return TimeBudgetMinutes
     */
    public function timeBudgetMinutes(): TimeBudgetMinutes
    {
        return $this->timeBudgetMinutes;
    }


}