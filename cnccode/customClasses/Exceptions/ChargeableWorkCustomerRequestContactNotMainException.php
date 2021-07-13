<?php

namespace CNCLTD\Exceptions;
use Exception;

class ChargeableWorkCustomerRequestContactNotMainException extends Exception
{

    /**
     * ChargeableWorkCustomerRequestContactNotMainException constructor.
     * @param int $requesteeId
     */
    public function __construct(int $requesteeId)
    {
        parent::__construct("The contact with id $requesteeId is not a Main contact!!");
    }
}