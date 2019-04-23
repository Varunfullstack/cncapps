<?php /*
* Renewal type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBERenewalType extends DBEntity
{
    const renewalTypeID = "renewalTypeID";
    const description = "description";

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
        $this->setTableName("renewaltype");
        $this->addColumn(self::renewalTypeID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}
