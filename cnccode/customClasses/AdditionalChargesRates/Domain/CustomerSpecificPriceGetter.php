<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
interface CustomerSpecificPriceGetter
{
    public function getSpecificPriceForCustomer(int $customerId, string $additionalChargeRateId);
}