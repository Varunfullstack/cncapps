<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
interface AdditionalChargeRateRepository
{
    function ofId(AdditionalChargeRateId $additionalChargeRateId): AdditionalChargeRate;

    function save(AdditionalChargeRate $additionalChargeRate);

    public function searchAll();

    public function delete(AdditionalChargeRate $additionalChargeRate);
}