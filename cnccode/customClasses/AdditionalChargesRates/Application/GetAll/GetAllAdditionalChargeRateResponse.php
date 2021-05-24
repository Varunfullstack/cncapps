<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetAll;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;

class GetAllAdditionalChargeRateResponse implements \JsonSerializable
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
    private $salesPrice;


    /**
     * GetAllAdditionalChargeRateResponse constructor.
     */
    public function __construct(string $id,
                                string $description,
                                string $salesPrice,
                                ?string $notes
    )
    {

        $this->id          = $id;
        $this->description = $description;
        $this->notes       = $notes;
        $this->salesPrice  = $salesPrice;
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
    public function salesPrice(): string
    {
        return $this->salesPrice;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}