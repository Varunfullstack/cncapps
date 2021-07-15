<?php


namespace CNCLTD\Exceptions;


use Exception;

class JsonHttpException extends Exception
{
    private $responseCode;

    /**
     * JsonHttpException constructor.
     * @param $responseCode
     * @param $message
     * @param array $additionalData
     */
    public function __construct($responseCode, $message="", $additionalData = [])
    {
        $encodedMessage = json_encode(["status" => "error", "message" => $message, "extraData" => $additionalData]);
        parent::__construct($encodedMessage);
        $this->responseCode = $responseCode;
    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }


}