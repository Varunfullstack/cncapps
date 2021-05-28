<?php

namespace CNCLTD\Shared\Domain;
interface ValueObject
{
    public function value();

    public function isNull(): bool;

    public function isSame(self $object): bool;
}