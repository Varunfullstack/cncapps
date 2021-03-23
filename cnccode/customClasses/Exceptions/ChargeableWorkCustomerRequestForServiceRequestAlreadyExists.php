<?php

namespace CNCLTD\Exceptions;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;

class ChargeableWorkCustomerRequestForServiceRequestAlreadyExists extends \Exception
{

    /**
     * ChargeableWorkCustomerRequestForServiceRequestAlreadyExists constructor.
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     */
    public function __construct(\CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    )
    {
        $message = "Service Request {$serviceRequestId->value()} already has a pending Extra Chargeable Work request, cannot create more.";
        parent::__construct($message);
    }
}