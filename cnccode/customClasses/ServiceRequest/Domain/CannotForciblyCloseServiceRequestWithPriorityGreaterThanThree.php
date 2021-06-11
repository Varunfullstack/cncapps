<?php

namespace CNCLTD\ServiceRequest\Domain;

use Exception;

class CannotForciblyCloseServiceRequestWithPriorityGreaterThanThree extends Exception
{

    /**
     * CannotForciblyCloseServiceRequestWithPriorityGreaterThanThree constructor.
     */
    public function __construct()
    {
        parent::__construct("Cannot forcibly close service request with priority greater than three ");
    }
}