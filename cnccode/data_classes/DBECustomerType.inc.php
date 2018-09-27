<?php /*
* Customer type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerType extends DBEntity
{

    const customerTypeID = "customerTypeID";
    const description = "description";

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
        $this->setTableName("custype");
        $this->addColumn(
            self::customerTypeID,
            DA_ID,
            DA_NOT_NULL,
            "cty_ctypeno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "cty_desc"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>