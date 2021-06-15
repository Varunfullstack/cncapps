<?php

use CNCLTD\Supplier\infra\MySQLSupplierRepository;

require './config.inc.php';
$supplierRepo = new MySQLSupplierRepository();
$suppliers    = $supplierRepo->getAllSuppliers();
foreach ($suppliers as $supplierDTO) {

    try {
        $supplier = $supplierRepo->getById(new \CNCLTD\Supplier\SupplierId($supplierDTO->getId()));
    } catch (\Exception $exception) {
        echo "<p>{$supplierDTO->getId()} validation failed with error: {$exception->getMessage()}</p>";
    }

}