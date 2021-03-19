<?php

namespace CNCLTD\Exceptions;
class SupplierContactMainInactiveException extends \Exception
{

    /**
     * SupplierContactMainInactiveException constructor.
     */
    public function __construct()
    {
        parent::__construct('Supplier Contact cannot be Main and Inactive at the same time');
    }
}