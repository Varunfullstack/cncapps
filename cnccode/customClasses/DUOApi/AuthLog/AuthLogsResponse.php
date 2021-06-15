<?php

namespace CNCLTD\DUOApi\AuthLog;
class AuthLogsResponse
{
    private $stat;
    /** @var AuthLogResponseItem */
    private $response;

    public function stat()
    {
        return $this->stat;
    }

    public function response(): AuthLogResponseItem
    {
        return $this->response;
    }
}