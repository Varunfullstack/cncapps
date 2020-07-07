<?php /*
* Problem Raise Type
* @authors Mustafa Taha
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProblemRaiseType extends DBEntity
{
    const id = "id";
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
        $this->setTableName("problemraisetype");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL,
            "id"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "description"
        );     
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
    protected function getDB()
    {
        global $db;
        return $db;
    }
}

?>