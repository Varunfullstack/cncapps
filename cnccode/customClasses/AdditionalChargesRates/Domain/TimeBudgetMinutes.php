<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\Shared\Domain\ValueObject;

class TimeBudgetMinutes implements ValueObject
{

    /** @var bool */
    private $value;

    /**
     * @throws TimeBudgetMinutesCannotBeNegativeException
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new TimeBudgetMinutesCannotBeNegativeException();
        }
        $this->value = $value;
    }


    public function value(): int
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