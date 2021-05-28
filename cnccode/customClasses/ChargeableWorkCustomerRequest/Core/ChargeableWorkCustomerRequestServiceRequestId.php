<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\shared\Domain\ValueObject;
use CNCLTD\shared\Domain\ValueObjectCompare;
use CNCLTD\shared\Domain\ValueObjectIsNull;

class ChargeableWorkCustomerRequestServiceRequestId implements ValueObject
{
    use ValueObjectIsNull;
    use ValueObjectCompare;

    /**
     * @var int
     */
    private $value;

    /**
     * ChargeableWorkCustomerRequestServiceRequestId constructor.
     * @param int $value
     */
    public function __construct(int $value) { $this->value = $value; }

    public function value(): int
    {
        return $this->value;
    }


}