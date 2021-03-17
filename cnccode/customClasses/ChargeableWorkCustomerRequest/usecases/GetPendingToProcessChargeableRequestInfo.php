<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLRepository;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestAlreadyProcessedException;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;

class GetPendingToProcessChargeableRequestInfo
{
    /**
     * @var ChargeableWorkCustomerRequestMySQLRepository
     */
    private $repository;

    /**
     * GetChargeableRequestInfo constructor.
     * @param ChargeableWorkCustomerRequestMySQLRepository $repository
     */
    public function __construct(ChargeableWorkCustomerRequestMySQLRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @return PendingToProcessChargeableRequestInfoDTO
     * @throws ChargeableWorkCustomerRequestAlreadyProcessedException
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws ServiceRequestNotFoundException
     */
    public function __invoke(ChargeableWorkCustomerRequestTokenId $id): PendingToProcessChargeableRequestInfoDTO
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        if ($request->getProcessedDateTime()->value()) {
            throw new ChargeableWorkCustomerRequestAlreadyProcessedException();
        }
        $dbeProblem       = new \DBEJProblem($this);
        $serviceRequestId = $request->getServiceRequestId()->value();
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new ServiceRequestNotFoundException();
        }
        return new PendingToProcessChargeableRequestInfoDTO(
            $id->value(),
            $serviceRequestId,
            $dbeProblem->getValue(\DBEProblem::emailSubjectSummary),
            $dbeProblem->getValue(\DBEJProblem::contactName),
            $request->getAdditionalHoursRequested()->value()
        );
    }
}