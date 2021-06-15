<?php

namespace CNCLTD\Exceptions;
class SupplierContactAlreadyArchivedException extends \Exception
{

    /**
     * SupplierContactAlreadyActiveException constructor.
     */
    public function __construct()
    {
        parent::__construct("The supplier contact is already archived!");
    }
}