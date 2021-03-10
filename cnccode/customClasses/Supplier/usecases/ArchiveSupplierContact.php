<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Exceptions\SupplierContactAlreadyArchivedException;
use CNCLTD\Exceptions\SupplierContactNotFoundException;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\SupplierId;
use CNCLTD\Supplier\SupplierRepository;
use Exception;

class ArchiveSupplierContact
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

    /**
     * @param SupplierId $supplierId
     * @param SupplierContactId $supplierContactId
     * @throws SupplierContactNotFoundException
     * @throws SupplierContactAlreadyArchivedException
     */
    public function __invoke(SupplierId $supplierId, SupplierContactId $supplierContactId)
    {
        $supplier = $this->repository->getById($supplierId);
        if (!$supplier) {
            throw new Exception('Supplier not found');
        }
        $supplier->archiveSupplierContact($supplierContactId);
        $this->repository->save($supplier);

    }
}