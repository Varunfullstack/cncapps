<?php

namespace CNCLTD\Exceptions;
class ChargeableWorkCustomerRequestAlreadyProcessedException extends \Exception
{

    /**
     * ChargeableWorkCustomerRequestNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct("This request has already been processed");
    }
}