<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
class SpecificCustomerPrice
{
    /** @var CustomerId */
    private $customerId;
    /** @var SalePrice */
    private $salePrice;

    public function __construct(CustomerId $customerId,
                                SalePrice $salePrice
    )
    {
        $this->customerId = $customerId;
        $this->salePrice  = $salePrice;
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
}