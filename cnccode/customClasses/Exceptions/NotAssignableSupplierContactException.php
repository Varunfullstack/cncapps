<?php

namespace CNCLTD\Exceptions;
class NotAssignableSupplierContactException extends \Exception
{

    /**
     * NotAssignableSupplierContactException constructor.
     */
    public function __construct()
    {
        parent::__construct("Supplier Contact cannot be assigned as main contact because it's inactive");
    }
}