<?php

namespace CNCLTD\Exceptions;
class InvalidEmailException extends \Exception
{

    /**
     * InvalidEmailException constructor.
     */
    public function __construct()
    {
        parent::__construct("Email is not valid");
    }
}