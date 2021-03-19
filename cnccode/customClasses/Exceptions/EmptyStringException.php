<?php

namespace CNCLTD\Exceptions;
class EmptyStringException extends \Exception
{

    /**
     * EmptyStringException constructor.
     * @param $fieldName
     */
    public function __construct($fieldName)
    {
        parent::__construct("Empty string is forbidden for $fieldName", 583);
    }
}