<?php

namespace CNCLTD\Shared\Domain;
trait ValueObjectCompare
{
    public function isSame(ValueObject $object): bool
    {
        return $this->value() === $object->value();
    }
}