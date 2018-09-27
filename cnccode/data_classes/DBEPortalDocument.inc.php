<?php

require_once($cfg["path_dbe"] . '/DBEPortalDocumentWithoutFile.php');

class DBEPortalDocument extends DBEPortalDocumentWithoutFile
{
    const filename = "filename";
    const file = "file";
    const fileMimeType = "fileMimeType";

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(self::filename, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::file, DA_BLOB, DA_ALLOW_NULL);
        $this->addColumn(self::fileMimeType, DA_STRING, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>