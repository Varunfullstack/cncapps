<?php

namespace CNCLTD\Supplier;
interface SupplierRepository
{
    public function getAllSuppliers(): Suppliers;

    public function add(Supplier $supplier): void;

    public function nextIdentity(): SupplierId;


}