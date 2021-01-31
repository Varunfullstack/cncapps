<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProjectIssues extends DBEntity
{
    const id = "id";
    const consID = "consID";
    const projectID = "projectID";
    const issuesRaised = "issuesRaised";    
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
        $this->setTableName("projectissues");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::consID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::projectID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::issuesRaised,
            DA_MEMO,
            DA_ALLOW_NULL
        );       
        $this->addColumn(
            self::createAt,
            DA_DATETIME,
            DA_ALLOW_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>