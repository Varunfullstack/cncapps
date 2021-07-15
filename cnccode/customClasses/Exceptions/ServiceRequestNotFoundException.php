<?php

namespace CNCLTD\Exceptions;
use Exception;

class ServiceRequestNotFoundException extends Exception
{

    /**
     * ServiceRequestNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct("Service Request not found");
    }
}