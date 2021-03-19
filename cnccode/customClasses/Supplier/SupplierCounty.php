<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObjectCompare;

class SupplierCounty
{
    use ValueObjectCompare;

    const MAX_LENGTH = 25;
    /** @var string */
    private $value;

    /**
     * SupplierCounty constructor.
     * @param $value
     * @throws EmptyStringException
     * @throws StringTooLongException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new EmptyStringException("County");
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