<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\shared\core\ValueObject;

class Description implements ValueObject
{

    private $value;

    /**
     * @throws EmptyStringException
     */
    public function __construct($value)
    {
        if (!$value) {
            throw new EmptyStringException('Additional charge rate description');
        }
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