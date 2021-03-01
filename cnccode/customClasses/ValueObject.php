<?php

namespace CNCLTD;
interface ValueObject
{
    public function isNull(): bool;

    public function isSame(ValueObject $object): bool;

    public function value();
}