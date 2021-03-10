<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\ValueObject;
use CNCLTD\ValueObjectCompare;
use CNCLTD\ValueObjectIsNull;

class Phone implements ValueObject
{
    use ValueObjectCompare;
    use ValueObjectIsNull;

    const MAX_LENGTH = 25;
    /** @var string */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws StringTooLongException
     * @throws EmptyStringException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new EmptyStringException('Phone');
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