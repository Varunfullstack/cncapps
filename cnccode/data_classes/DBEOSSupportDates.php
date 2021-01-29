<?php
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOSSupportDates extends DBEntity
{
    const id               = "id";
    const name             = "name";
    const version          = "version";
    const availabilityDate = "availabilityDate";
    const endOfLifeDate    = "endOfLifeDate";
    const isServer         = 'isServer';
    const friendlyName     = "friendlyName";

    public function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("OSSupportDates");
        $this->addColumn(self::id, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::version, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::availabilityDate, DA_DATE, DA_NOT_NULL);
        $this->addColumn(self::endOfLifeDate, DA_DATE, DA_ALLOW_NULL);
        $this->addColumn(self::isServer, DA_BOOLEAN, DA_NOT_NULL, 0);
        $this->addColumn(self::friendlyName, DA_STRING, DA_ALLOW_NULL);
        $this->setAddColumnsOff();
        $this->setPK(self::id);
    }

    public function getEndOfLifeRows()
    {
        $query = "select {$this->getFullDBColumnNamesAsString()} from {$this->getTableName()} where {$this->getDBColumnName(self::endOfLifeDate)} is not null";
        $this->queryString = $query;
        $this->getRows();
    }
}