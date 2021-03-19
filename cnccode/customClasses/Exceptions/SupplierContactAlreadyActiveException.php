<?php

namespace CNCLTD\Exceptions;
class SupplierContactAlreadyActiveException extends \Exception
{

    /**
     * SupplierContactAlreadyActiveException constructor.
     */
    public function __construct()
    {
        parent::__construct("The supplier contact is already active!");
    }
}