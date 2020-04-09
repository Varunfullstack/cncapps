<?php require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocumentWithoutFile.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");

class BUPortalCustomerDocument extends Business
{
    public $dbePortalCustomerDocument;
    public $dbePortalCustomerDocumentWithoutFile;
    public $dbeCallActivity;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePortalCustomerDocument = new DBEPortalCustomerDocument($this);
        $this->dbePortalCustomerDocumentWithoutFile = new DBEPortalCustomerDocumentWithoutFile($this);
    }

    function updateDocument(&$dsData, $userfile)
    {
        $this->setMethodName('updateDocument');
        /**
         * Upload new document from local disk
         * @access private
         */
        $this->updateDataAccessObject($dsData, $this->dbePortalCustomerDocumentWithoutFile);

        /* file to add? */
        if ($userfile['name']) {


            $this->dbePortalCustomerDocument->getRow($this->dbePortalCustomerDocumentWithoutFile->getPKValue());
            $this->dbePortalCustomerDocument->setValue(
                DBEPortalCustomerDocument::file,
                fread(fopen($userfile ['tmp_name'], 'rb'), $userfile ['size'])
            );
            echo '<h1>';
            var_dump($userfile);
            echo '</h1>';
            $this->dbePortalCustomerDocument->setValue(
                DBEPortalCustomerDocument::filename,
                $userfile ['name']
            );
            $this->dbePortalCustomerDocument->setValue(
                DBEPortalCustomerDocument::fileMimeType,
                ( string )$userfile ['type']
            );
            $this->dbePortalCustomerDocument->updateRow();
        }

        return TRUE;
    }

    function getDocumentByID($ID, &$dsResults)
    {
        $this->dbePortalCustomerDocument->setPKValue($ID);
        $this->dbePortalCustomerDocument->getRow();
        return ($this->getData($this->dbePortalCustomerDocument, $dsResults));
    }

    /**
     * @param int|string $customerID
     * @param DataSet $dsResults
     * @return bool
     */
    function getDocumentsByCustomerID($customerID, &$dsResults)
    {
        $this->dbePortalCustomerDocument->setValue(DBEPortalCustomerDocument::customerID, $customerID);
        $this->dbePortalCustomerDocument->getRowsByColumn(DBEPortalCustomerDocument::customerID, 'description');
        return ($this->getData($this->dbePortalCustomerDocument, $dsResults));
    }

    /**
     * @param $customerID
     * @return mixed
     */
    function hasContractDocumentByCustomerId($customerID)
    {
        return $this->dbePortalCustomerDocumentWithoutFile->hasContractDocumentByCustomerId($customerID);
    }

    function deleteDocument($ID)
    {
        $this->setMethodName('deleteDocument');
        return $this->dbePortalCustomerDocument->deleteRow($ID);
    }
}
