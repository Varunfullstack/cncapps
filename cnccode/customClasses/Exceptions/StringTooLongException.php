<?php

namespace CNCLTD\Exceptions;
class StringTooLongException extends \Exception
{

    /**
     * StringTooLongException constructor.
     * @param int $maxLength
     */
    public function __construct(int $maxLength)
    {
        parent::__construct("String must be at most {$maxLength} characters long");
    }
}