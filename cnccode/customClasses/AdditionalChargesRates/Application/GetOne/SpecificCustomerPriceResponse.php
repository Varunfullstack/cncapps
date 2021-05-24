<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOne;
class SpecificCustomerPriceResponse implements \JsonSerializable
{
    private $customerId;
    private $salePrice;

    /**
     * SpecificCustomerPriceResponse constructor.
     * @param $customerId
     * @param $salePrice
     */
    public function __construct($customerId, $salePrice)
    {
        $this->customerId = $customerId;
        $this->salePrice  = $salePrice;
    }

    /**
     * @return mixed
     */
    public function customerId()
    {
        return $this->customerId;
    }

    /**
     * @return mixed
     */
    public function salePrice()
    {
        return $this->salePrice;
    }


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}