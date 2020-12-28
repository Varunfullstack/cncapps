<?php


namespace CNCLTD\Exceptions;


class ContactNotFoundException extends \Exception
{

    /**
     * ContactNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Contact not found');
    }
}