<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\Shared\Domain\ValueObject;

class Notes implements ValueObject
{
    private $value;

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return $this->value == null;
    }

    public function isSame(ValueObject $object): bool
    {
        return $this->value === $object->value();
    }
}