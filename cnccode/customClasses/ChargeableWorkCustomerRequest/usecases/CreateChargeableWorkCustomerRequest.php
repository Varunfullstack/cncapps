<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestAdditionalTimeRequested;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestProcessedDateTime;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesteeId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesterId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;

class CreateChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;

    /**
     * CreateChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(\DBEProblem $serviceRequest,
                             \DBEUser $requester,
                             int $additionalTimeRequested,
                             \DBEContact $requestee
    )
    {

        $id         = $this->repository->getNextIdentity();
        $newRequest = ChargeableWorkCustomerRequest::create(
            $id,
            new \DateTimeImmutable(),
            new ChargeableWorkCustomerRequestServiceRequestId($serviceRequest->getValue(\DBEProblem::problemID)),
            new ChargeableWorkCustomerRequestRequesteeId($requestee->getValue(\DBEContact::contactID)),
            new ChargeableWorkCustomerRequestAdditionalTimeRequested($additionalTimeRequested),
            new ChargeableWorkCustomerRequestProcessedDateTime(null),
            new ChargeableWorkCustomerRequestRequesterId($requester->getValue(\DBEUser::userID))
        );
        $this->repository->save($newRequest);

    }
}