<?php

namespace CNCLTD\Exceptions;
class URLNotValidException extends \Exception
{

    /**
     * URLNotValidException constructor.
     */
    public function __construct()
    {
        parent::__construct('Not valid URL');
    }
}