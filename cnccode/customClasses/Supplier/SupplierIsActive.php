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
    public function getValue(): bool
    {
        return $this->value;
    }

}