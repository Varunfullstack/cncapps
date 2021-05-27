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
    /**
     * @var SpecificCustomerPrice[]
     */
    protected $specificCustomerPrices;
    /**
     * @var AdditionalChargeRateId
     */
    protected $id;

    /** @var TimeBudgetMinutes */
    protected $timeBudgetMinutes;

    public function __construct(AdditionalChargeRateId $id,
                                Description $description,
                                Notes $notes,
                                SalePrice $salePrice,
                                TimeBudgetMinutes $timeBudget
    )
    {
        $this->id                     = $id;
        $this->description            = $description;
        $this->notes                  = $notes;
        $this->salePrice              = $salePrice;
        $this->specificCustomerPrices = [];
        $this->timeBudgetMinutes      = $timeBudget;
    }

    public static function create(Description $description,
                                  Notes $notes,
                                  SalePrice $salePrice,
                                  TimeBudgetMinutes $timeBudget
    ): AdditionalChargeRate
    {
        return new self(
            AdditionalChargeRateId::create(), $description, $notes, $salePrice, $timeBudget
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

    public function timeBudgetMinutes(): TimeBudgetMinutes
    {
        return $this->timeBudgetMinutes;
    }

    public function addCustomerPrice(CustomerId $customerId, SalePrice $salePrice, TimeBudgetMinutes $timeBudgetMinutes)
    {
        $newPrice = new SpecificCustomerPrice($customerId, $salePrice, $timeBudgetMinutes);
        foreach ($this->specificCustomerPrices as $key => $specificCustomerPrice) {
            if ($specificCustomerPrice->customerId()->isSame($customerId)) {
                $this->specificCustomerPrices[$key] = $newPrice;
                return;
            }
        }
        $this->specificCustomerPrices[] = $newPrice;
    }

    public function setCustomerPrices($customerPrices)
    {
        $this->specificCustomerPrices = [];
        /** @var SpecificCustomerPrice $customerPrice */
        foreach ($customerPrices as $customerPrice) {
            $this->addCustomerPrice(
                $customerPrice->customerId(),
                $customerPrice->salePrice(),
                $customerPrice->timeBudgetMinutes()
            );
        }
    }

    /**
     * @return array
     */
    public function specificCustomerPrices(): array
    {
        return $this->specificCustomerPrices;
    }

    public function changeSalePrice(SalePrice $salePrice)
    {
        $this->salePrice = $salePrice;
    }

    public function changeDescription(Description $description)
    {
        $this->description = $description;
    }

    public function changeNotes(Notes $notes)
    {
        $this->notes = $notes;
    }

    public function canDelete(): bool
    {
        return !count($this->specificCustomerPrices);
    }

}