<?php

namespace CNCLTD;
trait ValueObjectCompare
{
    public function isSame(ValueObject $object): bool
    {
        return $this->value() === $object->value();
    }
}