<?php

namespace CNCLTD\Exceptions;
use Exception;

class SupplierContactArchivedException extends Exception
{

    /**
     * SupplierContactArchivedException constructor.
     */
    public function __construct()
    {
        parent::__construct("This contact is archived, cannot be modified while archived");
    }
}