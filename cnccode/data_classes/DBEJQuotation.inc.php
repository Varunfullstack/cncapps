<?php /*
* Quotation table join to user table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");

class DBEJQuotation extends DBEQuotation
{
    const userName = "userName";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::userName,
            DA_STRING,
            DA_NOT_NULL,
            'cns_name'
        );
        $this->setAddColumnsOff();
    }

    /**
     * Return rows by ordheadID
     * @access public
     * @return bool Success
     */
    function getRowsByOrdheadID()
    {
        $this->setMethodName("getRowsByOrdheadID");
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            'SELECT ' . $this->getDBColumnNamesAsString() .
            ' FROM ' . $this->getTableName() . ' LEFT JOIN consultant ON ' . $this->getTableName(
            ) . '.' . $this->getDBColumnName('userID') . '=consultant.cns_consno' .
            ' WHERE ' . $this->getTableName() . '.' . $this->getDBColumnName(
                'ordheadID'
            ) . '=' . $this->getFormattedValue('ordheadID')
        );
        return (parent::getRows());
    }
}

?>