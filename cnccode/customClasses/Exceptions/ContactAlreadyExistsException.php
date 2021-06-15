<?php

namespace CNCLTD\Exceptions;
class ContactAlreadyExistsException extends \Exception
{

    /**
     * ContactAlreadyExistsException constructor.
     */
    public function __construct()
    {
        parent::__construct('Contact already exists');
    }
}