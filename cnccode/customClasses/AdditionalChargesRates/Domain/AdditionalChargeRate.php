<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
class AdditionalChargeRate
{
    /** @var Description */
    private $description;
    /** @var Notes */
    private $notes;
    /** @var SalePrice */
    private $salePrice;
    /** @var AllowCustomerSpecificPrices */
    private $allowCustomerSpecificPrices;
    /**
     * @var array
     */
    private $specificCustomerPrices;
    /**
     * @var AdditionalChargeRateId
     */
    private $id;
    private $updatedSpecifics = [];

    public function __construct(AdditionalChargeRateId $id,
                                Description $description,
                                Notes $notes,
                                SalePrice $salePrice,
                                AllowCustomerSpecificPrices $allowCustomerSpecificPrices
    )
    {
        $this->id                          = $id;
        $this->description                 = $description;
        $this->notes                       = $notes;
        $this->salePrice                   = $salePrice;
        $this->allowCustomerSpecificPrices = $allowCustomerSpecificPrices;
        $this->specificCustomerPrices      = [];
    }

    public static function create(Description $description,
                                  Notes $notes,
                                  SalePrice $salePrice,
                                  AllowCustomerSpecificPrices $allowCustomerSpecificPrices
    ): AdditionalChargeRate
    {
        return new self(
            AdditionalChargeRateId::create(), $description, $notes, $salePrice, $allowCustomerSpecificPrices
        );
    }

    /**
     * @return AdditionalChargeRateId
     */
    public function id(): AdditionalChargeRateId
    {
        return $this->id;
    }

    /**
     * @return Description
     */
    public function description(): Description
    {
        return $this->description;
    }

    /**
     * @return Notes
     */
    public function notes(): Notes
    {
        return $this->notes;
    }

    /**
     * @return SalePrice
     */
    public function salePrice(): SalePrice
    {
        return $this->salePrice;
    }

    public function isCustomerSpecificPriceAllowed(): bool
    {
        return $this->allowCustomerSpecificPrices->value();
    }

    public function allowCustomerSpecificPrices()
    {
        $this->allowCustomerSpecificPrices = new AllowCustomerSpecificPrices(true);
    }

    public function disallowCustomerSpecificPrices()
    {
        $this->allowCustomerSpecificPrices = new AllowCustomerSpecificPrices(false);
        $this->specificCustomerPrices = [];
    }

    public function addCustomerPrice(CustomerId $customerId, SalePrice $salePrice)
    {
        $newPrice = new SpecificCustomerPrice($customerId, $salePrice);
        foreach ($this->specificCustomerPrices as $key => $specificCustomerPrice) {
            if ($specificCustomerPrice->customerId->isSame($customerId)) {
                $this->specificCustomerPrices[$key] = $newPrice;
                return;
            }
        }
        $this->specificCustomerPrices[] = $newPrice;
    }

    public function removeCustomerPrice(CustomerId $customerId)
    {
        $newPrice = new SpecificCustomerPrice($customerId, $salePrice);
        foreach ($this->specificCustomerPrices as $key => $specificCustomerPrice) {
            if ($specificCustomerPrice->customerId->isSame($customerId)) {
                $this->specificCustomerPrices[$key] = $newPrice;
                return;
            }
        }
    }
}