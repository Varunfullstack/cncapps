<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\shared\Domain\ValueObject;
use CNCLTD\shared\Domain\ValueObjectCompare;
use CNCLTD\shared\Domain\ValueObjectIsNull;

class ChargeableWorkCustomerRequestTokenId implements ValueObject
{
    use ValueObjectCompare;
    use ValueObjectIsNull;

    /** @var string */
    private $value;

    /**
     * ChargeableWorkCustomerRequestId constructor.
     * @param string $value
     */
    public function __construct(string $value) { $this->value = $value; }


    public function value(): string
    {
        return $this->value;
    }

}