<?php


class DBEOSSupportDates extends DBEntity
{
    const id = "id";
    const name = "name";
    const version = "version";
    const availabilityDate = "availabilityDate";
    const endOfLifeDate = "endOfLifeDate";

    public function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("OSSupportDates");
        $this->addColumn(self::id, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::version, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::availabilityDate, DA_DATE, DA_NOT_NULL);
        $this->addColumn(self::endOfLifeDate, DA_DATE, DA_ALLOW_NULL);
        $this->setAddColumnsOff();
        $this->setPK(self::id);
    }
}