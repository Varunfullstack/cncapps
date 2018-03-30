<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 10:53
 */

class DBEJHeader extends DBEHeader
{
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
        $this->setAddColumnsOn();
        $this->addColumn("gscItemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");
        $this->setAddColumnsOff();
    }

    function getRow()
    {
        $this->setMethodName("getRow");
        $ret = FALSE;
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON hed_gensup_itemno = itm_itemno"
        );
        return (parent::getRow());
    }
}