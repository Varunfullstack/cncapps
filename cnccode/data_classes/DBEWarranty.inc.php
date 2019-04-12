<?php /*
* Warranty table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEWarranty extends DBCNCEntity
{
    const warrantyID = "warrantyID";
    const description = "description";
    const years = "years";
    const manufacturerID = "manufacturerID";

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
        $this->setTableName("contract");
        $this->addColumn(
            self::warrantyID,
            DA_ID,
            DA_NOT_NULL,
            "cnt_contno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "cnt_desc"
        );
        $this->addColumn(
            self::years,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "cnt_years"
        );
        $this->addColumn(
            self::manufacturerID,
            DA_ID,
            DA_ALLOW_NULL,
            "cnt_manno"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>