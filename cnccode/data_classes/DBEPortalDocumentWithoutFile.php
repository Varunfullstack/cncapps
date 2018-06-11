<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:24
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPortalDocumentWithoutFile extends DBEntity
{
    const portalDocumentID = "portalDocumentID";
    const description = "description";
    const mainContactOnlyFlag = "mainContactOnlyFlag";
    const requiresAcceptanceFlag = "requiresAcceptanceFlag";
    const createdDate = "createdDate";
    const createdUserID = "createdUserID";

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("portal_document");
        $this->addColumn(self::portalDocumentID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::mainContactOnlyFlag, DA_YN, DA_NOT_NULL);
        $this->addColumn(self::requiresAcceptanceFlag, DA_YN, DA_NOT_NULL);
        $this->addColumn(self::createdDate, DA_DATE, DA_NOT_NULL);
        $this->addColumn(self::createdUserID, DA_ID, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Resets all but current PK row
     *
     */
    function unsetAllOtherRequiresAcceptanceFlag()
    {
        $statement =
            "UPDATE
        portal_document
      SET
        requiresAcceptanceFlag = 'N'
      WHERE
        portalDocumentID <> " . $this->getPKValue();
        $this->setQueryString($statement);
        $this->runQuery();
    }
}