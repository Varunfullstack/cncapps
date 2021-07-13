<?php

use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierId;

require './config.inc.php';
$supplierRepo = new MySQLSupplierRepository();
$suppliers    = $supplierRepo->getAllSuppliers();
foreach ($suppliers as $supplierDTO) {

    try {
        $supplier = $supplierRepo->getById(new SupplierId($supplierDTO->getId()));
    } catch (Exception $exception) {
        echo "<p>{$supplierDTO->getId()} validation failed with error: {$exception->getMessage()}</p>";
    }

}