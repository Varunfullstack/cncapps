<?php


namespace CNCLTD;


use Psr\Log\LoggerInterface;

interface SignableProcess
{
    public function process($signableResponseEnvelope,LoggerInterface $logger);
}