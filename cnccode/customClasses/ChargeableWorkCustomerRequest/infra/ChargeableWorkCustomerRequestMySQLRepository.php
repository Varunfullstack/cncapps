<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\infra;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\Exceptions\AdditionalHoursRequestedInvalidValueException;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestForServiceRequestAlreadyExists;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use Exception;
use mysqli_result;
use PDO;

class ChargeableWorkCustomerRequestMySQLRepository implements ChargeableWorkCustomerRequestRepository
{
    /**
     * @var PDO
     */
    private $dbInstance;
    private $tableName = "chargeableWorkCustomerRequest";


    /**
     * ChargeableWorkCustomerRequestMySQLRepository constructor.
     */
    public function __construct()
    {
        $this->dbInstance = \DBConnect::instance()->getDB();

    }

    public function getNextIdentity(): ChargeableWorkCustomerRequestTokenId
    {
        return new ChargeableWorkCustomerRequestTokenId(uniqid());
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @return ChargeableWorkCustomerRequest
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws Exception
     */
    public function getById(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest
    {
        $query     = "select * from {$this->tableName} where id = ?";
        $statement = $this->dbInstance->prepare($query);
        $statement->execute([$id->value()]);
        /** @var ChargeableWorkCustomerRequestMySQLDTO $dto */
        $dto = $statement->fetchObject(ChargeableWorkCustomerRequestMySQLDTO::class);
        if (!$dto) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        return ChargeableWorkCustomerRequest::fromMySQLDTO($dto);
    }

    /**
     * @param ChargeableWorkCustomerRequest $chargeableWorkCustomerRequest
     * @throws Exception
     */
    public function save(ChargeableWorkCustomerRequest $chargeableWorkCustomerRequest)
    {
        $this->guardAgainstAlreadyExistsOneForServiceRequest($chargeableWorkCustomerRequest->getServiceRequestId());
        $query      = "insert into {$this->tableName}(id,createdAt, serviceRequestId, requesteeId, additionalHoursRequested,requesterId,reason) values (?,?,?,?,?,?,?)";
        $parameters = [
            $chargeableWorkCustomerRequest->getId()->value(),
            $chargeableWorkCustomerRequest->getCreatedAt()->format(DATE_MYSQL_DATETIME),
            $chargeableWorkCustomerRequest->getServiceRequestId()->value(),
            $chargeableWorkCustomerRequest->getRequesteeId()->value(),
            $chargeableWorkCustomerRequest->getAdditionalHoursRequested()->value(),
            $chargeableWorkCustomerRequest->getRequesterId()->value(),
            $chargeableWorkCustomerRequest->getReason()->value()
        ];
        $statement  = $this->dbInstance->prepare($query);
        $statement->execute($parameters);
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return bool|int|mysqli_result
     * @throws Exception
     */
    public function delete(ChargeableWorkCustomerRequest $request)
    {
        $query     = "delete from {$this->tableName} where id = ?";
        $statement = $this->dbInstance->prepare($query,);
        return $statement->execute([$request->getId()->value()]);
    }

    public function getCountRequestsForServiceRequestId(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId)
    {
        $query     = "select count(*) > 0 from {$this->tableName} where serviceRequestId = ?";
        $statement = $this->dbInstance->prepare($query,);
        $statement->execute([$serviceRequestId->value()]);
        return $statement->fetch(PDO::FETCH_NUM)[0];
    }

    /**
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     * @throws ChargeableWorkCustomerRequestForServiceRequestAlreadyExists
     */
    private function guardAgainstAlreadyExistsOneForServiceRequest(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    )
    {
        if ($this->getCountRequestsForServiceRequestId($serviceRequestId)) {
            throw new ChargeableWorkCustomerRequestForServiceRequestAlreadyExists($serviceRequestId);
        }
    }

    /**
     * @param ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
     * @return ChargeableWorkCustomerRequest
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws AdditionalHoursRequestedInvalidValueException
     * @throws Exception
     */
    public function getChargeableRequestForServiceRequest(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    ): ChargeableWorkCustomerRequest
    {
        $query     = "select * from {$this->tableName} where serviceRequestId = ?";
        $statement = $this->dbInstance->prepare($query,);
        $statement->execute([$serviceRequestId->value()]);
        /** @var ChargeableWorkCustomerRequestMySQLDTO $dto */
        $dto = $statement->fetchObject(ChargeableWorkCustomerRequestMySQLDTO::class);
        if (!$dto) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        return ChargeableWorkCustomerRequest::fromMySQLDTO($dto);
    }


    public function deleteChargeableRequestsForServiceRequest(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    )
    {
        $query     = "delete from {$this->tableName} where serviceRequestId = ?";
        $statement = $this->dbInstance->prepare($query,);
        $statement->execute([$serviceRequestId->value()]);
    }
}