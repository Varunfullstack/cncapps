<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEItemBillingCategory extends DBEntity
{
    const id = "id";
    const name = "name";

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
        $this->setTableName("itemBillingCategory");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_TEXT,
            DA_NOT_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
