<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:27
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESalesOrderDocumentWithoutFile extends DBEntity
{
    /**
     * portals constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("salesorder_document");
        $this->addColumn("salesOrderDocumentID", DA_ID, DA_NOT_NULL);
        $this->addColumn("ordheadID", DA_ID, DA_ALLOW_NULL);
        $this->addColumn("description", DA_STRING, DA_NOT_NULL);
        $this->addColumn("createdDate", DA_DATE, DA_NOT_NULL);
        $this->addColumn("createdUserID", DA_ID, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

}