<?php

namespace CNCLTD\Exceptions;
class InvalidIdException extends \Exception
{

    /**
     * InvalidIdException constructor.
     */
    public function __construct()
    {
        parent::__construct('Invalid Id value');
    }
}