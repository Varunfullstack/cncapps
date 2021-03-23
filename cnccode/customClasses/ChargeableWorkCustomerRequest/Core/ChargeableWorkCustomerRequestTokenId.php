<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;
use CNCLTD\shared\core\ValueObjectIsNull;

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