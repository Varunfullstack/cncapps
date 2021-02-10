<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEReports extends DBEntity
{
    const id = "id";
    const reportID = "reportID";         
    const categoryID='categoryID';
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
        $this->setTableName("report_categories");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::reportID,
            DA_INTEGER,
            DA_NOT_NULL
        );        
        $this->addColumn(
            self::categoryID,
            DA_INTEGER,
            DA_NOT_NULL
        );
         
        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>