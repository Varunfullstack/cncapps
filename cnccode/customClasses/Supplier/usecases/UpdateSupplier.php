<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierRepository;
use CNCLTD\Supplier\UpdateSupplierRequest;
use Exception;

class UpdateSupplier
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
     * @param UpdateSupplierRequest $request
     */
    public function __invoke(UpdateSupplierRequest $request)
    {

        $repo     = new MySQLSupplierRepository();
        $supplier = $repo->getById($request->getId());
        if (!$supplier) {
            throw new Exception('Not found');
        }
        $supplier->updateFromRequest($request);
        $repo->save($supplier);
    }
}