<?php require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPortalDocumentWithoutFile extends DBEntity
{
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("portal_document");
        $this->addColumn("portalDocumentID", DA_ID, DA_NOT_NULL);
        $this->addColumn("description", DA_STRING, DA_NOT_NULL);
        $this->addColumn("mainContactOnlyFlag", DA_YN, DA_NOT_NULL);
        $this->addColumn("requiresAcceptanceFlag", DA_YN, DA_NOT_NULL);
        $this->addColumn("createdDate", DA_DATE, DA_NOT_NULL);
        $this->addColumn("createdUserID", DA_ID, DA_NOT_NULL);
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

class DBEPortalDocument extends DBEPortalDocumentWithoutFile
{
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("filename", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("file", DA_BLOB, DA_ALLOW_NULL);
        $this->addColumn("fileMimeType", DA_STRING, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>