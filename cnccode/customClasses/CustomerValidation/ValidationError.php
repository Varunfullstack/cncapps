<?php


namespace CNCLTD\CustomerValidation;


class ValidationError
{
    private $description;

    public function __construct($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

}