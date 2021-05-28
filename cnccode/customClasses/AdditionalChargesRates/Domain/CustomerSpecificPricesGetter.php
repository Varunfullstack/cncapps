<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
interface CustomerSpecificPricesGetter
{
    public function getSpecificPricesForCustomer(int $customerId);
}