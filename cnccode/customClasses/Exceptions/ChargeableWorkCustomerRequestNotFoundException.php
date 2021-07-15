<?php

namespace CNCLTD\Exceptions;
use Exception;

class ChargeableWorkCustomerRequestNotFoundException extends Exception
{

    /**
     * ChargeableWorkCustomerRequestNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct("ChargeableWorkCustomerRequest Not Found");
    }
}