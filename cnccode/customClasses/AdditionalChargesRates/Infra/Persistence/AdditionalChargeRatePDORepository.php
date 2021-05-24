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
            'select id,description,notes,salesPrice from additionalChargeRate where id = ?'
        );
        if (!$additionalChargeRateStatement || !$additionalChargeRateStatement->execute(
                [$additionalChargeRateId->value()]
            )) {
            throw new PDOException("Failed to retrieve AdditionalChargeRate: {$this->pdo->errorInfo()}");
        }
        $data = $this->addSpecificPricesToData($additionalChargeRateStatement, $additionalChargeRateId);
        return AdditionalChargeRatePDO::fromPersistence($data);
    }

    function save(AdditionalChargeRate $additionalChargeRate)
    {
        $this->pdo->beginTransaction();
        $this->deleteAdditionalChargeCustomerSpecificPrices($additionalChargeRate);
        $this->insertAdditionalChargeCustomerSpecificPrices($additionalChargeRate);
        $query                   = "insert into additionalChargeRate(id,description,notes,salesPrice) values (?,?,?,?) on duplicate key update description = ?, notes = ?, salesPrice = ?";
        $insertOrUpdateStatement = $this->pdo->prepare($query);
        if (!$insertOrUpdateStatement->execute(
            [
                $additionalChargeRate->id()->value(),
                $additionalChargeRate->description()->value(),
                $additionalChargeRate->notes()->value(),
                $additionalChargeRate->salePrice()->value(),
            ]
        )) {
            $this->pdo->rollBack();
        }
        $this->pdo->commit();
    }

    /**
     * @param AdditionalChargeRate $additionalChargeRate
     */
    private function deleteAdditionalChargeCustomerSpecificPrices(AdditionalChargeRate $additionalChargeRate): void
    {
        $deleteAdditionalChargeCustomerPricesStatement = $this->pdo->prepare(
            'delete * from additionalChargeRateCustomerPrices where additionalChargeRateId = ?'
        );
        $deleteAdditionalChargeCustomerPricesStatement->execute([$additionalChargeRate->id()->value()]);
    }

    /**
     * @param AdditionalChargeRate $additionalChargeRate
     */
    private function insertAdditionalChargeCustomerSpecificPrices(AdditionalChargeRate $additionalChargeRate): void
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
        if (!empty($values)) {
            $query           = "insert into additionalChargeRateCustomerPrices(additionalChargeRateId, customerId, salePrice) values ";
            $query           .= implode(',', $subQueries);
            $insertStatement = $this->pdo->prepare($query);
            $insertStatement->execute($values);
        }
    }

    public function searchAll(): array
    {
        $additionalChargeRateStatement = $this->pdo->prepare(
            'select id,description,notes,salesPrice from additionalChargeRate'
        );
        if (!$additionalChargeRateStatement || !$additionalChargeRateStatement->execute()) {
            $errorInfo = print_r($additionalChargeRateStatement->errorInfo());
            throw new PDOException("Failed to retrieve AdditionalChargeRate: {$errorInfo}");
        }
        $toReturn = [];
        while ($data = $additionalChargeRateStatement->fetch(PDO::FETCH_ASSOC)) {
            $data       = $this->addSpecificPricesToData($additionalChargeRateStatement, $data['id']);
            $toReturn[] = AdditionalChargeRatePDO::fromPersistence($data);
        }
        return $toReturn;
    }

    /**
     * @param PDOStatement $additionalChargeRateStatement
     * @param AdditionalChargeRateId $additionalChargeRateId
     * @return mixed
     * @throws AdditionalChargeRateNotFoundException
     */
    private function addSpecificPricesToData(PDOStatement $additionalChargeRateStatement,
                                             AdditionalChargeRateId $additionalChargeRateId
    )
    {
        $data = $additionalChargeRateStatement->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            throw new AdditionalChargeRateNotFoundException($additionalChargeRateId);
        }
        $data['specificCustomerPrices'] = [];
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
}