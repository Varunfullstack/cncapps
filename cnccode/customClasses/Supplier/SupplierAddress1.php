<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObjectCompare;

class SupplierAddress1
{
    use ValueObjectCompare;

    const MAX_LENGTH = 35;
    /** @var string */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws EmptyStringException
     * @throws StringTooLongException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new EmptyStringException("Address 1");
        }
        if (strlen($value) > self::MAX_LENGTH) {
            throw new StringTooLongException(self::MAX_LENGTH);
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

}