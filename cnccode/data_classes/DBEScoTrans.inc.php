<?php /*
* Customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEScoTrans extends DBEntity
{
    const scoTransID = "ScoTransID";
    const statement = "Statement";

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
        $this->setTableName("ScoTrans");
        $this->addColumn(
            self::scoTransID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::statement,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Run statement that has no result
     * @access public
     * @return void
     * @param  void
     */
    function executeStatement($statement)
    {
        $this->setMethodName('executeStatement');
        $this->setQueryString($statement);
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }
}

?>