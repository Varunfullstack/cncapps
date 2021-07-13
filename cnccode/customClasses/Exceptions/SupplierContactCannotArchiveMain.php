<?php

namespace CNCLTD\Exceptions;
use Exception;

class SupplierContactCannotArchiveMain extends Exception
{

    /**
     * SupplierContactCannotArchiveMain constructor.
     */
    public function __construct()
    {
        parent::__construct("Main contact cannot be archived!");
    }
}