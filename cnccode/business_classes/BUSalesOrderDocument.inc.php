<?php require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBESalesOrderDocumentWithoutFile.php");

class BUSalesOrderDocument extends Business
{
    var $dbeSalesOrderDocument = "";
    var $dbeSalesOrderDocumentWithoutFile = "";
    var $dbeCallActivity = "";

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
        if ($userfile['name'] != '') {
            $this->dbeSalesOrderDocument->getRow($this->dbeSalesOrderDocumentWithoutFile->getPKValue());
            $this->dbeSalesOrderDocument->setValue('file',
                                                   fread(fopen($userfile ['tmp_name'], 'rb'), $userfile ['size']));
            $this->dbeSalesOrderDocument->setValue('filename', ( string )$userfile ['name']);
            $this->dbeSalesOrderDocument->setValue('fileMimeType', ( string )$userfile ['type']);
            $this->dbeSalesOrderDocument->setValue('createdDate', date(CONFIG_MYSQL_DATETIME));
            $this->dbeSalesOrderDocument->setValue('createdUserID', $this->owner->userID);
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
        $this->dbeSalesOrderDocument->setValue('ordheadID', $ordheadID);
        $this->dbeSalesOrderDocument->getRowsByColumn('ordheadID', 'createdDate');
        return ($this->getData($this->dbeSalesOrderDocument, $dsResults));
    }

    function deleteDocument($ID)
    {
        $this->setMethodName('deleteDocument');
        if ($this->canDelete($ID)) {
            return $this->dbeSalesOrderDocument->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    function copyDocumentsToOrder($fromOrdheadID, $toOrdheadID)
    {
        $this->setMethodName('copyDocumentsToOrder');

        $this->getDocumentsByOrdheadID($fromOrdheadID, $dsDocument);

        $dsNewDocument = new DataSet($this);
        $dsNewDocument->copyColumnsFrom($dsDocument);

        while ($dsDocument->fetchNext()) {
            $dsNewDocument->setUpdateModeInsert();
            $dsNewDocument->row = $dsDocument->row;
            $dsNewDocument->setValue('salesOrderDocumentID', 0);
            $dsNewDocument->setValue('ordheadID', $toOrdheadID);
            $dsNewDocument->post();
        }
        $this->dbeSalesOrderDocument->replicate($dsNewDocument);

    }

    function canDelete($ID)
    {
        return TRUE;
    }

}// End of class
?>