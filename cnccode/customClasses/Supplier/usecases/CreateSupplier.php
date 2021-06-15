<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Supplier\CreateSupplierRequest;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\Supplier;
use CNCLTD\Supplier\SupplierIsActive;
use CNCLTD\Supplier\SupplierRepository;

class CreateSupplier
{
    /**
     * @var SupplierRepository
     */
    private $repository;

    /**
     * UpdateSupplier constructor.
     * @param SupplierRepository $repository
     */
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateSupplierRequest $request
     */
    public function __invoke(CreateSupplierRequest $request)
    {

        $repo       = new MySQLSupplierRepository();
        $supplierId = $repo->nextIdentity();
        $contactId  = $repo->nextContactIdentity();
        $supplier = Supplier::create(
            $supplierId,
            $request->getName(),
            $request->getAddress1(),
            $request->getAddress2(),
            $request->getTown(),
            $request->getCounty(),
            $request->getPostcode(),
            $request->getWebsiteURL(),
            $request->getPaymentMethodId(),
            $request->getAccountCode(),
            new SupplierIsActive(true),
            $contactId,
            $request->getMainContactPosition(),
            $request->getMainContactTitle(),
            $request->getMainContactFirstName(),
            $request->getMainContactLastName(),
            $request->getMainContactEmail(),
            $request->getMainContactPhone()
        );
        $repo->save($supplier);
    }
}