<?php /*
* prizewinner table join to contact
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEPrizewinner.inc.php");

class DBEJPrizewinner extends DBEPrizewinner
{
    /**
     * calls constructor()
     * @access public
     *
     * @param $owner
     * @param bool $pkID
     * @internal param $void
     * @see constructor()
     */
    function __construct(&$owner, $pkID = false)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
        $this->addColumn("contactFirstName", DA_STRING, DA_ALLOW_NULL, "con_first_name");
        $this->addColumn("contactLastName", DA_STRING, DA_ALLOW_NULL, "con_last_name");
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    function getRow($pkID)
    {
        $this->setPKValue($pkID);

        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            "  LEFT JOIN contact ON prz_contno = con_contno
         LEFT JOIN customer ON con_custno = cus_custno
        WHERE " . $this->getPKWhere();

        $this->setQueryString($sql);

        return (parent::getRow());
    }

    function getRows()
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            "  LEFT JOIN contact ON prz_contno = con_contno
         LEFT JOIN customer ON con_custno = cus_custno
        ORDER BY prz_yearmonth ";

        $this->setQueryString($sql);

        return (parent::getRows());
    }
}

?>
