<?php

namespace CNCLTD\Exceptions;
use Exception;

class InvalidIdException extends Exception
{

    /**
     * InvalidIdException constructor.
     */
    public function __construct()
    {
        parent::__construct('Invalid Id value');
    }
}