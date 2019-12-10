<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerContactRefreshToken extends DBEntity
{
    const id = "id";
    const contactID = "contactID";
    const token = "token";
    const createdAt = "createdAt";

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
        $this->setTableName("answer");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::contactID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::token,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createdAt,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
