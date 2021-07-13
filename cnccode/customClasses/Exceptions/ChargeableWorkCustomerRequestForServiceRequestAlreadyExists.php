<?php

namespace CNCLTD\Exceptions;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use Exception;

class ChargeableWorkCustomerRequestForServiceRequestAlreadyExists extends Exception
{

    /**
     * ChargeableWorkCustomerRequestForServiceRequestAlreadyExists constructor.
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     */
    public function __construct(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    )
    {
        $message = "Service Request {$serviceRequestId->value()} already has a pending Extra Chargeable Work request, cannot create more.";
        parent::__construct($message);
    }
}