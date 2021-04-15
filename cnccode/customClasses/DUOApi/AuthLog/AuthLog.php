<?php

namespace CNCLTD\DUOApi\AuthLog;
class AuthLog
{
    /** @var AccessDevice */
    private $accessDevice;
    /** @var Application */
    private $application;
    /** @var User */
    private $user;

    private $timestamp;
    private $email;
    private $result;

    public function accessDevice(): AccessDevice
    {
        return $this->accessDevice;
    }

    public function application(): Application
    {
        return $this->application;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function timestamp()
    {
        return $this->timestamp;
    }

    public function email()
    {
        return $this->email;
    }

    public function result()
    {
        return $this->result;
    }

}