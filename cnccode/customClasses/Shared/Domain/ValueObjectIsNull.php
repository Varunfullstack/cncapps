<?php

namespace CNCLTD\Shared\Domain;
trait ValueObjectIsNull
{
    public function isNull(): bool
    {
        return $this->value === null;
    }
}