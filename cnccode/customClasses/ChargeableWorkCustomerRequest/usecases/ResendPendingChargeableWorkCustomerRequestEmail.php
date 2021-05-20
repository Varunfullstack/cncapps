<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\Business\BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use DateTimeImmutable;
use DBEContact;

class ResendPendingChargeableWorkCustomerRequestEmail
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
     * @var DBEContact
     */
    private $DBEContact;

    /**
     * CancelPendingChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     * @param BUActivity $BUActivity
     * @param DBEContact $DBEContact
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository,
                                BUActivity $BUActivity,
                                DBEContact $DBEContact
    )
    {
        $this->repository = $repository;
        $this->BUActivity = $BUActivity;
        $this->DBEContact = $DBEContact;
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @throws ChargeableWorkCustomerRequestNotFoundException
     */
    public function __invoke(ChargeableWorkCustomerRequestTokenId $id)
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        $serviceRequestId = $request->getServiceRequestId()->value();
        $requesteeId      = $request->getRequesteeId()->value();
        $requestee        = $this->DBEContact;
        $requestee->getRow($requesteeId);
        $currentDateTime   = new DateTimeImmutable();
        $requesteeFullName = "{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)}";
        $description       = "The request for extra work has been resent to {$requesteeFullName} at {$currentDateTime->format('H:i d/m/Y')}";
        $this->BUActivity->logOperationalActivity($serviceRequestId, $description);
        CommunicationService::sendExtraChargeableWorkRequestToContact($request);
    }
}