<?php

namespace CNCLTD\Exceptions;
class SupplierContactNotFoundException extends \Exception
{

    /**
     * SupplierContactNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Supplier contact not found');
    }
}