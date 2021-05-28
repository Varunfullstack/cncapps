<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\Business\BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestAdditionalHoursRequested;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestReason;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesteeId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRequesterId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestContactNotFoundException;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestContactNotMainException;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
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
     * @param int $requesteeId
     * @throws AdditionalHoursRequestedInvalidValueException
     * @throws ChargeableWorkCustomerRequestContactNotFoundException
     * @throws ChargeableWorkCustomerRequestContactNotMainException
     * @throws ColumnOutOfRangeException
     */
    public function __invoke(DBEProblem $serviceRequest,
                             DBEUser $requester,
                             int $additionalTimeRequested,
                             string $reason,
                             int $requesteeId
    )
    {

        $serviceRequestId = $serviceRequest->getValue(DBEProblem::problemID);
        $id               = $this->repository->getNextIdentity();
        $requestee        = new DBEContact($this);
        if (!$requestee->getRow($requesteeId)) {
            throw new ChargeableWorkCustomerRequestContactNotFoundException($requesteeId);
        }
        if ($requestee->getValue(DBEContact::supportLevel) !== DBEContact::supportLevelMain) {
            throw new ChargeableWorkCustomerRequestContactNotMainException($requesteeId);
        }
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