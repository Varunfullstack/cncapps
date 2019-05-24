<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:41
 */

require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");

class DBEContactAudit extends DBEContact
{
    const createdByContactId = "createdByContactId";
    const createdByUserId = "createdByUserId";
    const createdAt = "createdAt";
    const action = "action";

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
        $this->setTableName("contactauditlog");
        $this->setAddColumnsOn();
        $this->addColumn(
            self::action,
            DA_STRING,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::createdAt,
            DA_DATETIME,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::createdByUserId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'userId'
        );

        $this->addColumn(
            self::createdByContactId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "contactId"
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}