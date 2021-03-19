<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:22
 */
global $cfg;
require_once($cfg["path_dbe"] . '/DBEPortalCustomerDocumentWithoutFile.inc.php');

class DBEPortalCustomerDocument extends DBEPortalCustomerDocumentWithoutFile
{
    const filename = "filename";
    const file = "file";
    const fileMimeType = "fileMimeType";

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
            self::filename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::file,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileMimeType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function getCurrentAssetList($customerID)
    {
        $queryString = "select " . $this->getDBColumnNamesAsString(
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
        $queryString = "select " . $this->getDBColumnNamesAsString(
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

}