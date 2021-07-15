<?php

use CNCLTD\Data\DBConnect;
use CNCLTD\SortableDBE;

/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 10/01/2018
 * Time: 16:25
 */
class DBECustomerLeadStatus extends DBEntity
{
    use SortableDBE;

    const id             = "id";
    const name           = "name";
    const appearOnScreen = "appearOnScreen";
    const sortOrder      = "sortOrder";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("customerleadstatus");
        $this->addColumn(self::id, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::appearOnScreen, DA_BOOLEAN, DA_NOT_NULL, null, 0);
        $this->addColumn(self::sortOrder, DA_FLOAT, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    protected function getSortOrderForItem($id)
    {
        $this->getRow($id);
        return $this->getValue(self::sortOrder);
    }

    protected function getSortOrderColumnName()
    {
        return $this->getDBColumnName(self::sortOrder);
    }

    protected function getDB()
    {
        global $db;
        return $db;
    }

    public function hasName($name)
    {
        return DBConnect::fetchOne("select * from customerleadstatus where name=:name", ["name" => $name]);
    }
}