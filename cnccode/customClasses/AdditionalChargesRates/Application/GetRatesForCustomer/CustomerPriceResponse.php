<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetRatesForCustomer;
class CustomerPriceResponse implements \JsonSerializable
{
    private $description;
    private $salePrice;

    /**
     * CustomerPriceResponse constructor.
     * @param $description
     * @param $salePrice
     */
    public function __construct($description, $salePrice)
    {
        $this->description = $description;
        $this->salePrice   = $salePrice;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function salePrice()
    {
        return $this->salePrice;
    }

}