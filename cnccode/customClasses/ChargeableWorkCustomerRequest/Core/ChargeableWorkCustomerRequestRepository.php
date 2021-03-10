<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;
interface ChargeableWorkCustomerRequestRepository
{
    public function getNextIdentity(): ChargeableWorkCustomerRequestId;
}