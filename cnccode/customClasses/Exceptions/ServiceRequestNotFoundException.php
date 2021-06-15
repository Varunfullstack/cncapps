<?php

namespace CNCLTD\Exceptions;
class ServiceRequestNotFoundException extends \Exception
{

    /**
     * ServiceRequestNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct("Service Request not found");
    }
}