<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\infra;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use dbSweetcode;
use MDB_PEAR_PROXY;
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

    public function save(ChargeableWorkCustomerRequest $chargeableWorkCustomerRequest)
    {
        $date          = null;
        $dateTimeValue = $chargeableWorkCustomerRequest->getProcessedDateTime()->value();
        if ($dateTimeValue) {
            $date = $dateTimeValue->format(DATE_MYSQL_DATETIME);
        }
        if ($this->existsId($chargeableWorkCustomerRequest->getId())) {
            $query = "update {$this->tableName} set processedDateTime = ? where id = ? ";
            $this->dbInstance->preparedQuery(
                $query,
                [
                    [
                        "type"  => "s",
                        "value" => $date
                    ],
                    [
                        "type"  => "s",
                        "value" => $chargeableWorkCustomerRequest->getId()->value()
                    ],
                ]
            );
        } else {
            $query = "insert into {$this->tableName}(id,createdAt, serviceRequestId, requesteeId, additionalTimeRested,requesterId) values (?,?,?,?,?,?)";
            $this->dbInstance->preparedQuery(
                $query,
                [
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
                    ]
                ]
            );
        }
    }

    private function existsId(ChargeableWorkCustomerRequestTokenId $id)
    {
        $query     = "select count(*) > 0 as `exists` from {$this->tableName} where id = ?";
        $statement = $this->dbInstance->preparedQuery(
            $query,
            [
                [
                    "type"  => "s",
                    "value" => $id->value()
                ]
            ]
        );
        return $statement->fetch_row()[0];
    }
}