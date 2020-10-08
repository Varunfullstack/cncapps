<?php /*
* Item table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEProblemNotStartReason extends DBCNCEntity
{

    const ID = "id";
    const problemID = "problemID";
    const userID = "userID";
    const reason = "reason";
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
        $this->setTableName("problemNotStartReason");
        $this->addColumn(
            self::ID,
            DA_ID,
            DA_NOT_NULL,
            "id"
        );
        $this->addColumn(
            self::reason,
            DA_STRING,
            DA_NOT_NULL,
            "reason"
        );

        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL,
            "problemID"
        );

        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "userID"
        );
        $this->addColumn(
            self::createAt,
            DA_TIME,
            DA_ALLOW_NULL,
            "createAt"
        );         
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

}
