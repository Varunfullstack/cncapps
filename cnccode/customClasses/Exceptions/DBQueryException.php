<?php

namespace CNCLTD\Exceptions;
class DBQueryException extends \Exception
{

    /**
     * DBQueryException constructor.
     * @param string $Error
     * @param string $queryString
     */
    public function __construct(string $Error, string $queryString)
    {
        parent::__construct("Query Error: $Error, with query $queryString");
    }
}