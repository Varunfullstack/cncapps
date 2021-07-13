<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Supplier\SupplierId;
use CNCLTD\Supplier\SupplierRepository;
use Exception;

class ReactivateSupplier
{
    /**
     * @var SupplierRepository
     */
    private $repository;

    /**
     * ArchiveSupplier constructor.
     * @param SupplierRepository $repository
     */
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(SupplierId $supplierId)
    {
        $supplier = $this->repository->getById($supplierId);
        if (!$supplier) {
            throw new Exception('Supplier not found');
        }
        $supplier->reactivate();
        $this->repository->save($supplier);

    }
}