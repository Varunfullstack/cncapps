<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use CNCLTD\shared\core\ValueObject;
use CNCLTD\shared\core\ValueObjectCompare;

class AdditionalChargeRateId implements ValueObject
{

    use ValueObjectCompare;

    /** @var string */
    private $value;

    private function __construct(string $value) { $this->value = $value; }

    public function value(): string
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return false;
    }

    /**
     * @throws InvalidAdditionalChargeRageIdValue
     */
    public static function fromNative(string $id): AdditionalChargeRateId
    {
        if (!\Ramsey\Uuid\Uuid::isValid($id)) {
            throw new InvalidAdditionalChargeRageIdValue($id);
        }
        return new self($id);
    }

    public static function create(): AdditionalChargeRateId
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4();
        return new self($uuid);
    }

}