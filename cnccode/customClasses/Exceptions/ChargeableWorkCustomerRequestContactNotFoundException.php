<?php

namespace CNCLTD\Exceptions;
use Exception;

class ChargeableWorkCustomerRequestContactNotFoundException extends Exception
{

    /**
     * ChargeableWorkCustomerRequestContactNotFoundException constructor.
     * @param int $requesteeId
     */
    public function __construct(int $requesteeId)
    {
        parent::__construct("The contact with id $requesteeId could not be found!");
    }
}