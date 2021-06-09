<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOne;
class SpecificCustomerPriceResponse implements \JsonSerializable
{
    private $customerId;
    private $salePrice;
    private $timeBudgetMinutes;

    /**
     * SpecificCustomerPriceResponse constructor.
     * @param $customerId
     * @param $salePrice
     */
    public function __construct($customerId, $salePrice, $timeBudgetMinutes)
    {
        $this->customerId        = $customerId;
        $this->salePrice         = $salePrice;
        $this->timeBudgetMinutes = $timeBudgetMinutes;
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

    /**
     * @return mixed
     */
    public function timeBudgetMinutes()
    {
        return $this->timeBudgetMinutes;
    }


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}