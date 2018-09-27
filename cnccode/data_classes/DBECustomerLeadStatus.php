<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 10/01/2018
 * Time: 16:25
 */

class DBECustomerLeadStatus extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("customerleadstatus");
        $this->addColumn("customerLeadStatusID", DA_ID, DA_NOT_NULL, "id");
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "name");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}