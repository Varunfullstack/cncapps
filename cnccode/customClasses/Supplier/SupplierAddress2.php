<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObjectCompare;

class SupplierAddress2
{
    use ValueObjectCompare;

    const MAX_LENGTH = 35;
    /** @var string|null */
    private $value;

    /**
     * SupplierAddress2 constructor.
     * @param $value
     * @throws EmptyStringException
     * @throws StringTooLongException
     */
    public function __construct(?string $value)
    {
        if ($value !== null && !$value) {
            throw new EmptyStringException("Address 2");
        }
        if (strlen($value) > self::MAX_LENGTH) {
            throw new StringTooLongException(self::MAX_LENGTH);
        }
        $this->value = $value;
    }

    public function value(): ?string
    {
        return $this->value;
    }

}