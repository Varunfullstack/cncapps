<?php /*
* Manufacturer table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEManufacturer extends DBCNCEntity
{
    const manufacturerID = "manufacturerID";
    const name = "name";

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
        $this->setTableName("manufact");
        $this->addColumn(self::manufacturerID, DA_ID, DA_NOT_NULL, "man_manno");
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL, "man_name");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsByNameMatch($name, $limit = true)
    {
        $this->setMethodName("getRowsByNameMatch");
        if (!$name) {
            $this->getRows(self::name);
        }
        global $db;
        $escapedName = mysqli_real_escape_string($db->link_id(), $name);
        $queryString =
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE  man_name LIKE '%{$escapedName}%' ORDER BY {$this->getDBColumnName(self::name)}";
        if ($limit) {
            $queryString .= " LIMIT 0,200";
        }

        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }
    function hasName($name,$id=null)
    {
        return DBConnect::fetchOne("select * from ".$this->getTableName()." where man_name=:name and (:id=null or man_manno<>:id)",["name"=>$name,"id"=>$id]);
    }
}
