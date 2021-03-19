<?php

namespace CNCLTD;
trait ValueObjectIsNull
{
    public function isNull(): bool
    {
        return $this->value === null;
    }
}