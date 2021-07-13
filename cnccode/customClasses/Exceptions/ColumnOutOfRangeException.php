<?php

namespace CNCLTD\Exceptions;
use Exception;

class ColumnOutOfRangeException extends Exception
{

    /**
     * ColumnOutOfRangeException constructor.
     * @param $columnName
     */
    public function __construct($columnName)
    {
        parent::__construct("Column $columnName  out of range");
    }
}