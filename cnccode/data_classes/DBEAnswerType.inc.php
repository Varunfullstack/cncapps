<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEAnswerType extends DBEntity
{
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
        $this->setTableName("answertype");
        $this->addColumn("answerTypeID", DA_ID, DA_NOT_NULL, "ant_answertypeno");
        $this->addColumn("description", DA_STRING, DA_NOT_NULL, "ant_desc");
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
