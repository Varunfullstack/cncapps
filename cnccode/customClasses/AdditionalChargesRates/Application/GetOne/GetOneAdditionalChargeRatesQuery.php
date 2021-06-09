<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOne;

use CNCLTD\Shared\Domain\Bus\Query;

class GetOneAdditionalChargeRatesQuery implements Query
{
    private $id;

    /**
     * GetOneAdditionalChargeRatesQuery constructor.
     * @param $id
     */
    public function __construct($id) { $this->id = $id; }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }
}