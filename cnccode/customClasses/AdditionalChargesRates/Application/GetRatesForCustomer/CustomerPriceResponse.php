<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetRatesForCustomer;
use JsonSerializable;

class CustomerPriceResponse implements JsonSerializable
{
    private $description;
    private $salePrice;
    private $timeBudgetMinutes;

    /**
     * CustomerPriceResponse constructor.
     * @param $description
     * @param $salePrice
     * @param $timeBudgetMinutes
     */
    public function __construct($description, $salePrice, $timeBudgetMinutes)
    {
        $this->description       = $description;
        $this->salePrice         = $salePrice;
        $this->timeBudgetMinutes = $timeBudgetMinutes;
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

    /**
     * @return mixed
     */
    public function timeBudgetMinutes()
    {
        return $this->timeBudgetMinutes;
    }

}