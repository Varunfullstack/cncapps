<?php

namespace CNCLTD\Exceptions\infra;

use CNCLTD\Exceptions\Supplier;
use CNCLTD\Exceptions\SupplierId;
use CNCLTD\Exceptions\SupplierRepository;
use dbSweetcode;
use Exception;
use ReflectionClass;

class MySQLSupplierRepository implements SupplierRepository
{
    /**
     * @var dbSweetcode
     */
    private $sweetCodeDB;
    private $tableName = "supplier";


    /**
     * MySQLSupplierRepository constructor.
     */
    public function __construct()
    {
        global $db;
        $this->sweetCodeDB = $db;
    }

    /**
     * @param SupplierId $supplierId
     * @return Supplier|void
     * @throws Exception
     */
    public function getById(SupplierId $supplierId): Supplier
    {
        $statement = $this->sweetCodeDB->preparedQuery(
            'select * from supplier where sup_suppno = ? ',
            [
                [
                    "type"  => "i",
                    "value" => $supplierId->value()
                ]
            ]
        );
        if (!$statement) {
            throw new Exception('Failed to retrieve Supplier!');
        }
        $supplierDTO = $statement->fetch_object(SupplierMySQLDTO::class);
        if (!$supplierId) {
            throw new Exception('Supplier not found');
        }
        $reflection = new ReflectionClass(Supplier::class);
//        $supplier = $reflection->newInstanceWithoutConstructor();

    }

    public function getAllSuppliers(): array
    {
        $statement = $this->sweetCodeDB->preparedQuery(
            "
select supplier.sup_suppno                                      as id,
       supplier.sup_name                                        as name,
       supplier.sup_add1                                        as address1,
       supplier.sup_add1                                        as address2,
       supplier.sup_town                                        as town,
       supplier.sup_county                                      as county,
       supplier.sup_postcode                                    as postcode,
       supplier.sup_cnc_accno                                   as cncAccountCode,
       supplier.sup_web_site_url                                as websiteURL,
       mainContact.title                                        as mainContactTitle,
       mainContact.position                                     as mainContactPosition,
       concat(mainContact.firstName, ' ', mainContact.lastName) as mainContactName,
       mainContact.email                                        as mainContactEmail,
       mainContact.phone                                        as mainContactPhone
from supplier
         left join supplierContact mainContact on mainContact.supplierId = supplier.sup_suppno and mainContact.isMain",
            []
        );
        $list      = [];
        while ($supplierDTO = $statement->fetch_object(SupplierWithMainContactMysqlDTO::class)) {
            $list[] = $supplierDTO;
        }
        return $list;
    }

    public function add(Supplier $supplier): void
    {
        // TODO: Implement add() method.
    }

    public function nextIdentity(): SupplierId
    {
        $nextId = $this->sweetCodeDB->nextid($this->tableName);
        return new SupplierId($nextId);
    }
}