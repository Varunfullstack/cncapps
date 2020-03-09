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
    const fixedExplanation = "fixedExplanation";

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
        $this->addColumn(
            self::fixedExplanation,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
