<?php

namespace CNCLTD\DUOApi\AuthLog;
class AccessDevice
{
    /** @var AccessDeviceLocation */
    private $location;
    private $ip;

    public function location(): AccessDeviceLocation
    {
        return $this->location;
    }

    public function ip()
    {
        return $this->ip;
    }

}