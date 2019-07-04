<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOffice365License extends DBEntity
{
    const id = "id";
    const replacement = "replacement";
    const specificity = "specificity";
    const licensesJSON = "licensesJSON";

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
        $this->setTableName("office365License");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::replacement,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::specificity,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::licensesJSON,
            DA_JSON_ARRAY,
            DA_NOT_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
