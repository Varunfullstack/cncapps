<?php

namespace CNCLTD\ServiceRequest\Domain;

use Exception;

class CannotCloseServiceRequestOnHold extends Exception
{

    /**
     * CannotCloseServiceRequestOnHold constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot close Service Request that is on Hold for QA');
    }
}