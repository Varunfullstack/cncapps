<?php

namespace CNCLTD\Supplier;
class Supplier
{
    private $mainContact;

    /**
     * Supplier constructor.
     * @param $mainContact
     */
    public function __construct($mainContact) { $this->mainContact = $mainContact; }


    public function mainContact(): SupplierContact
    {
        return $this->mainContact;
    }

    public function changeMainContact(SupplierContact $supplierContact): void
    {
        if (!$this->isActive) {
            throw new SupplierArchivedException();
        }
        if (!$supplierContact->isActive) {
            throw new NotAssignableSupplierContactException();
        }
        $this->mainContact = $supplierContact;
    }

    public function addContact()
    {

    }


}