<?php

namespace CNCLTD\Supplier;

use CNCLTD\ValueObjectCompare;

class SupplierPaymentMethodId
{
    use ValueObjectCompare;

    /**
     * @var int
     */
    private $value;

    /**
     * SupplierId constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->value = $id;
    }

    public function value(): int
    {
        return $this->value;
    }
}