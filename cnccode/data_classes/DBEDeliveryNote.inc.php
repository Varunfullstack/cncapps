<?php /*
* Delivery Note table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEDeliveryNote extends DBEntity
{

    const deliveryNoteID = "deliveryNoteID";
    const ordheadID = "ordheadID";
    const noteNo = "noteNo";
    const dateTime = "dateTime";

    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("deliverynote");
        $this->addColumn(
            self::deliveryNoteID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::noteNo,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::dateTime,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getNextNoteNo()
    {
        $this->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName(self::noteNo) . ') + 1 FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName(self::ordheadID) . '=' . $this->getFormattedValue(self::ordheadID)
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $ret = ($this->getDBColumnValue(0));
            }
        }
        $this->resetQueryString();
        if ($ret == null) {
            $ret = 1;
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
            ) . ' = ' . $this->getFormattedValue(self::ordheadID)
        );
        return (parent::runQuery());
    }
}

?>