<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\ValueObjectCompare;

class Active
{
    use ValueObjectCompare;

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