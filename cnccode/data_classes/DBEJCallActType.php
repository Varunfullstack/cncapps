<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:19
 */

class DBEJCallActType extends DBECallActType
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
        $this->addColumn("itemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");        // linked item
        $this->addColumn("itemSalePrice", DA_STRING, DA_NOT_NULL, "itm_sstk_price");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRow($pkValue = false)
    {

        if ($pkValue) {
            $this->setPKValue($pkValue);
        }

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON itm_itemno = cat_itemno" .
            " WHERE " . $this->getPKWhere();
        $this->setQueryString($statement);
        $ret = (parent::getRow());
    }

    function getActiveRows()
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON itm_itemno = cat_itemno" .
            " WHERE activeFlag = 'Y'" .
            " ORDER BY cat_desc";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }
}