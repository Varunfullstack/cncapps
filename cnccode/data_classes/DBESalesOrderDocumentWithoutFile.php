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

    const salesOrderDocumentID = "salesOrderDocumentID";
    const ordheadID = "ordheadID";
    const description = "description";
    const createdDate = "createdDate";
    const createdUserID = "createdUserID";

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
        $this->addColumn(
            self::salesOrderDocumentID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createdDate,
            DA_DATE,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createdUserID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

}