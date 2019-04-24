<?php /*
* Quotation table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuotation extends DBEntity
{

    const quotationID = "quotationID";
    const ordheadID = "ordheadID";
    const versionNo = "versionNo";
    const salutation = "salutation";
    const emailSubject = "emailSubject";
    const sentDateTime = "sentDateTime";
    const userID = "userID";
    const fileExtension = "fileExtension";
    const documentType = "documentType";

    /**
     * calls constructor()
     * @access public
     * @param $owner
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("quotation");
        $this->addColumn(
            self::quotationID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::versionNo,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::salutation,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::emailSubject,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::sentDateTime,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::fileExtension,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::documentType,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getNextVersionNo()
    {
        $ret = null;
        $this->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName(self::versionNo) . ') + 1 FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName(self::ordheadID) . '=' . $this->getFormattedValue(self::ordheadID)
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $ret = ($this->getDBColumnValue(0));
            }
        }
        $this->resetQueryString();
        if ($ret == null) {
            return 1;
        }
        return $ret;
    }

    function deleteRowsByOrderID()
    {
        $this->setMethodName('deleteRowsByOrderID');
        if ($this->getValue(self::ordheadID) == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getDBColumnName(
                self::ordheadID
            ) . ' = ' . $this->getValue(self::ordheadID)
        );
        return (parent::runQuery());
    }
}
