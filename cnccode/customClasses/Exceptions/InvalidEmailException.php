<?php

namespace CNCLTD\Exceptions;
use Exception;

class InvalidEmailException extends Exception
{

    /**
     * InvalidEmailException constructor.
     * @param $invalidEmail
     */
    public function __construct($invalidEmail)
    {
        parent::__construct("Email is not valid: '{$invalidEmail}'");
    }
}