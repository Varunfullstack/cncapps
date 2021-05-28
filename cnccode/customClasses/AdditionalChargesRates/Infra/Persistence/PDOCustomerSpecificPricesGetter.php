<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPricesGetter;
use PDO;

class PDOCustomerSpecificPricesGetter implements CustomerSpecificPricesGetter
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

    public function getSpecificPricesForCustomer(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
       a.id
       a.description,
       cp.salePrice,
       cp.timeBudgetMinutes
FROM additionalChargeRate a
         JOIN additionalchargeratecustomerprices cp
                   ON cp.`additionalChargeRateId` = a.`id`
                       AND cp.`customerId` = :customerId'
        );
        $statement->execute(["customerId" => $customerId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}