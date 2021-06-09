<?php

namespace CNCLTD\Business;

use Exception;

class StandardTextNotFoundException extends Exception
{

    /**
     * StandardTextNotFoundException constructor.
     * @param $ID
     */
    public function __construct($ID)
    {
        parent::__construct("Could not find a Standard Text with the given Id: $ID");
    }
}