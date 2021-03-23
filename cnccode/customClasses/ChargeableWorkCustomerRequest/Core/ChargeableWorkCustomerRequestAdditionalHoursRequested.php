<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;
use CNCLTD\shared\core\ValueObjectIsNull;

class ChargeableWorkCustomerRequestAdditionalHoursRequested implements ValueObject
{
    use ValueObjectIsNull;
    use ValueObjectCompare;

    /** @var int */
    private $value;

    /**
     * ChargeableWorkCustomerRequestAdditionalTimeRequested constructor.
     * @param int $value
     * @throws AdditionalHoursRequestedInvalidValueException
     */
    public function __construct(int $value)
    {
        if (!in_array($value, [1, 2, 3, 4], true)) {
            throw new AdditionalHoursRequestedInvalidValueException($value);
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }


}