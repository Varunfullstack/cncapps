<?php

namespace CNCLTD\ServiceRequest\Domain;

use Exception;

class CannotCloseServiceRequestWithoutFixedActivity extends Exception
{

    /**
     * CannotCLoseServiceRequestWithoutFixedActivity constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot close Service Request without fixed Activity');
    }
}