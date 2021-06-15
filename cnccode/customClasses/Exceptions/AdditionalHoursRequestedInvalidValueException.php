<?php

namespace CNCLTD\Exceptions;
class AdditionalHoursRequestedInvalidValueException extends \Exception
{

    /**
     * AdditionalHoursRequestedInvalidValueException constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        parent::__construct("The value $value provided is not valid it must be one of [1,2,3,4]");
    }
}