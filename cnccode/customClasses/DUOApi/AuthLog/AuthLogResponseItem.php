<?php

namespace CNCLTD\DUOApi\AuthLog;
class AuthLogResponseItem
{
    /**
     * @var AuthLog[]
     */
    private $authLogs;
    /** @var AuthLogsMetadata */
    private $metadata;

    /**
     * @return AuthLog[]
     */
    public function authLogs(): array
    {
        return $this->authLogs;
    }

    public function metadata(): AuthLogsMetadata
    {
        return $this->metadata;
    }

}