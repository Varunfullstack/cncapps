<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\shared\core\ValueObject;

class CancelReason implements ValueObject
{
    /** @var string */
    private $value;

    /**
     * CancelReason constructor.
     * @param string $value
     * @throws EmptyStringException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new EmptyStringException('Cancel reason cannot be an empty string');
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
        return $object->value === $this->value;
    }
}