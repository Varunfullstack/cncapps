<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use DBEProblem;

class ClearPendingChargeableRequestsOnServiceRequestClosed
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;


    /**
     * ClearPendingChargeableRequestsOnServiceRequestClosed constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DBEProblem $serviceRequest)
    {
        $this->repository->deleteChargeableRequestsForServiceRequest(
            new ChargeableWorkCustomerRequestServiceRequestId($serviceRequest->getValue(DBEProblem::problemID))
        );
    }
}