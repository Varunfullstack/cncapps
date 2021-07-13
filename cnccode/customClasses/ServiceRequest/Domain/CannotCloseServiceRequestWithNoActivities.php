<?php

namespace CNCLTD\ServiceRequest\Domain;
use Exception;

class CannotCloseServiceRequestWithNoActivities extends Exception
{

    /**
     * CannotCloseServiceRequestWithNoActivities constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot close Service Request without any activities');
    }
}