<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use DateTimeImmutable;
use DBEUser;

class CancelPendingChargeableWorkCustomerRequest
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
     * CancelPendingChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     * @param BUActivity $BUActivity
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository, BUActivity $BUActivity)
    {
        $this->repository = $repository;
        $this->BUActivity = $BUActivity;
    }

    public function __invoke(ChargeableWorkCustomerRequestTokenId $id, DBEUser $currentUser)
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        $serviceRequestId    = $request->getServiceRequestId()->value();
        $currentUserFullName = "{$currentUser->getValue(DBEUser::firstName)} {$currentUser->getValue(DBEUser::lastName)}";
        $currentDateTime     = new DateTimeImmutable();
        $description         = "The request for extra work has been cancelled by {$currentUserFullName} at  {$currentDateTime->format('H:i d/m/Y')}";
        $this->BUActivity->logOperationalActivity($serviceRequestId, $description);
        $this->repository->delete($request);
    }
}