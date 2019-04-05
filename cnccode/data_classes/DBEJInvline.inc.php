<?php /*
* Invline table
* @authors Karim Ahmed
* @access public
*/
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
     * @return bool Success
     */
    function getRowsByColumn($column)
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            $this->raiseError("Column " . $column . " out of range");
            return DA_OUT_OF_RANGE;
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON " . $this->getDBColumnName('itemID') . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn) .
            " ORDER BY " . $this->getDBColumnName('sequenceNo')
        );
        return ($this->getRows());
    }

    function getRowByInvheadIDSequenceNo()
    {
        if ($this->getValue('invheadID') == '') {
            $this->raiseError('invheadID not set');
        }
        if ($this->getValue('sequenceNo') == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON " . $this->getDBColumnName('itemID') . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName('invheadID') . "=" . $this->getFormattedValue('invheadID') .
            " AND " . $this->getDBColumnName('sequenceNo') . "=" . $this->getFormattedValue('sequenceNo')
        );
        return (parent::getRow());
    }
}

?>