<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetAll;

use CNCLTD\AdditionalChargesRates\Application\GetOne\SpecificCustomerPriceResponse;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\SpecificCustomerPrice;
use JsonSerializable;
use function Lambdish\Phunctional\map;

class GetAllAdditionalChargeRateResponse implements JsonSerializable
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string|null
     */
    private $notes;
    /**
     * @var string
     */
    private $salePrice;
    /**
     * @var bool
     */
    private $canDelete;
    /**
     * @var int
     */
    private $timeBudgetMinutes;
    /**
     * @var array
     */
    private $specificCustomerPrices;


    /**
     * GetAllAdditionalChargeRateResponse constructor.
     */
    public function __construct(string $id,
                                string $description,
                                string $salePrice,
                                int $timeBudgetMinutes,
                                ?string $notes,
                                bool $canDelete,
                                array $specificCustomerPrices
    )
    {

        $this->id                     = $id;
        $this->description            = $description;
        $this->notes                  = $notes;
        $this->salePrice              = $salePrice;
        $this->canDelete              = $canDelete;
        $this->timeBudgetMinutes      = $timeBudgetMinutes;
        $this->specificCustomerPrices = map(
            function (SpecificCustomerPrice $customerPrice) {
                return new SpecificCustomerPriceResponse(
                    $customerPrice->customerId()->value(),
                    $customerPrice->salePrice()->value(),
                    $customerPrice->timeBudgetMinutes()->value()
                );
            },
            $specificCustomerPrices
        );
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return string
     */
    public function salePrice(): string
    {
        return $this->salePrice;
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->canDelete;
    }

    /**
     * @return int
     */
    public function timeBudgetMinutes(): int
    {
        return $this->timeBudgetMinutes;
    }

    /**
     * @return array
     */
    public function specificCustomerPrices(): array
    {
        return $this->specificCustomerPrices;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}