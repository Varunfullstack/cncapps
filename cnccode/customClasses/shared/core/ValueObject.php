<?php

namespace CNCLTD\shared\core;
interface ValueObject
{
    public function value();

    public function isNull(): bool;

    public function isSame(self $object): bool;
}