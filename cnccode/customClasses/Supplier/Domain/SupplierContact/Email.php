<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\InvalidEmailException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObject;
use CNCLTD\ValueObjectCompare;
use CNCLTD\ValueObjectIsNull;

class Email implements ValueObject
{
    use ValueObjectCompare;
    use ValueObjectIsNull;

    const MAX_LENGTH = 60;
    /** @var string */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws StringTooLongException
     * @throws EmptyStringException
     * @throws InvalidEmailException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new EmptyStringException("Email");
        }
        if (strlen($value) > self::MAX_LENGTH) {
            throw new StringTooLongException(self::MAX_LENGTH);
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}