<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\ValueObject;
use CNCLTD\ValueObjectCompare;
use CNCLTD\ValueObjectIsNull;

class Active implements ValueObject
{
    use ValueObjectCompare;
    use ValueObjectIsNull;

    /** @var bool */
    private $value;

    /**
     * constructor.
     * @param $value
     */
    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function value(): bool
    {
        return $this->value;
    }
}