<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestAdditionalTimeRequested;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestProcessedDateTime;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesteeId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesterId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\CommunicationService\CommunicationService;

class CreateChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;
    /**
     * @var \BUActivity
     */
    private $BUActivity;

    /**
     * CreateChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     * @param \BUActivity $BUActivity
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository, \BUActivity $BUActivity)
    {
        $this->repository = $repository;
        $this->BUActivity = $BUActivity;
    }

    public function __invoke(\DBEProblem $serviceRequest,
                             \DBEUser $requester,
                             int $additionalTimeRequested,
                             \DBEContact $requestee
    )
    {

        $id = $this->repository->getNextIdentity();
        $serviceRequestId = $serviceRequest->getValue(\DBEProblem::problemID);
        $newRequest = ChargeableWorkCustomerRequest::create(
            $id,
            new \DateTimeImmutable(),
            new ChargeableWorkCustomerRequestServiceRequestId($serviceRequestId),
            new ChargeableWorkCustomerRequestRequesteeId($requestee->getValue(\DBEContact::contactID)),
            new ChargeableWorkCustomerRequestAdditionalTimeRequested($additionalTimeRequested),
            new ChargeableWorkCustomerRequestProcessedDateTime(null),
            new ChargeableWorkCustomerRequestRequesterId($requester->getValue(\DBEUser::userID))
        );
        $this->repository->save($newRequest);
        CommunicationService::sendExtraChargeableWorkRequestToContact($newRequest);
        $requesterFullName = "{$requester->getValue(\DBEUser::firstName)} {$requester->getValue(\DBEUser::lastName)}";
        $requesteeFullName = "{$requestee->getValue(\DBEContact::firstName)} {$requestee->getValue(\DBEContact::lastName)}";
        $this->BUActivity->logOperationalActivity(
            $serviceRequestId,
            "Person $requesterFullName sent a request for $additionalTimeRequested hours to $requesteeFullName"
        );

    }
}