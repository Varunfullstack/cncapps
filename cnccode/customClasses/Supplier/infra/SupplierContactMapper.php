<?php

namespace CNCLTD\Supplier\infra;

use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;

class SupplierContactMapper
{
    public static function toJSONArray(SupplierContact $supplierContact)
    {
        return [
            "id"        => $supplierContact->getId()->value(),
            "title"     => $supplierContact->getTitle()->value(),
            "position"  => $supplierContact->getPosition()->value(),
            "firstName" => $supplierContact->getFirstName()->value(),
            "lastName"  => $supplierContact->getLastName()->value(),
            "phone"     => $supplierContact->getPhone()->value(),
            "email"     => $supplierContact->getEmail()->value(),
            "active"    => $supplierContact->getActive()->value(),
        ];
    }
}