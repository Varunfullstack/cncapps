<?php

namespace CNCLTD\Supplier;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObjectCompare;

class SupplierPhone
{
    use ValueObjectCompare;
    const MAX_LENGTH = 20;
    /** @var string|null */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws EmptyStringException
     * @throws StringTooLongException
     */
    public function __construct(?string $value)
    {
        if ($value !== null && !$value) {
            throw new EmptyStringException();
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