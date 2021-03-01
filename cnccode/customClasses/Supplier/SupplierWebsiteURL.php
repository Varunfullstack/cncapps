<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\Exceptions\URLNotValidException;
use CNCLTD\ValueObjectCompare;

class SupplierWebsiteURL
{
    use ValueObjectCompare;

    const MAX_LENGTH = 100;
    /** @var string|null */
    private $value;

    /**
     * SupplierAddress1 constructor.
     * @param $value
     * @throws EmptyStringException
     * @throws StringTooLongException
     * @throws URLNotValidException
     */
    public function __construct(?string $value)
    {
        if ($value !== null && !$value) {
            throw new EmptyStringException();
        }
        if (strlen($value) > self::MAX_LENGTH) {
            throw new StringTooLongException(self::MAX_LENGTH);
        }
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new URLNotValidException();
        }
        $this->value = $value;
    }

    public function value(): ?string
    {
        return $this->value;
    }

}