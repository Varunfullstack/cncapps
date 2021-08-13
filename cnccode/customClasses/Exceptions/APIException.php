<?php

namespace CNCLTD\Exceptions;

use Exception;

class APIException extends Exception
{
    public $state;
    public $error;
    public $responseCode;

    public function __construct($responseCode, $message)
    {
        $this->state        = false;
        $this->error        = $message;
        $this->responseCode = $responseCode;
        parent::__construct($message);
        http_response_code($responseCode);
    }

    public const success       = 200;
    public const badRequest    = 400;
    public const conflict      = 409;
    public const notAcceptable = 406;
    public const unAuthorized  = 401;
    public const notFound      = 404;
}