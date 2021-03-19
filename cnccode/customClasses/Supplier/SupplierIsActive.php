<?php

namespace CNCLTD\Supplier;
class SupplierIsActive
{

    private $value;

    /**
     * SupplierIsActive constructor.
     * @param $value
     */
    public function __construct(bool $value) { $this->value = $value; }

    /**
     * @return bool
     */
    public function value(): bool
    {
        return $this->value;
    }

}