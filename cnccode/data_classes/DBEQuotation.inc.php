<?php /*
* Quotation table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuotation extends DBEntity
{

    const quotationID          = "quotationID";
    const ordheadID            = "ordheadID";
    const versionNo            = "versionNo";
    const salutation           = "salutation";
    const emailSubject         = "emailSubject";
    const sentDateTime         = "sentDateTime";
    const userID               = "userID";
    const fileExtension        = "fileExtension";
    const documentType         = "documentType";
    const deliverySiteAdd1     = "deliverySiteAdd1";
    const deliverySiteAdd2     = "deliverySiteAdd2";
    const deliverySiteAdd3     = "deliverySiteAdd3";
    const deliverySiteTown     = "deliverySiteTown";
    const deliverySiteCounty   = "deliverySiteCounty";
    const deliverySitePostCode = "deliverySitePostCode";
    const deliveryContactID    = "deliveryContactID";
    const confirmCode          = "confirmationCode";
    const signableEnvelopeID   = "signableEnvelopeID";
    const isDownloaded         = "isDownloaded";


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
            DA_ALLOW_NULL
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
        $this->addColumn(
            self::deliverySiteAdd1,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliverySiteAdd2,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliverySiteAdd3,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliverySiteTown,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliverySiteCounty,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliverySitePostCode,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::deliveryContactID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::confirmCode,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::signableEnvelopeID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::isDownloaded,
            DA_BOOLEAN,
            DA_NOT_NULL,
            "is_downloaded",
            0
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    function getNextVersionNo()
    {
        $ret = null;
        $this->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName(self::versionNo) . ') + 1 FROM ' . $this->getTableName(
            ) . ' WHERE ' . $this->getDBColumnName(self::ordheadID) . '=' . $this->getFormattedValue(self::ordheadID)
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

    public function getQuotesWithSignableDocumentForSalesOrder(int $salesOrderID)
    {
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::ordheadID
            ) . " = " . $salesOrderID . " and " . $this->getDBColumnName(self::signableEnvelopeID) . " is not null"
        );
        parent::getRows();
    }
}
