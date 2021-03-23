<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEReports extends DBEntity
{
    const id = "id";
    const title = "title";         
    const active='active';
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
        $this->setTableName("reports_categories");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::title,
            DA_STRING,
            DA_NOT_NULL
        );        
        $this->addColumn(
            self::active,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
         
        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>