<?php
/*
* Standard Text Type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStandardTextType extends DBEntity
{
    const standardTextTypeID = "standardTextTypeID";
    const variables = "variables";
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
        $this->setTableName("standardtexttype");
        $this->addColumn(
            self::standardTextTypeID,
            DA_ID,
            DA_NOT_NULL,
            'sty_standardtexttypeno'
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            'sty_desc'
        );
        $this->addColumn(
            self::variables,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>