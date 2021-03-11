<?php

namespace CNCLTD\shared\core;
trait ValueObjectCompare
{
    public function isSame(ValueObject $object): bool
    {
        return $this->value === $object->value();
    }
}