<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
use Exception;

class InvalidAdditionalChargeRageIdValue extends Exception
{

    /**
     * InvalidAdditionalChargeRageIdValue constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct("The provided id: $id, does not conform to the valid UUID format");
    }

}