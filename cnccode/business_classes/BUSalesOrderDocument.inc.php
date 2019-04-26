<?php require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocumentWithoutFile.php");

class BUSalesOrderDocument extends Business
{
    /** @var DBESalesOrderDocument */
    public $dbeSalesOrderDocument;
    /** @var DBESalesOrderDocumentWithoutFile */
    public $dbeSalesOrderDocumentWithoutFile;
    /** @var DBECallActivity */
    public $dbeCallActivity;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSalesOrderDocument = new DBESalesOrderDocument($this);
        $this->dbeSalesOrderDocumentWithoutFile = new DBESalesOrderDocumentWithoutFile($this);
    }

    function updateDocument(&$dsData, $userfile)
    {
        $this->setMethodName('updateDocument');
        /**
         * Upload new document from local disk
         * @access private
         */
        $this->updateDataAccessObject($dsData, $this->dbeSalesOrderDocumentWithoutFile);

        /* file to add? */
        if ($userfile['name']) {
            $this->dbeSalesOrderDocument->getRow($this->dbeSalesOrderDocumentWithoutFile->getPKValue());
            $this->dbeSalesOrderDocument->setValue(
                DBESalesOrderDocument::file,
                fread(fopen($userfile ['tmp_name'], 'rb'), $userfile ['size'])
            );
            $this->dbeSalesOrderDocument->setValue(DBESalesOrderDocument::filename, ( string )$userfile ['name']);
            $this->dbeSalesOrderDocument->setValue(DBESalesOrderDocument::fileMimeType, ( string )$userfile ['type']);
            $this->dbeSalesOrderDocument->setValue(DBESalesOrderDocument::createdDate, date(DATE_MYSQL_DATETIME));
            $this->dbeSalesOrderDocument->setValue(DBESalesOrderDocument::createdUserID, $this->owner->userID);
            $this->dbeSalesOrderDocument->updateRow();
        }

        return TRUE;
    }

    function getDocumentByID($ID, &$dsResults)
    {
        $this->dbeSalesOrderDocument->setPKValue($ID);
        $this->dbeSalesOrderDocument->getRow();
        return ($this->getData($this->dbeSalesOrderDocument, $dsResults));
    }

    function getDocumentsByOrdheadID($ordheadID, &$dsResults)
    {
        $this->dbeSalesOrderDocument->setValue(DBESalesOrderDocument::ordheadID, $ordheadID);
        $this->dbeSalesOrderDocument->getRowsByColumn(DBESalesOrderDocument::ordheadID, 'createdDate');
        return ($this->getData($this->dbeSalesOrderDocument, $dsResults));
    }

    function deleteDocument($ID)
    {
        $this->setMethodName('deleteDocument');
        return $this->dbeSalesOrderDocument->deleteRow($ID);
    }

    function copyDocumentsToOrder($fromOrdheadID, $toOrdheadID)
    {
        $this->setMethodName('copyDocumentsToOrder');
        $dsDocument = new DataSet($this);
        $this->getDocumentsByOrdheadID($fromOrdheadID, $dsDocument);

        $dsNewDocument = new DataSet($this);
        $dsNewDocument->copyColumnsFrom($dsDocument);

        while ($dsDocument->fetchNext()) {
            $dsNewDocument->setUpdateModeInsert();
            $dsNewDocument->row = $dsDocument->row;
            $dsNewDocument->setValue(DBESalesOrderDocument::salesOrderDocumentID, 0);
            $dsNewDocument->setValue(DBESalesOrderDocument::ordheadID, $toOrdheadID);
            $dsNewDocument->post();
        }
        $this->dbeSalesOrderDocument->replicate($dsNewDocument);

    }

}
