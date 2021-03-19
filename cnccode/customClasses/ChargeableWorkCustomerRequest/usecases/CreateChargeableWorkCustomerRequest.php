<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestAdditionalHoursRequested;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestReason;
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
     * @param string $reason
     * @throws AdditionalHoursRequestedInvalidValueException
     */
    public function __invoke(DBEProblem $serviceRequest,
                             DBEUser $requester,
                             int $additionalTimeRequested,
                             string $reason
    )
    {

        $serviceRequestId = $serviceRequest->getValue(DBEProblem::problemID);
        $id          = $this->repository->getNextIdentity();
        $requestee   = new DBEContact($this);
        $requesteeId = $serviceRequest->getValue(DBEProblem::contactID);
        $requestee->getRow($requesteeId);
        $newRequest = ChargeableWorkCustomerRequest::create(
            $id,
            new DateTimeImmutable(),
            new ChargeableWorkCustomerRequestServiceRequestId($serviceRequestId),
            new ChargeableWorkCustomerRequestRequesteeId($requesteeId),
            new ChargeableWorkCustomerRequestAdditionalHoursRequested($additionalTimeRequested),
            new ChargeableWorkCustomerRequestRequesterId($requester->getValue(DBEUser::userID)),
            new ChargeableWorkCustomerRequestReason($reason)
        );
        $this->repository->save($newRequest);
        CommunicationService::sendExtraChargeableWorkRequestToContact($newRequest);
        $requesterFullName = "{$requester->getValue(DBEUser::firstName)} {$requester->getValue(DBEUser::lastName)}";
        $requesteeFullName = "{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)}";
        $contactActivity   = $this->BUActivity->addCustomerContactActivityToServiceRequest(
            $serviceRequest,
            "<p>$requesterFullName sent a request for $additionalTimeRequested hour(s) to $requesteeFullName</p><br/>{$reason}",
            $requester
        );
        $contactActivity->setValue(DBECallActivity::awaitingCustomerResponseFlag, 'Y');
        $contactActivity->updateRow();
        $serviceRequest->setValue(DBEProblem::awaitingCustomerResponseFlag, 'Y');
        $serviceRequest->updateRow();
    }
}