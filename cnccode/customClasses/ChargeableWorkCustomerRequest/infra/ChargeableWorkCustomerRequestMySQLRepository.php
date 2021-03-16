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
        return $this->dbInstance->nextid($this->tableName);
    }

    public function getById(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest
    {
        $query     = "select * from {$this->tableName} where id = ?";
        $statement = $this->dbInstance->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
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
        // TODO: Implement save() method.
    }

    public function getByToken(ChargeableWorkCustomerRequestTokenId $token): ChargeableWorkCustomerRequest
    {
        // TODO: Implement getByToken() method.
    }
}