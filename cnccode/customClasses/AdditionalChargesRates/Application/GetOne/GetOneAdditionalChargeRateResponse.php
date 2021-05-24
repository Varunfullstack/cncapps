<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOne;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\SpecificCustomerPrice;
use CNCLTD\Shared\Domain\Bus\Response;
use function Lambdish\Phunctional\map;

class GetOneAdditionalChargeRateResponse implements \JsonSerializable, Response
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
     * @var array
     */
    private $customerSpecificPrices;


    /**
     * GetAllAdditionalChargeRateResponse constructor.
     */
    private function __construct(string $id,
                                 string $description,
                                 string $salePrice,
                                 ?string $notes,
                                 array $customerSpecificPrices
    )
    {

        $this->id                     = $id;
        $this->description            = $description;
        $this->notes                  = $notes;
        $this->salePrice              = $salePrice;
        $this->customerSpecificPrices = $customerSpecificPrices;
    }

    public static function fromDomain(AdditionalChargeRate $additionalChargeRate): self
    {
        return new self(
            $additionalChargeRate->id()->value(),
            $additionalChargeRate->description()->value(),
            $additionalChargeRate->salePrice()->value(),
            $additionalChargeRate->notes()->value(),
            map(
                function (SpecificCustomerPrice $specificCustomerPrice) {
                    return new SpecificCustomerPriceResponse(
                        $specificCustomerPrice->customerId()->value(), $specificCustomerPrice->salePrice()->value()
                    );
                },
                $additionalChargeRate->specificCustomerPrices()
            )
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
     * @return array
     */
    public function customerSpecificPrices(): array
    {
        return $this->customerSpecificPrices;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}