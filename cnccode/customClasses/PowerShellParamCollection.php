<?php


namespace CNCLTD;


use InvalidArgumentException;

class PowerShellParamCollection extends Collection
{
    public function current(): ?PowerShellParam
    {
        return parent::current();
    }

    public function offsetGet($offset): ?PowerShellParam
    {
        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof PowerShellParam) {
            throw new InvalidArgumentException("value must be instance of PowerShellParam.");
        }

        parent::offsetSet($offset, $value);
    }
}