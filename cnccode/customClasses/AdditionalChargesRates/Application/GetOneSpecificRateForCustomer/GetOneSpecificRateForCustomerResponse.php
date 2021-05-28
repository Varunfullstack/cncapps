<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOneSpecificRateForCustomer;

use CNCLTD\Shared\Domain\Bus\Response;

class GetOneSpecificRateForCustomerResponse implements Response, \JsonSerializable
{
    private $description;
    private $salePrice;
    private $timeBudgetMinutes;
    private $id;

    /**
     * CustomerPriceResponse constructor.
     * @param $description
     * @param $salePrice
     * @param $timeBudgetMinutes
     */
    private function __construct($id, $description, $salePrice, $timeBudgetMinutes)
    {
        $this->description       = $description;
        $this->salePrice         = $salePrice;
        $this->timeBudgetMinutes = $timeBudgetMinutes;
        $this->id                = $id;
    }

    public static function fromRawData($customerPriceRaw): self
    {
        return new self(
            $customerPriceRaw['id'],
            $customerPriceRaw['description'],
            $customerPriceRaw['salePrice'],
            $customerPriceRaw['timeBudgetMinutes']
        );
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

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

}