<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;
interface ChargeableWorkCustomerRequestRepository
{
    public function getNextIdentity(): ChargeableWorkCustomerRequestTokenId;

    public function getById(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest;

    public function save(ChargeableWorkCustomerRequest $chargeableWorkCustomerRequest);
}