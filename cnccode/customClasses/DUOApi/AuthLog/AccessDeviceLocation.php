<?php

namespace CNCLTD\DUOApi\AuthLog;
class AccessDeviceLocation
{
    private $city;
    private $country;
    private $state;

    public function city()
    {
        return $this->city;
    }

    public function country()
    {
        return $this->country;
    }

    public function state()
    {
        return $this->state;
    }
}