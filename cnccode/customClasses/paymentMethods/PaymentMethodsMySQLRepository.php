<?php

namespace CNCLTD\paymentMethods;

use dbSweetcode;

class PaymentMethodsMySQLRepository
{
    /**
     * @var dbSweetcode
     */
    private $db;


    /**
     * PaymentMethodsMySQLRepository constructor.
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    public function getAll(): array
    {
        global $db;
        $statement      = $db->preparedQuery('select * from payMeth', []);
        $paymentMethods = [];
        while ($paymentMethod = $statement->fetch_object(PaymentMethodMySQLDTO::class)) {
            $paymentMethods[] = $paymentMethod;
        }
        return $paymentMethods;
    }
}