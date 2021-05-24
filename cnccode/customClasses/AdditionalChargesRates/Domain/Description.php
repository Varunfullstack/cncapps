<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\shared\Domain\ValueObject;

class Description implements ValueObject
{

    const MAXLENGTH = 100;
    private $value;

    /**
     * @throws EmptyStringException
     * @throws StringTooLongException
     */
    public function __construct($value)
    {
        if (!$value) {
            throw new EmptyStringException('Additional charge rate description');
        }
        if (strlen($value) > self::MAXLENGTH) {
            throw new StringTooLongException(self::MAXLENGTH);
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