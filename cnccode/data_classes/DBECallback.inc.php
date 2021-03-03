<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");
class CallBackStatus
{
    const AWAITING='awaiting';
    const CONTACTED='contacted';
    const CANCELED='canceled';
}
class DBECallback extends DBEntity
{
    const id                = "id";
    const consID            = "consID";     
    const problemID         ="problemID";
    const callActivityID    ='callActivityID';
    const contactID         ='contactID';    
    const description       ='description';
    const callback_datetime ='callback_datetime';
    const status       ='status';
    const createAt          ='createAt';
    
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
        $this->setTableName("contact_callback");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::consID,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::problemID,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::callActivityID,
            DA_INTEGER,
            DA_NOT_NULL
        );
         
        $this->addColumn(
            self::contactID,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::callback_datetime,
            DA_DATETIME,
            DA_NOT_NULL
        );        
        $this->addColumn(
            self::status,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createAt,
            DA_DATETIME,
            DA_NOT_NULL
        );
         
        //$this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }
 
  
}

?>