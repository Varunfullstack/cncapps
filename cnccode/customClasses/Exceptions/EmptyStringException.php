<?php

namespace CNCLTD\Exceptions;
class EmptyStringException extends \Exception
{

    /**
     * EmptyStringException constructor.
     */
    public function __construct()
    {
        parent::__construct('Empty string is forbidden', 583);
    }
}