<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProjectTypes extends DBEntity
{
    const id = "id";
    const name = "name";     
    const includeInWeeklyReport="includeInWeeklyReport";
    const notes='notes';
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
        $this->setTableName("projecttypes");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::includeInWeeklyReport,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::notes,
            DA_TEXT,
            DA_NOT_NULL
        );
         
        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>