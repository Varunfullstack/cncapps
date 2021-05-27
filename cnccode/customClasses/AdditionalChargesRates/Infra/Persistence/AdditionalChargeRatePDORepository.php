<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateNotFoundException;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\InvalidAdditionalChargeRageIdValue;
use CNCLTD\Exceptions\EmptyStringException;
use PDO;
use PDOException;
use PDOStatement;

class AdditionalChargeRatePDORepository implements AdditionalChargeRateRepository
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * AdditionalChargeRatePDORepository constructor.
     */
    public function __construct(PDO $PDO)
    {
        $this->pdo = $PDO;
    }

    /**
     * @throws InvalidAdditionalChargeRageIdValue
     * @throws AdditionalChargeRateNotFoundException
     * @throws EmptyStringException
     */
    function ofId(AdditionalChargeRateId $additionalChargeRateId): AdditionalChargeRate
    {
        $additionalChargeRateStatement = $this->pdo->prepare(
            'select id,description,notes,salePrice from additionalChargeRate where id = ?'
        );
        if (!$additionalChargeRateStatement || !$additionalChargeRateStatement->execute(
                [$additionalChargeRateId->value()]
            )) {
            $errorInfo = json_encode($additionalChargeRateStatement->errorInfo());
            throw new PDOException("Failed to retrieve AdditionalChargeRate: {$errorInfo}");
        }
        $data = $additionalChargeRateStatement->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            throw new AdditionalChargeRateNotFoundException($additionalChargeRateId);
        }
        $data = $this->addSpecificPricesToData($data, $additionalChargeRateId);
        return AdditionalChargeRatePDO::fromPersistence($data);
    }

    function save(AdditionalChargeRate $additionalChargeRate)
    {
        $this->pdo->beginTransaction();
        try {
            $query                   = "insert into additionalChargeRate(id,description,notes,salePrice) values (:id,:description,:notes,:salePrice) on duplicate key update description = :description, notes = :notes, salePrice = :salePrice";
            $insertOrUpdateStatement = $this->pdo->prepare($query);
            if (!$insertOrUpdateStatement->execute(
                [
                    "id"          => $additionalChargeRate->id()->value(),
                    "description" => $additionalChargeRate->description()->value(),
                    "notes"       => $additionalChargeRate->notes()->value(),
                    "salePrice"   => $additionalChargeRate->salePrice()->value(),
                ]
            )) {
                throw new PDOException('Failed to insert or update');
            }
            $this->deleteAdditionalChargeSpecificCustomerPrices($additionalChargeRate);
            $this->insertAdditionalChargeSpecificCustomerPrices($additionalChargeRate);
            $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            error_log("Failed to insert or update Additional Charge Rate: {$exception->getMessage()}");
            throw new PDOException("Failed to insert or update: {$exception->getMessage()}");
        }
    }

    /**
     * @param AdditionalChargeRate $additionalChargeRate
     */
    private function deleteAdditionalChargeSpecificCustomerPrices(AdditionalChargeRate $additionalChargeRate): void
    {
        $deleteAdditionalChargeCustomerPricesStatement = $this->pdo->prepare(
            'delete from additionalChargeRateCustomerPrices where additionalChargeRateId = ?'
        );
        if (!$deleteAdditionalChargeCustomerPricesStatement->execute([$additionalChargeRate->id()->value()])) {
            $errorInfo = json_encode($deleteAdditionalChargeCustomerPricesStatement->errorInfo());
            throw new PDOException("Failed to delete additionalChargeRateCustomerPrices: {$errorInfo}");
        }

    }

    /**
     * @param AdditionalChargeRate $additionalChargeRate
     */
    private function insertAdditionalChargeSpecificCustomerPrices(AdditionalChargeRate $additionalChargeRate): void
    {
        $values     = [];
        $subQueries = [];
        foreach ($additionalChargeRate->specificCustomerPrices() as $specificCustomerPrice) {
            $values       = array_merge(
                $values,
                [
                    $additionalChargeRate->id()->value(),
                    $specificCustomerPrice->customerId()->value(),
                    $specificCustomerPrice->salePrice()->value()
                ]
            );
            $subQueries[] = "(?,?,?)";
        }
        if (empty($values)) {
            return;
        }
        $query           = "insert into additionalChargeRateCustomerPrices(additionalChargeRateId, customerId, salePrice) values ";
        $query           .= implode(',', $subQueries);
        $insertStatement = $this->pdo->prepare($query);
        if (!$insertStatement->execute($values)) {
            $errorInfo = json_encode($insertStatement->errorInfo());
            throw new PDOException("Failed to insert additionalChargeRateCustomerPrices: {$errorInfo}");
        }
    }

    public function searchAll(): array
    {
        $additionalChargeRateStatement = $this->pdo->prepare(
            'select id,description,notes,salePrice from additionalChargeRate'
        );
        if (!$additionalChargeRateStatement || !$additionalChargeRateStatement->execute()) {
            $errorInfo = json_encode($additionalChargeRateStatement->errorInfo());
            throw new PDOException("Failed to retrieve AdditionalChargeRate: {$errorInfo}");
        }
        $toReturn = [];
        while ($data = $additionalChargeRateStatement->fetch(PDO::FETCH_ASSOC)) {
            $data       = $this->addSpecificPricesToData(
                $data,
                AdditionalChargeRateId::fromNative($data['id'])
            );
            $toReturn[] = AdditionalChargeRatePDO::fromPersistence($data);
        }
        return $toReturn;
    }

    /**
     * @param array $data
     * @param AdditionalChargeRateId $additionalChargeRateId
     * @return array
     */
    private function addSpecificPricesToData(array $data,
                                             AdditionalChargeRateId $additionalChargeRateId
    ): array
    {

        $data['specificCustomerPrices']  = [];
        $specificCustomerPricesStatement = $this->pdo->prepare(
            'select additionalChargeRateId, customerId, salePrice from additionalChargeRateCustomerPrices where additionalChargeRateId = ?'
        );
        if (!$specificCustomerPricesStatement || !$specificCustomerPricesStatement->execute(
                [$additionalChargeRateId->value()]
            )) {
            $prettyErrorInfo = print_r($this->pdo->errorInfo());
            throw new PDOException(
                "Failed to retrieve additionalChargeRateCustomerPrices: {$prettyErrorInfo}"
            );
        }
        $data['specificCustomerPrices'] = $specificCustomerPricesStatement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function delete(AdditionalChargeRate $additionalChargeRate)
    {
        $this->pdo->beginTransaction();
        try {
            $query                   = "delete from additionalChargeRate where id = :id";
            $deleteStatement = $this->pdo->prepare($query);
            if (!$deleteStatement->execute(
                [
                    "id"          => $additionalChargeRate->id()->value(),
                ]
            )) {
                throw new PDOException('Failed to insert or update');
            }
            $this->deleteAdditionalChargeSpecificCustomerPrices($additionalChargeRate);
            $this->pdo->commit();
        } catch (\Exception $exception) {
            $this->pdo->rollBack();
            error_log("Failed to insert or update Additional Charge Rate: {$exception->getMessage()}");
            throw new PDOException("Failed to insert or update: {$exception->getMessage()}");
        }
    }
}