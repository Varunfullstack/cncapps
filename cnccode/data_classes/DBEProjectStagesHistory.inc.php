<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProjectIssues extends DBEntity
{
    const id = "id";
    const projectID = "projectID";    
    const stageID = "stageID";
    const consID = "consID";
    const stageTimeHours = "stageTimeHours";     
    const createAt = "createAt";     

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
        $this->setTableName("ProjectStagesHistory");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::projectID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::stageID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::consID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::stageTimeHours,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createAt,
            DA_STRING,
            DA_NOT_NULL
        );
         
        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>