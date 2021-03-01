<?php

namespace CNCLTD\Exceptions;
class SupplierArchivedException extends \Exception
{

    /**
     * SupplierArchivedException constructor.
     */
    public function __construct()
    {
        parent::__construct("Archived supplier!!");
    }
}