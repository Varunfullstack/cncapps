<?php /*
* Manufacturer table
* @authors Karim Ahmed
* @access public
*/
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

    function getRowsByNameMatch()
    {
        $this->setMethodName("getRowsByNameMatch");
        if ($this->getValue(self::name) == '') {
            $this->raiseError('name not set');
        }

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE 1=1";
        $queryString .=
            " AND man_name LIKE '%" . $this->getValue(self::name) . "%'";


        $queryString .=
            " ORDER BY " . $this->getDBColumnName(self::name) .
            " LIMIT 0,200";

        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }
}
