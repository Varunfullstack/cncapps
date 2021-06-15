<?php

namespace CNCLTD\Exceptions;
class ChargeableWorkCustomerRequestContactNotFoundException extends \Exception
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