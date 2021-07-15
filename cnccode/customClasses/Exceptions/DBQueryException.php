<?php

namespace CNCLTD\Exceptions;
use Exception;

class DBQueryException extends Exception
{

    /**
     * DBQueryException constructor.
     * @param string|null $Error
     * @param string $queryString
     */
    public function __construct(?string $Error, string $queryString)
    {
        parent::__construct("Query Error: $Error, with query $queryString");
    }
}