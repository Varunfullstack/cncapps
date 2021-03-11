<?php

namespace CNCLTD\shared\core;
trait ValueObjectIsNull
{
    public function isNull(): bool
    {
        return $this->value === null;
    }
}