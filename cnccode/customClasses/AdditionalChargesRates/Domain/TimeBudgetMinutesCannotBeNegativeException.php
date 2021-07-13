<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
use Exception;

class TimeBudgetMinutesCannotBeNegativeException extends Exception
{

    /**
     * TimeBudgetMinutesCannotBeNegativeException constructor.
     */
    public function __construct()
    {
        parent::__construct("Time budget minutes has to be equal or greater than zero");
    }
}