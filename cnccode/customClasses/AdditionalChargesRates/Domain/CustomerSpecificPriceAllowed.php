<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;

class CustomerSpecificPriceAllowed implements ValueObject
{

    use ValueObjectCompare;

    /** @var boolean */
    private $value;

    /**
     * CustomerSpecificPriceAllowed constructor.
     * @param bool $value
     */
    public function __construct(bool $value) { $this->value = $value; }

    public function value(): bool
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return false;
    }

}