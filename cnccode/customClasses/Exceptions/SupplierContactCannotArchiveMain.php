<?php

namespace CNCLTD\Exceptions;
class SupplierContactCannotArchiveMain extends \Exception
{

    /**
     * SupplierContactCannotArchiveMain constructor.
     */
    public function __construct()
    {
        parent::__construct("Main contact cannot be archived!");
    }
}