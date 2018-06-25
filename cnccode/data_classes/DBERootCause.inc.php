<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBERootCause extends DBEntity
{
    const rootCauseID = "rootCauseID";
    const description = "description";
    const longDescription = "longDescription";

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
        $this->setTableName("rootcause");
        $this->addColumn(
            self::rootCauseID,
            DA_ID,
            DA_NOT_NULL,
            "rtc_rootcauseno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "rtc_desc"
        );
        $this->addColumn(
            self::longDescription,
            DA_STRING,
            DA_NOT_NULL,
            "rtc_long_desc"
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
