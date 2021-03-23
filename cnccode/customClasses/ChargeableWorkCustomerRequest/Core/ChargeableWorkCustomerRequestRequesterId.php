<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;
use CNCLTD\shared\core\ValueObjectIsNull;

class ChargeableWorkCustomerRequestRequesterId implements ValueObject
{

    use ValueObjectCompare;
    use ValueObjectIsNull;

    /** @var int */
    private $value;

    /**
     * ChargeableWorkCustomerRequestRequesterId constructor.
     * @param int $value
     */
    public function __construct(int $value) { $this->value = $value; }

    public function value(): int
    {
        return $this->value;
    }


}