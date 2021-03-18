<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestAdditionalHoursRequested;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesteeId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesterId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use DateTimeImmutable;
use DBECallActivity;
use DBEContact;
use DBEProblem;
use DBEUser;

class CreateChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;
    /**
     * @var BUActivity
     */
    private $BUActivity;

    /**
     * CreateChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     * @param BUActivity $BUActivity
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository, BUActivity $BUActivity)
    {
        $this->repository = $repository;
        $this->BUActivity = $BUActivity;
    }

    /**
     * @param DBEProblem $serviceRequest
     * @param DBEUser $requester
     * @param int $additionalTimeRequested
     * @throws AdditionalHoursRequestedInvalidValueException
     */
    public function __invoke(DBEProblem $serviceRequest,
                             DBEUser $requester,
                             int $additionalTimeRequested
    )
    {

        $id               = $this->repository->getNextIdentity();
        $serviceRequestId = $serviceRequest->getValue(DBEProblem::problemID);
        $requestee        = new DBEContact($this);
        $requestee->getRow();
        $newRequest = ChargeableWorkCustomerRequest::create(
            $id,
            new DateTimeImmutable(),
            new ChargeableWorkCustomerRequestServiceRequestId($serviceRequestId),
            new ChargeableWorkCustomerRequestRequesteeId($serviceRequest->getValue(DBEProblem::contactID)),
            new ChargeableWorkCustomerRequestAdditionalHoursRequested($additionalTimeRequested),
            new ChargeableWorkCustomerRequestRequesterId($requester->getValue(DBEUser::userID))
        );
        $this->repository->save($newRequest);
        CommunicationService::sendExtraChargeableWorkRequestToContact($newRequest);
        $requesterFullName = "{$requester->getValue(DBEUser::firstName)} {$requester->getValue(DBEUser::lastName)}";
        $requesteeFullName = "{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)}";
        $contactActivity   = $this->BUActivity->addCustomerContactActivityToServiceRequest(
            $serviceRequest,
            "Person $requesterFullName sent a request for $additionalTimeRequested hours to $requesteeFullName",
            $requester
        );
        $contactActivity->setValue(DBECallActivity::awaitingCustomerResponseFlag, 'Y');
        $contactActivity->updateRow();
        $serviceRequest->setValue(DBEProblem::awaitingCustomerResponseFlag, 'Y');
        $serviceRequest->updateRow();
    }
}