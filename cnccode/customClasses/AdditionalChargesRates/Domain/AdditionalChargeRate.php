<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
class AdditionalChargeRate
{
    /** @var Description */
    protected $description;
    /** @var Notes */
    protected $notes;
    /** @var SalePrice */
    protected $salePrice;
    /** @var CustomerSpecificPriceAllowed */
    protected $customerSpecificPriceAllowed;
    /**
     * @var SpecificCustomerPrice[]
     */
    protected $specificCustomerPrices;
    /**
     * @var AdditionalChargeRateId
     */
    protected $id;

    public function __construct(AdditionalChargeRateId $id,
                                Description $description,
                                Notes $notes,
                                SalePrice $salePrice,
                                CustomerSpecificPriceAllowed $customerSpecificPriceAllowed
    )
    {
        $this->id                           = $id;
        $this->description                  = $description;
        $this->notes                        = $notes;
        $this->salePrice                    = $salePrice;
        $this->customerSpecificPriceAllowed = $customerSpecificPriceAllowed;
        $this->specificCustomerPrices       = [];
    }

    public static function create(Description $description,
                                  Notes $notes,
                                  SalePrice $salePrice,
                                  CustomerSpecificPriceAllowed $customerSpecificPriceAllowed
    ): AdditionalChargeRate
    {
        return new self(
            AdditionalChargeRateId::create(), $description, $notes, $salePrice, $customerSpecificPriceAllowed
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
        return $this->customerSpecificPriceAllowed->value();
    }

    public function allowCustomerSpecificPrice()
    {
        $this->customerSpecificPriceAllowed = new CustomerSpecificPriceAllowed(true);
    }

    public function disallowCustomerSpecificPrice()
    {
        $this->customerSpecificPriceAllowed = new CustomerSpecificPriceAllowed(false);
        $this->specificCustomerPrices       = [];
    }

    public function addCustomerPrice(CustomerId $customerId, SalePrice $salePrice)
    {
        $newPrice = new SpecificCustomerPrice($customerId, $salePrice);
        foreach ($this->specificCustomerPrices as $key => $specificCustomerPrice) {
            if ($specificCustomerPrice->customerId()->isSame($customerId)) {
                $this->specificCustomerPrices[$key] = $newPrice;
                return;
            }
        }
        $this->specificCustomerPrices[] = $newPrice;
    }

    public function removeCustomerPrice(CustomerId $customerId)
    {
        foreach ($this->specificCustomerPrices as $key => $specificCustomerPrice) {
            if ($specificCustomerPrice->customerId()->isSame($customerId)) {
                unset($this->specificCustomerPrices[$key]);
                return;
            }
        }
    }

    /**
     * @return array
     */
    public function specificCustomerPrices(): array
    {
        return $this->specificCustomerPrices;
    }

}