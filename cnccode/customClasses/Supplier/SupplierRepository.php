<?php

namespace CNCLTD\Supplier;
interface SupplierRepository
{
    public function getAllSuppliers(): array;

    public function add(Supplier $supplier): void;

    public function nextIdentity(): SupplierId;

    public function getById(SupplierId $supplierId): Supplier;


}