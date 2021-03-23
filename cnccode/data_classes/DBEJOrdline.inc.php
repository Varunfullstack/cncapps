<?php /*
* Ordline to supplier join
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\Exceptions\ColumnOutOfRangeException;

require_once($cfg["path_dbe"] . "/DBEOrdline.inc.php");

class DBEJOrdline extends DBEOrdline
{

    const supplierName = "supplierName";
    const webSiteURL = "webSiteURL";
    const itemDescription = "itemDescription";
    const renewalTypeID = "renewalTypeID";
    const partNo = "partNo";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::supplierName,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_name"
        );
        $this->addColumn(
            self::webSiteURL,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_web_site_url"
        );
        $this->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_desc"
        );
        $this->addColumn(
            self::renewalTypeID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::partNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_unit_of_sale"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return rows from DB by column value
     * @access public
     * @param $column
     * @param string $sortColumn
     * @return bool Success
     */
    function getRowsByColumn($column, $sortColumn = '')
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            throw new ColumnOutOfRangeException($column);
        }
        $query = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN supplier ON " . $this->getDBColumnName(
                self::supplierID
            ) . "=sup_suppno" . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue(
                $ixColumn
            );
        if ($sortColumn) {
            $query .= " order by {$this->getDBColumnName($sortColumn)}";
        } else {
            $query .= " order by {$this->getDBColumnName(self::isRecurring)},  {$this->getDBColumnName(self::sequenceNo)}";
        }
        $this->setQueryString($query);
        return ($this->getRows());
    }

    function getRow($pkValue = null)
    {
        $this->setPKValue($pkValue);
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN supplier ON " . $this->getDBColumnName(
                self::supplierID
            ) . "=sup_suppno" . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno where " . $this->getPKWhere()
        );
        return parent::getRow($pkValue);
    }

    function getRowByOrdheadIDSequenceNo()
    {
        if (!$this->getValue(self::ordheadID)) {
            $this->raiseError('ordheadID not set');
        }
        $query = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN supplier ON " . $this->getDBColumnName(
                self::supplierID
            ) . "=sup_suppno" . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName(self::ordheadID) . "=" . $this->getFormattedValue(
                self::ordheadID
            ) . " AND " . $this->getDBColumnName(self::sequenceNo) . "=" . $this->getFormattedValue(self::sequenceNo);
        $this->setQueryString($query);
        return (parent::getRow());
    }
}
