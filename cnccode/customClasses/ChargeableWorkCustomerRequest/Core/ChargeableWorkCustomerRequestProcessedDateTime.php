<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;

use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;
use CNCLTD\shared\core\ValueObjectIsNull;
use DateTimeInterface;

class ChargeableWorkCustomerRequestProcessedDateTime implements ValueObject
{
    use ValueObjectIsNull;
    use ValueObjectCompare;

    /** @var DateTimeInterface|null */
    private $value;

    /**
     * ChargeableWorkCustomerRequestProcessedDateTime constructor.
     * @param DateTimeInterface|null $value
     */
    public function __construct(?DateTimeInterface $value) { $this->value = $value; }

    public function value(): ?DateTimeInterface
    {
        return $this->value;
    }


}