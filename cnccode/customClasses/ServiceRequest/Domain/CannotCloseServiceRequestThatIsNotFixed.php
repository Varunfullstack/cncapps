<?php

namespace CNCLTD\ServiceRequest\Domain;

use Exception;

class CannotCloseServiceRequestThatIsNotFixed extends Exception
{

    /**
     * CannotCloseServiceRequestThatIsNotFixed constructor.
     */
    public function __construct()
    {
        parent::__construct("Cannot close Service Request that is not fixed");
    }

}