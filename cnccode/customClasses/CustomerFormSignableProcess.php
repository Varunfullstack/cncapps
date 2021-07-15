<?php


namespace CNCLTD;


use DBESignableEnvelope;
use Psr\Log\LoggerInterface;

class CustomerFormSignableProcess implements SignableProcess
{
    private $quotationID;

    public function __construct($quotationID)
    {
        $this->quotationID = $quotationID;
    }

    public function process($signableEnvelopeResponse, LoggerInterface $logger)
    {
        switch ($signableEnvelopeResponse['action']) {
            case 'signed-envelope-complete':
                break;
            case 'cancelled-envelope':
                break;
            case 'bounced-envelope':
                break;

        }

        $dbeSignableEnvelope = new DBESignableEnvelope($this);


    }
}