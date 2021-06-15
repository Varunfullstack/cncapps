<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\InvalidEmailException;
use CNCLTD\Exceptions\InvalidIdException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;
use CNCLTD\Exceptions\URLNotValidException;
use CNCLTD\Supplier\CreateSupplierContactRequest;
use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierRepository;
use Exception;

class CreateSupplierContact
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
     * @param CreateSupplierContactRequest $request
     * @throws ContactAlreadyExistsException
     * @throws EmptyStringException
     * @throws InvalidEmailException
     * @throws InvalidIdException
     * @throws StringTooLongException
     * @throws SupplierContactMainInactiveException
     * @throws URLNotValidException
     */
    public function __invoke(CreateSupplierContactRequest $request)
    {

        $repo     = new MySQLSupplierRepository();
        $supplier = $repo->getById($request->getSupplierId());
        if (!$supplier) {
            throw new Exception('Not found');
        }
        $newContactId = $repo->nextContactIdentity();
        $supplier->addContact(
            $newContactId,
            $request->getTitle(),
            $request->getPosition(),
            $request->getFirstName(),
            $request->getLastName(),
            $request->getPhone(),
            $request->getEmail(),
            new Active(true)
        );
        $repo->save($supplier);
    }
}