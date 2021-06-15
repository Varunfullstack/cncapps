<?php


namespace CNCLTD\Exceptions;

class APIException extends \Exception{
    public function __construct($responseCode, $message)
    {        
        $this->state = false;
        $this->error = $message;
        $this->responseCode  = $responseCode;
        parent::__construct($message,$responseCode);        
        http_response_code($responseCode);  
    }
    const success=200;
    const badRequest=400;
    const conflict=409;    
    const notAcceptable=406;
    const unAuthorized=401;
    const notFound=404;
}