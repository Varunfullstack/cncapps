<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObjectCompare;

class Position
{
    use ValueObjectCompare;

    const MAX_LENGTH = 50;
    /** @var string */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws StringTooLongException
     */
    public function __construct(string $value)
    {
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