<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 10:53
 */

class DBEJHeader extends DBEHeader
{

    const gscItemDescription = "gscItemDescription";

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
        $this->addColumn(self::gscItemDescription, DA_STRING, DA_NOT_NULL, "itm_desc");
        $this->setAddColumnsOff();
    }

    function getRow($pkValue = null)
    {
        $this->setMethodName("getRow");
        $ret = FALSE;
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " JOIN item ON hed_gensup_itemno = itm_itemno"
        );
        return (parent::getRow());
    }
}