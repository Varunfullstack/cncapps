<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
class CannotDeleteAdditionalChargeRateException extends \Exception
{

    /**
     * CannotDeleteAdditionalChargeRateException constructor.
     * @param AdditionalChargeRateId $additionalChargeRateId
     */
    public function __construct(AdditionalChargeRateId $additionalChargeRateId)
    {
        parent::__construct(
            "Additional Charge rate with id: {$additionalChargeRateId->value()} cannot be deleted because it has specific customer prices"
        );
    }
}