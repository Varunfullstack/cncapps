<?php

namespace CNCLTD\AdditionalChargesRates\Domain;
interface CustomerPricesGetter
{
    public function getPricesForCustomer(int $customerId);
}