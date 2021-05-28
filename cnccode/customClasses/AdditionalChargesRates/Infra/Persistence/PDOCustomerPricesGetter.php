<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\CustomerPricesGetter;
use PDO;

class PDOCustomerPricesGetter implements CustomerPricesGetter
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

    public function getPricesForCustomer(int $customerId)
    {
        $statement = $this->pdo->prepare(
            'SELECT a.description,
       IFNULL(cp.salePrice, a.salePrice) as salePrice,
       IFNULL(cp.timeBudgetMinutes, a.timeBudgetMinutes) as timeBudgetMinutes
FROM additionalChargeRate a
         LEFT JOIN additionalchargeratecustomerprices cp
                   ON cp.`additionalChargeRateId` = a.`id`
                       AND cp.`customerId` = :customerId'
        );
        $statement->execute(["customerId" => $customerId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}