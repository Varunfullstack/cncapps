<?php


namespace CNCLTD;


class TokenData
{
    public $id;
    public $token;
    public $serviceRequestId;

    public static function fromDB($array)
    {
        $result = new TokenData();
        $result->id = $array['id'];
        $result->token = $array['token'];
        $result->serviceRequestId = $array['serviceRequestId'];
        return $result;
    }
}