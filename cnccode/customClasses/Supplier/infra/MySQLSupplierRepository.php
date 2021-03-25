<?php

namespace CNCLTD\Supplier\infra;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\InvalidEmailException;
use CNCLTD\Exceptions\InvalidIdException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;
use CNCLTD\Exceptions\SupplierContactNotFoundException;
use CNCLTD\Exceptions\URLNotValidException;
use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\Domain\SupplierContact\Title;
use CNCLTD\Supplier\Supplier;
use CNCLTD\Supplier\SupplierAccountCode;
use CNCLTD\Supplier\SupplierAddress1;
use CNCLTD\Supplier\SupplierAddress2;
use CNCLTD\Supplier\SupplierCounty;
use CNCLTD\Supplier\SupplierId;
use CNCLTD\Supplier\SupplierIsActive;
use CNCLTD\Supplier\SupplierName;
use CNCLTD\Supplier\SupplierPaymentMethodId;
use CNCLTD\Supplier\SupplierPostcode;
use CNCLTD\Supplier\SupplierRepository;
use CNCLTD\Supplier\SupplierTown;
use CNCLTD\Supplier\SupplierWebsiteURL;
use dbSweetcode;
use Exception;

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
     * @return SupplierWithMainContactMysqlDTO[]
     * @throws Exception
     */
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
         left join supplierContact mainContact on mainContact.id = supplier.sup_contno",
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
            if ($contact->getId() === $supplierDTO->getMainSupplierContactId()) {
                $mainContact = $contact;
            } else {
                $contacts[] = $contact;
            }
        }
        if (!$mainContact) {
            throw new SupplierContactNotFoundException();
        }
        $supplier = Supplier::create(
            new SupplierId((int)$supplierDTO->getId()),
            new SupplierName($supplierDTO->getName()),
            new SupplierAddress1($supplierDTO->getAddress1()),
            new SupplierAddress2($supplierDTO->getAddress2()),
            new SupplierTown($supplierDTO->getTown()),
            new SupplierCounty($supplierDTO->getCounty()),
            new SupplierPostcode($supplierDTO->getPostcode()),
            new SupplierWebsiteURL($supplierDTO->getWebsiteUrl()),
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

    public function save(Supplier $supplier): void
    {
        $supplierParams = [
            [
                "type"  => "s",
                "value" => $supplier->name()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->address1()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->address2()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->town()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->county()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->postcode()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->websiteURL()->value()
            ],
            [
                "type"  => "i",
                "value" => $supplier->paymentMethodId()->value()
            ],
            [
                "type"  => "s",
                "value" => $supplier->accountCode()->value()
            ],
            [
                "type"  => "i",
                "value" => $supplier->isActive()->value()
            ],
            [
                "type"  => "i",
                "value" => $supplier->mainContact()->id()->value()
            ],
            [
                "type"  => "i",
                "value" => $supplier->id()->value()
            ]
        ];
        if ($this->existsSupplierId($supplier->id())) {
            $query = "update supplier set sup_name = ?, sup_add1 = ?, sup_add2 = ?, sup_town = ?, sup_county = ?, sup_postcode = ?, sup_web_site_url = ?, sup_payno = ?, sup_cnc_accno = ?, active = ?, sup_contno = ? where sup_suppno = ? ";
        } else {
            $query = "insert into supplier(sup_name,
                     sup_add1,
                     sup_add2,
                     sup_town,
                     sup_county,
                     sup_postcode,
                     sup_web_site_url,
                     sup_payno,
                     sup_cnc_accno,
                     active,
                     sup_contno,
                     sup_suppno)
values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?) ";
        }
        $this->sweetCodeDB->preparedQuery(
            $query,
            $supplierParams
        );
        /** @var SupplierContact[] $contacts */
        $contacts           = $supplier->getContacts();
        $updateContactQuery = "update supplierContact set title = ?, position = ?, firstName = ?, lastName = ?, email = ?, phone = ?, active = ? where id = ? ";
        $action             = 'update';
        $insertContactQuery = "insert into supplierContact(title, position, firstName, lastName, email, phone, active, id, supplierId)
values (?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?)";
        foreach ($contacts as $contact) {

            $contactParameters = [
                [
                    "type"  => "s",
                    "value" => $contact->getTitle()->value()
                ],
                [
                    "type"  => "s",
                    "value" => $contact->getPosition()->value()
                ],
                [
                    "type"  => "s",
                    "value" => $contact->getFirstName()->value()
                ],
                [
                    "type"  => "s",
                    "value" => $contact->getLastName()->value()
                ],
                [
                    "type"  => "s",
                    "value" => $contact->getEmail()->value()
                ],
                [
                    "type"  => "s",
                    "value" => $contact->getPhone()->value()
                ],
                [
                    "type"  => "i",
                    "value" => $contact->getActive()->value()
                ],
                [
                    "type"  => "i",
                    "value" => $contact->getId()->value()
                ]
            ];
            $query             = $updateContactQuery;
            $params            = $contactParameters;
            if (!$this->existsSupplierContactId($contact->id())) {
                $query  = $insertContactQuery;
                $action = 'insert';
                $params = array_merge(
                    $contactParameters,
                    [
                        [
                            "type"  => "i",
                            "value" => $supplier->id()->value()
                        ]
                    ]
                );
            }
            if ($action !== 'insert' && !isset($supplier->getContactDirty()[$contact->getId()->value()])) {
                continue;
            }
            $currentUser          = $GLOBALS['auth']->is_authenticated();
            $contactAuditLogQuery = "insert into supplierContactAuditLog(`action`, userId, id, supplierId, title, position, firstName, lastName, email,
                                    phone,
                                    `active`)
select '$action' as `action`,
        $currentUser as `userId`,
       supplierContact.id,
       supplierContact.supplierId,
       supplierContact.title,
       supplierContact.position,
       supplierContact.firstName,
       supplierContact.lastName,
       supplierContact.email,
       supplierContact.phone,
       supplierContact.active
from supplierContact
where id = ? ";
            $this->sweetCodeDB->preparedQuery(
                $query,
                $params
            );
            $this->sweetCodeDB->preparedQuery(
                $contactAuditLogQuery,
                [
                    [
                        "type"  => "i",
                        "value" => $contact->id()->value()
                    ]
                ]
            );
        }
    }

    private function existsSupplierId(SupplierId $id): bool
    {
        $query  = "select count(*) > 0  from supplier where sup_suppno = ?";
        $result = $this->sweetCodeDB->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $id->value()
                ]
            ]
        );
        $row    = $result->fetch_row();
        return (bool)$row[0];
    }

    private function existsSupplierContactId(SupplierContactId $id): bool
    {
        $query  = "select count(*) > 0  from supplierContact where id = ?";
        $result = $this->sweetCodeDB->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $id->value()
                ]
            ]
        );
        $row    = $result->fetch_row();
        return (bool)$row[0];

    }

    /**
     * @return SupplierContactId
     * @throws InvalidIdException
     */
    public function nextContactIdentity(): SupplierContactId
    {
        $nextId = $this->sweetCodeDB->nextid('supplierContact');
        return new SupplierContactId($nextId);
    }
}