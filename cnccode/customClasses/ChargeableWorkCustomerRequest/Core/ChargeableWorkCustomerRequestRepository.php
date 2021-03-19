<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\Core;
interface ChargeableWorkCustomerRequestRepository
{
    public function getNextIdentity(): ChargeableWorkCustomerRequestTokenId;

    public function getById(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest;

    public function save(ChargeableWorkCustomerRequest $chargeableWorkCustomerRequest);

    public function delete(ChargeableWorkCustomerRequest $request);

    public function getCountRequestsForServiceRequestId(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    );

    public function getChargeableRequestForServiceRequest(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    ): ChargeableWorkCustomerRequest;

    public function deleteChargeableRequestsForServiceRequest(ChargeableWorkCustomerRequestServiceRequestId $serviceRequestId
    );
}