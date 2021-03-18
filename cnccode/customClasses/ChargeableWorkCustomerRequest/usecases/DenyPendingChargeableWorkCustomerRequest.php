<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;
use DateTimeImmutable;
use DateTimeInterface;
use DBEContact;
use DBEJProblem;
use DBEProblem;
use DBEUser;

global $cfg;
require_once($cfg["path_bu"] . "/BUActivity.inc.php");

class DenyPendingChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;
    private $requestee;
    private $requester;

    /**
     * ApprovePendingChargeableWorkCustomerRequest constructor.
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
     */
    private function logCustomerContactActivity(ChargeableWorkCustomerRequest $request,
                                                ?DateTimeInterface $requestApprovedAt,
                                                DBEProblem $serviceRequest,
                                                ?string $comments
    ): void
    {
        $requestee   = $this->getRequestee($request);
        $buActivity  = new BUActivity($this);
        $description = "<p>{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)} denied the request for {$request->getAdditionalHoursRequested()->value()} hours at {$requestApprovedAt->format('d/m/Y H:i:s')}</p>";
        if ($comments) {
            $description .= "<p>$comments</p>";
        }
        $requester = $this->getRequester($request);
        $buActivity->addCustomerContactActivityToServiceRequest($serviceRequest, $description, $requester);
    }

    /**
     * @param DBEJProblem $dbeProblem
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
        CommunicationService::sendExtraChargeableWorkRequestDeniedEmail($request);
    }

    private function deleteChargeableRequest(ChargeableWorkCustomerRequest $request)
    {
        $this->repository->delete($request);
    }
}
