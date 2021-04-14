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
}