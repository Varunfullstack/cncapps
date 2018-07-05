<?php /*
* Activity Categories
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESector extends DBEntity
{
    const sectorID = "sectorID";
    const description = "description";

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
        $this->setTableName("sector");
        $this->addColumn(self::sectorID, DA_ID, DA_NOT_NULL, 'sec_sectorno');
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL, 'sec_desc');
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>