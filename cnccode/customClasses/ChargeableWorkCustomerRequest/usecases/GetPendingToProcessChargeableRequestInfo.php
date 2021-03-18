<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\ChargeableWorkCustomerRequest\DTO\PendingToProcessChargeableRequestInfoDTO;
use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLRepository;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;
use DBEJProblem;
use DBEProblem;

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
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws ServiceRequestNotFoundException
     */
    public function __invoke(ChargeableWorkCustomerRequestTokenId $id): PendingToProcessChargeableRequestInfoDTO
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        $dbeProblem       = new DBEJProblem($this);
        $serviceRequestId = $request->getServiceRequestId()->value();
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new ServiceRequestNotFoundException();
        }
        return new PendingToProcessChargeableRequestInfoDTO(
            $id->value(),
            $serviceRequestId,
            $dbeProblem->getValue(DBEProblem::emailSubjectSummary) || "",
            $dbeProblem->getValue(DBEJProblem::contactName),
            $request->getAdditionalHoursRequested()->value(),
            $request->getReason()->value()
        );
    }
}