<?php /**
 * Database entity access class for CNC
 *
 * @access virtual
 * @author Karim Ahmed
 */
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

//require_once($cfg["path_dbe"]."/DBEScoTrans.inc.php");
class DBCNCEntity extends DBEntity
{
    const nextID = "nextID";                // need this one for getting next PK. it is created in descendent
    protected $dbeNextPK = '';

    function __construct(&$owner)
    {
        parent::__construct($owner);
        //$this->dbeScoTrans = new DBEScoTrans($this);
        $this->dbeNextPK = new DBEntity($this);// for getting next key
        $this->dbeNextPK->addColumn(
            self::nextID,
            DA_ID,
            DA_ALLOW_NULL
        );
    }
}

?>