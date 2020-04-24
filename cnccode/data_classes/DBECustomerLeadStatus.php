<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 10/01/2018
 * Time: 16:25
 */

class DBECustomerLeadStatus extends DBEntity
{
    const id = "id";
    const name = "name";
    const appearOnScreen = "appearOnScreen";

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
        $this->addColumn(self::id, DA_ID, DA_NOT_NULL, "id");
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL, "name");
        $this->addColumn(self::appearOnScreen, DA_BOOLEAN, DA_NOT_NULL, null, 0);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}