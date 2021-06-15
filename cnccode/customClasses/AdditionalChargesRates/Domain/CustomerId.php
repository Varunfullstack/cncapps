<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\shared\Domain\ValueObject;
use CNCLTD\shared\Domain\ValueObjectCompare;

class CustomerId implements ValueObject
{

    use ValueObjectCompare;

    /** @var int */
    private $value;

    public function __construct(int $value) { $this->value = $value; }

    public function value(): int
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return false;
    }
}