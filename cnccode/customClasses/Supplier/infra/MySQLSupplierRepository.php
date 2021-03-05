<?php

namespace CNCLTD\Supplier\infra;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\InvalidEmailException;
use CNCLTD\Exceptions\InvalidIdException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;
use CNCLTD\Exceptions\URLNotValidException;
use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\Domain\SupplierContact\Title;
use CNCLTD\Supplier\Supplier;
use CNCLTD\Supplier\SupplierAccountCode;
use CNCLTD\Supplier\SupplierAddress1;
use CNCLTD\Supplier\SupplierAddress2;
use CNCLTD\Supplier\SupplierCounty;
use CNCLTD\Supplier\SupplierFax;
use CNCLTD\Supplier\SupplierId;
use CNCLTD\Supplier\SupplierIsActive;
use CNCLTD\Supplier\SupplierName;
use CNCLTD\Supplier\SupplierPaymentMethodId;
use CNCLTD\Supplier\SupplierPhone;
use CNCLTD\Supplier\SupplierPostcode;
use CNCLTD\Supplier\SupplierRepository;
use CNCLTD\Supplier\SupplierTown;
use CNCLTD\Supplier\SupplierWebsiteURL;
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
       supplier.active                                          as active,
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

    /**
     * @param SupplierId $supplierId
     * @return Supplier
     * @throws ContactAlreadyExistsException
     * @throws EmptyStringException
     * @throws InvalidEmailException
     * @throws InvalidIdException
     * @throws StringTooLongException
     * @throws SupplierContactMainInactiveException
     * @throws URLNotValidException
     */
    public function getSupplierWithContactsById(SupplierId $supplierId)
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
        /** @var SupplierMySQLDTO $supplierDTO */
        $supplierDTO = $statement->fetch_object(SupplierMySQLDTO::class);
        if (!$supplierId) {
            throw new Exception('Supplier not found');
        }
        $supplierContactsStatement = $this->sweetCodeDB->preparedQuery(
            'select * from supplierContact where supplierId = ?',
            [
                [
                    "type"  => "i",
                    "value" => $supplierId->value()
                ]
            ]
        );
        if (!$supplierContactsStatement) {
            throw new Exception('Failed to retrieve supplier contacts');
        }
        $mainContact = null;
        $contacts    = [];
        /** @var SupplierContactMysqlDTO $contact */
        while ($contact = $supplierContactsStatement->fetch_object(SupplierContactMysqlDTO::class)) {
            if ($contact->getIsMain()) {
                $mainContact = $contact;
            } else {
                $contacts[] = $contact;
            }
        }
        $supplier = Supplier::create(
            new SupplierId((int)$supplierDTO->getId()),
            new SupplierName($supplierDTO->getName()),
            new SupplierAddress1($supplierDTO->getAddress1()),
            new SupplierAddress2($supplierDTO->getAddress2()),
            new SupplierTown($supplierDTO->getTown()),
            new SupplierCounty($supplierDTO->getCounty()),
            new SupplierPostcode($supplierDTO->getPostcode()),
            new SupplierPhone($supplierDTO->getPhone()),
            new SupplierWebsiteURL($supplierDTO->getWebsiteUrl()),
            new SupplierFax($supplierDTO->getFax()),
            new SupplierPaymentMethodId($supplierDTO->getPayMethodId()),
            new SupplierAccountCode($supplierDTO->getCNCAccountCode()),
            new SupplierIsActive($supplierDTO->getActive()),
            new SupplierContactId($mainContact->getId()),
            new Position($mainContact->getPosition()),
            new Title($mainContact->getTitle()),
            new FirstName($mainContact->getFirstName()),
            new LastName($mainContact->getLastName()),
            new Email($mainContact->getEmail()),
            new Phone($mainContact->getPhone())
        );
        foreach ($contacts as $contact) {
            $supplier->addContact(
                new SupplierContactId($contact->getId()),
                new Title($contact->getTitle()),
                new Position($contact->getPosition()),
                new FirstName($contact->getFirstName()),
                new LastName($contact->getLastName()),
                new Phone($contact->getPhone()),
                new Email($contact->getEmail()),
                new Active($contact->getActive())
            );
        }
        return $supplier;
    }
}