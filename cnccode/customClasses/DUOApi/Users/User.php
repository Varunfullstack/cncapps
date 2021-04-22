<?php

namespace CNCLTD\DUOApi\Users;
class User
{
    private $email;
    private $firstName;
    private $lastLogin;
    private $lastName;
    private $status;

    public function email()
    {
        return $this->email;
    }

    public function firstName()
    {
        return $this->firstName;
    }

    public function lastLogin()
    {
        return $this->lastLogin;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function status()
    {
        return $this->status;
    }

    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}