<?php
global $cfg;
require_once($cfg["path_dbe"] . '/DBEPortalCustomerDocumentWithoutFile.inc.php');

class DBEPortalCustomerDocument extends DBEPortalCustomerDocumentWithoutFile
{
    const file = "file";

    /**
     * portals constructor()
     * @access public
     * @param $owner
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::file,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function getCurrentAssetList($customerID)
    {
        $queryString       = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::customerID
            ) . " = $customerID and " . $this->getDBColumnName(
                self::filename
            ) . " = 'Current Asset List Extract.xlsx' limit 1";
        $this->queryString = $queryString;
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }

    public function getCurrentOffice365Licenses($customerID)
    {
        $queryString       = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::customerID
            ) . " = $customerID and " . $this->getDBColumnName(
                self::filename
            ) . " = 'Current Mailbox Extract.xlsx' limit 1";
        $this->queryString = $queryString;
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }

    public function getDUODocumentForCustomer(int $customerId, string $DUO_USERS_AND_LOGS)
    {
        $queryString       = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::customerID
            ) . " = $customerId and " . $this->getDBColumnName(
                self::description
            ) . " = '$DUO_USERS_AND_LOGS' limit 1";
        $this->queryString = $queryString;
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }

}