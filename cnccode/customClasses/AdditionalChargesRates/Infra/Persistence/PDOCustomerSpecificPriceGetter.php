<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPriceGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPricesGetter;
use PDO;

class PDOCustomerSpecificPriceGetter implements CustomerSpecificPriceGetter
{
    /**
     * @var PDO
     */
    private $pdo;


    /**
     * PDOCustomerPricesGetter constructor.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSpecificPriceForCustomer(int $customerId, string $additionalChargeRateId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.id,
       a.description,
       cp.salePrice,
       cp.timeBudgetMinutes
FROM additionalChargeRate a
         JOIN additionalchargeratecustomerprices cp
              ON cp.`additionalChargeRateId` = a.`id`
                  AND cp.`customerId` = :customerId
where a.id = :additionalChargeRateId'
        );
        $statement->execute(["customerId" => $customerId, "additionalChargeRateId" => $additionalChargeRateId]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}