<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\infra;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestServiceRequestId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestForServiceRequestAlreadyExists;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use dbSweetcode;
use Exception;
use MDB_PEAR_PROXY;
use mysqli_result;
use PDO;

class ChargeableWorkCustomerRequestMySQLRepository implements ChargeableWorkCustomerRequestRepository
{
    /**
     * @var dbSweetcode|MDB_PEAR_PROXY|mixed|object|PDO
     */
    private $dbInstance;
    private $tableName = "chargeableWorkCustomerRequest";


    /**
     * ChargeableWorkCustomerRequestMySQLRepository constructor.
     */
    public function __construct()
    {
        global $db;
        $this->dbInstance = $db;

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
        $statement = $this->dbInstance->preparedQuery(
            $query,
            [
                [
                    "type"  => "s",
                    "value" => $id->value()
                ]
            ]
        );
        /** @var ChargeableWorkCustomerRequestMySQLDTO $dto */
        $dto = $statement->fetch_object(ChargeableWorkCustomerRequestMySQLDTO::class);
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
            [
                "type"  => "s",
                "value" => $chargeableWorkCustomerRequest->getId()->value()
            ],
            [
                "type"  => "s",
                "value" => $chargeableWorkCustomerRequest->getCreatedAt()->format(DATE_MYSQL_DATETIME)
            ],
            [
                "type"  => "i",
                "value" => $chargeableWorkCustomerRequest->getServiceRequestId()->value()
            ],
            [
                "type"  => "i",
                "value" => $chargeableWorkCustomerRequest->getRequesteeId()->value()
            ],
            [
                "type"  => "i",
                "value" => $chargeableWorkCustomerRequest->getAdditionalHoursRequested()->value()
            ],
            [
                "type"  => "i",
                "value" => $chargeableWorkCustomerRequest->getRequesterId()->value()
            ],
            [
                "type"  => "s",
                "value" => $chargeableWorkCustomerRequest->getReason()->value()
            ]
        ];
        $this->dbInstance->preparedQuery(
            $query,
            $parameters
        );
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return bool|int|mysqli_result
     * @throws Exception
     */
    public function delete(ChargeableWorkCustomerRequest $request)
    {
        $query = "delete from {$this->tableName} where id = ?";
        return $this->dbInstance->preparedQuery(
            $query,
            [
                [
                    "type"  => "s",
                    "value" => $request->getId()->value()
                ]
            ]
        );
    }

    public function getCountRequestsForServiceRequestId(ChargeableWorkCustomerRequestServiceRequestId $param)
    {
        $query     = "select count(*) > 0 from {$this->tableName} where serviceRequestId = ?";
        $statement = $this->dbInstance->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $param->value()
                ]
            ]
        );
        return $statement->fetch_row()[0];
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


}