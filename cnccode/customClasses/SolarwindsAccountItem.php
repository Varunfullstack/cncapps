<?php


namespace CNCLTD;


use DateTime;

class SolarwindsAccountItem
{
    public $name;
    public $contractId;
    /**
     * @var DateTime
     */
    public $lastSuccessfulBackupDate;
    public $protectedUsers;

    /**
     * SolarwindsAccountItem constructor.
     * @param $name
     * @param $contractId
     * @param $lastSuccessfulBackupDate
     * @param $protectedUsers
     */
    public function __construct($name, $contractId, $lastSuccessfulBackupDate, $protectedUsers)
    {
        $this->name = $name;
        $this->contractId = $contractId;
        $this->lastSuccessfulBackupDate = $lastSuccessfulBackupDate;
        $this->protectedUsers = $protectedUsers;
    }

}