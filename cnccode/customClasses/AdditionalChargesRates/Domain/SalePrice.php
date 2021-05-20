<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\shared\core\ValueObject;

class SalePrice implements ValueObject
{
    /** @var string */
    private $value;

    /**
     * SalePrice constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }


    public function value(): string
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return false;
    }

    public function isSame(ValueObject $object): bool
    {
        return $this->value() === $object->value();
    }
}