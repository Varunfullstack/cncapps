<?php


namespace CNCLTD\TwigDTOs;


class NotAuthorisedContactDTO
{
    public $supportContacts;
    public $contactFirstName;

    /**
     * NotAuthorisedContactDTO constructor.
     * @param $supportContacts
     * @param $contactFirstName
     */
    public function __construct($supportContacts, $contactFirstName)
    {
        $this->supportContacts = $supportContacts;
        $this->contactFirstName = $contactFirstName;
    }

}