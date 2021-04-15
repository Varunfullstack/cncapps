<?php

namespace CNCLTD\DUOApi\AuthLog;
class AuthLogsMetadata
{
    /** @var string[] */
    private $nextOffset;
    private $totalObjects;

    public function nextOffset(): ?array
    {
        return $this->nextOffset;
    }

    public function totalObjects()
    {
        return $this->totalObjects;
    }


}