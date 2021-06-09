<?php

namespace CNCLTD\AdditionalChargesRates\Domain;

use Exception;

class AdditionalChargeRateNotFoundException extends Exception
{

    /**
     * AdditionalChargeRateNotFoundException constructor.
     * @param AdditionalChargeRateId $additionalChargeRateId
     */
    public function __construct(AdditionalChargeRateId $additionalChargeRateId)
    {
        parent::__construct("Additional Charge Rate not found with id: {$additionalChargeRateId->value()}");
    }
}