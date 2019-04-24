<?php require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalDocumentWithoutFile.php");

class BUPortalDocument extends Business
{
    /** @var DBEPortalDocument */
    public $dbePortalDocument;
    /** @var DBEPortalDocumentWithoutFile */
    public $dbePortalDocumentWithoutFile;

    public $dbeCallActivity;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePortalDocument = new DBEPortalDocument($this);
        $this->dbePortalDocumentWithoutFile = new DBEPortalDocumentWithoutFile($this);
    }

    function updateDocument(&$dsData, $userfile)
    {
        $this->setMethodName('updateDocument');
        /**
         * Upload new document from local disk
         * @access private
         */
        $this->updateDataAccessObject($dsData, $this->dbePortalDocumentWithoutFile);

        if ($this->dbePortalDocumentWithoutFile->getValue(DBEPortalDocumentWithoutFile::requiresAcceptanceFlag)) {
            $this->dbePortalDocumentWithoutFile->unsetAllOtherRequiresAcceptanceFlag();
        }

        /* file to add? */
        if ($userfile['name'] != '') {
            $this->dbePortalDocument->getRow($this->dbePortalDocumentWithoutFile->getPKValue());
            $this->dbePortalDocument->setValue(
                DBEPortalDocument::file,
                fread(fopen($userfile ['tmp_name'], 'rb'), $userfile ['size'])
            );
            $this->dbePortalDocument->setValue(DBEPortalDocument::filename, ( string )$userfile ['name']);
            $this->dbePortalDocument->setValue(DBEPortalDocument::fileMimeType, ( string )$userfile ['type']);
            $this->dbePortalDocument->updateRow();
        }

        return TRUE;
    }

    function getDocumentByID($ID, &$dsResults)
    {
        $this->dbePortalDocument->setPKValue($ID);
        $this->dbePortalDocument->getRow();
        return ($this->getData($this->dbePortalDocument, $dsResults));
    }

    function getDocuments(&$dsResults)
    {
        $this->dbePortalDocument->getRows('description');
        return ($this->getData($this->dbePortalDocument, $dsResults));
    }

    function deleteDocument($ID)
    {
        $this->setMethodName('deleteDocument');
        return $this->dbePortalDocument->deleteRow($ID);
    }
}