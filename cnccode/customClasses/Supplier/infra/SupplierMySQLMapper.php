<?php

namespace CNCLTD\Supplier\infra;

use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;
use CNCLTD\Supplier\Supplier;

class SupplierMySQLMapper
{
    public static function toJSONArray(Supplier $supplier): array
    {
        return [
            "id"                    => $supplier->id()->value(),
            "mainSupplierContactId" => $supplier->mainContact()->getId()->value(),
            "town"                  => $supplier->town()->value(),
            "county"                => $supplier->county()->value(),
            "postcode"              => $supplier->postcode()->value(),
            "name"                  => $supplier->name()->value(),
            "address1"              => $supplier->address1()->value(),
            "address2"              => $supplier->address2()->value(),
            "websiteURL"            => $supplier->websiteURL()->value(),
            "isActive"              => $supplier->isActive()->value(),
            "paymentMethodId"       => $supplier->paymentMethodId()->value(),
            "accountCode"           => $supplier->accountCode()->value(),
            "contacts"              => array_map(
                function (SupplierContact $contact) {
                    return SupplierContactMapper::toJSONArray($contact);
                },
                $supplier->getContacts()
            ),
        ];
    }
}