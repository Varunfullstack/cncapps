<?php

namespace CNCLTD\Supplier\usecases;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\InvalidEmailException;
use CNCLTD\Exceptions\InvalidIdException;
use CNCLTD\Exceptions\StringTooLongException;
use CNCLTD\Exceptions\SupplierContactCannotArchiveMain;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;
use CNCLTD\Exceptions\SupplierContactNotFoundException;
use CNCLTD\Exceptions\URLNotValidException;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierRepository;
use CNCLTD\Supplier\UpdateSupplierContactRequest;
use Exception;

class UpdateSupplierContact
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
     * @param UpdateSupplierContactRequest $request
     * @throws ContactAlreadyExistsException
     * @throws EmptyStringException
     * @throws InvalidEmailException
     * @throws InvalidIdException
     * @throws StringTooLongException
     * @throws SupplierContactMainInactiveException
     * @throws SupplierContactNotFoundException
     * @throws URLNotValidException
     * @throws SupplierContactCannotArchiveMain
     */
    public function __invoke(UpdateSupplierContactRequest $request)
    {

        $repo     = new MySQLSupplierRepository();
        $supplier = $repo->getById($request->getSupplierId());
        if (!$supplier) {
            throw new Exception('Not found');
        }
        $supplier->updateSupplierContactFromRequest($request);
        $repo->save($supplier);
    }

}