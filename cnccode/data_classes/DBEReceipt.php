<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEReceipt extends DBEntity
{
    const id = "id";
    const fileMIMEType = "fileMIMEType";
    const expenseId = "expenseId";
    const filePath = "filePath";

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
        $this->setTableName("receipt");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::fileMIMEType,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::expenseId,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::filePath,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>