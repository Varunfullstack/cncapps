<?php use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocumentWithoutFile.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");

class BUPortalCustomerDocument extends Business
{
    const DUO_USERS_AND_LOGS = 'DUO Users and Logs';
    public $dbePortalCustomerDocument;
    public $dbePortalCustomerDocumentWithoutFile;
    public $dbeCallActivity;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePortalCustomerDocument            = new DBEPortalCustomerDocument($this);
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

    public function addOrUpdateDUOClientReportDocument(int $customerId, $spreadsheet)
    {
        // check if the customer already has a DUO document
        $duoDocument  = $this->getCustomerDUODocument($customerId);
        $tempFileName = @tempnam('e:\temp', 'duoDocument');
        $writer       = new Xlsx($spreadsheet);
        $writer->save($tempFileName);
        $data = file_get_contents($tempFileName);
        $duoDocument->setValue(DBEPortalCustomerDocument::file, $data);
        if (!$duoDocument->getPKValue()) {
            $duoDocument->insertRow();
        } else {
            $duoDocument->updateRow();
        }
    }

    private function getCustomerDUODocument(int $customerId)
    {
        $portalCustomerDocument = new DBEPortalCustomerDocument($this);
        $portalCustomerDocument->getDUODocumentForCustomer($customerId, self::DUO_USERS_AND_LOGS);
        if (!$portalCustomerDocument->rowCount()) {
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::customerID, $customerId);
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::description, self::DUO_USERS_AND_LOGS);
            $portalCustomerDocument->setValue(
                DBEPortalCustomerDocument::createdDate,
                (new DateTime())->format(DATE_MYSQL_DATETIME)
            );
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::createdUserID, 0);
            $portalCustomerDocument->setValue(
                DBEPortalCustomerDocument::fileMimeType,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::filename, self::DUO_USERS_AND_LOGS . '.xlsx');
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::mainContactOnlyFlag, 'Y');
            $portalCustomerDocument->setValue(DBEPortalCustomerDocument::customerContract, 0);
        }
        return $portalCustomerDocument;
    }
}
