<?php

namespace CNCLTD\LabtechRepo;

use PDO;

class LabtechPDORepo
{
    /**
     * @var PDO
     */
    private $labTechDB;

    public function __construct()
    {
        $dsn             = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options         = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        $this->labTechDB = new PDO($dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options);
    }

    public function getComputerNameForComputerId($computerId)
    {
        $statement = $this->labTechDB->prepare("SELECT NAME FROM computers WHERE ComputerID = ?");
        if (!$statement->execute([$computerId])) {
            return null;
        }
        return $statement->fetchColumn(0);
    }
}