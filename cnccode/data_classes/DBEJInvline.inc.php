<?php /*
* Invline table
* @authors Karim Ahmed
* @access public
*/

use CNCLTD\Exceptions\ColumnOutOfRangeException;
global $cfg;
require_once($cfg["path_dbe"] . "/DBEInvline.inc.php");

class DBEJInvline extends DBEInvline
{

    const itemDescription = "itemDescription";

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
            self::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_desc"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Return rows from DB by column value
     * @access public
     * @param $column
     * @param null $columnSort
     * @return bool Success
     */
    function getRowsByColumn($column, $columnSort = null)
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
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue(
                $ixColumn
            ) . " ORDER BY " . $this->getDBColumnName(self::sequenceNo)
        );
        return ($this->getRows());
    }

    function getRowByInvheadIDSequenceNo()
    {
        if ($this->getValue(self::invheadID) == '') {
            $this->raiseError('invheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName(self::invheadID) . "=" . $this->getFormattedValue(
                self::invheadID
            ) . " AND " . $this->getDBColumnName(self::sequenceNo) . "=" . $this->getFormattedValue(self::sequenceNo)
        );
        return (parent::getRow());
    }
}
