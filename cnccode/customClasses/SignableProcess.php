<?php


namespace CNCLTD;


interface SignableProcess
{
    public function process($signableResponseEnvelope);
}