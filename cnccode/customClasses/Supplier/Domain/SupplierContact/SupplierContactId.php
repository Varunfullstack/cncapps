<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\Exceptions\InvalidIdException;
use CNCLTD\ValueObjectCompare;

class SupplierContactId
{
    use ValueObjectCompare;

    /** @var string */
    private $value;

    /**
     * constructor.
     * @param $value
     * @throws InvalidIdException
     */
    public function __construct(int $value)
    {
        if (!$value) {
            throw new InvalidIdException();
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}