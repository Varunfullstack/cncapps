<?php

namespace CNCLTD\StreamOneProcessing;
use JsonSerializable;

class StreamOneLicenseData implements JsonSerializable
{
    private $sku;
    private $endCustomerEmail;

    /**
     * StreamOneLicenseData constructor.
     * @param $sku
     * @param $endCustomerEmail
     */
    public function __construct($sku, $endCustomerEmail)
    {
        $this->sku              = $sku;
        $this->endCustomerEmail = $endCustomerEmail;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return strtolower($this->sku);
    }

    /**
     * @return mixed
     */
    public function getEndCustomerEmail()
    {
        return strtolower($this->endCustomerEmail);
    }


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}