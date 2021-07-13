<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\Business\BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Data\DBEJProblem;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;
use DateTimeImmutable;
use DateTimeInterface;
use DBEContact;
use DBEProblem;
use DBEUser;
use Exception;

class RejectPendingChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;
    private $requestee;
    private $requester;

    /**
     * RejectPendingChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @param string|null $comments
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws ServiceRequestNotFoundException
     */
    public function __invoke(ChargeableWorkCustomerRequestTokenId $id, ?string $comments)
    {
        $request           = $this->getRequest($id);
        $serviceRequest    = $this->getServiceRequest($request);
        $requestApprovedAt = new DateTimeImmutable();
        $this->logCustomerContactActivity($request, $requestApprovedAt, $serviceRequest, $comments);
        $this->updateServiceRequest($serviceRequest);
        $this->sendEmailToEngineer($request);
        $this->deleteChargeableRequest($request);
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @return ChargeableWorkCustomerRequest
     * @throws ChargeableWorkCustomerRequestNotFoundException
     */
    private function getRequest(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        return $request;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return DBEJProblem
     * @throws ServiceRequestNotFoundException
     */
    private function getServiceRequest(ChargeableWorkCustomerRequest $request): DBEJProblem
    {
        $dbeProblem       = new DBEJProblem($this);
        $serviceRequestId = $request->getServiceRequestId()->value();
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new ServiceRequestNotFoundException();
        }
        return $dbeProblem;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return DBEContact
     */
    private function getRequestee(ChargeableWorkCustomerRequest $request): DBEContact
    {
        if (!$this->requestee) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($request->getRequesteeId()->value());
            $this->requestee = $dbeContact;
        }
        return $this->requestee;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @param DateTimeInterface|null $requestApprovedAt
     * @param DBEProblem $serviceRequest
     * @param string|null $comments
     * @throws ColumnOutOfRangeException
     * @throws Exception
     */
    private function logCustomerContactActivity(ChargeableWorkCustomerRequest $request,
                                                ?DateTimeInterface $requestApprovedAt,
                                                DBEProblem $serviceRequest,
                                                ?string $comments
    ): void
    {
        $requestee   = $this->getRequestee($request);
        $buActivity  = new BUActivity($this);
        $description = "<p>{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)} rejected the request for {$request->getAdditionalHoursRequested()->value()} hours at {$requestApprovedAt->format('d/m/Y H:i:s')}</p>";
        if ($comments) {
            $description .= "<p>$comments</p>";
        }
        $requester = $this->getRequester($request);
        $buActivity->addCustomerContactActivityToServiceRequest($serviceRequest, $description, $requester);
    }

    /**
     * @param DBEJProblem $dbeProblem
     * @throws ColumnOutOfRangeException
     */
    private function updateServiceRequest(DBEJProblem $dbeProblem): void
    {
        $toUpdateProblem = new DBEProblem($this);
        $toUpdateProblem->getRow($dbeProblem->getValue(DBEProblem::problemID));
        $toUpdateProblem->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');
        $toUpdateProblem->updateRow();
    }

    private function getRequester(ChargeableWorkCustomerRequest $request): DBEUser
    {
        if (!$this->requester) {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($request->getRequesterId()->value());
            $this->requester = $dbeUser;
        }
        return $this->requester;
    }

    private function sendEmailToEngineer(ChargeableWorkCustomerRequest $request)
    {
        CommunicationService::sendExtraChargeableWorkRequestRejectedEmail($request);
    }

    private function deleteChargeableRequest(ChargeableWorkCustomerRequest $request)
    {
        $this->repository->delete($request);
    }
}
