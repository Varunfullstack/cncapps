<?php

require_once($cfg["path_dbe"] . '/DBEPortalDocumentWithoutFile.php');

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