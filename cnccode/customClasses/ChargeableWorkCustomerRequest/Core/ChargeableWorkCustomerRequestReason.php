<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;
use CNCLTD\shared\core\ValueObjectIsNull;

class ChargeableWorkCustomerRequestReason implements ValueObject
{
    use ValueObjectIsNull;
    use ValueObjectCompare;

    /** @var string */
    private $value;

    /**
     * ChargeableWorkCustomerRequestReason constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {


        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }


}