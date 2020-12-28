<?php


namespace CNCLTD\TwigDTOs;


class PrimaryMainContactNotAuthorisedNotification
{
    public $primaryMainContactFirstName;
    public $contactFirstName;
    public $contactLastName;
    public $contactSupportLevel;

    /**
     * PrimaryMainContactNotAuthorisedNotification constructor.
     * @param $primaryMainContactFirstName
     * @param $contactFirstName
     * @param $contactLastName
     * @param $contactSupportLevel
     */
    public function __construct($primaryMainContactFirstName, $contactFirstName, $contactLastName, $contactSupportLevel)
    {
        $this->primaryMainContactFirstName = $primaryMainContactFirstName;
        $this->contactFirstName = $contactFirstName;
        $this->contactLastName = $contactLastName;
        $this->contactSupportLevel = $contactSupportLevel;
    }


}