<?php
/*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStandardText extends DBEntity
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
        $this->setTableName("standardtext");
        $this->addColumn(
            "stt_standardtextno",
            DA_ID,
            DA_NOT_NULL,
            'stt_standardtextno'
        );
        $this->addColumn(
            "stt_sort_order",
            DA_INTEGER,
            DA_NOT_NULL,
            'stt_sort_order'
        );
        $this->addColumn(
            "stt_desc",
            DA_STRING,
            DA_NOT_NULL,
            'stt_desc'
        );
        $this->addColumn(
            "stt_text",
            DA_TEXT,
            DA_NOT_NULL,
            'stt_text'
        );
        $this->addColumn(
            "stt_standardtexttypeno",
            DA_INTEGER,
            DA_NOT_NULL,
            'stt_standardtexttypeno'
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsByTypeID($standardTextTypeID)
    {
        $this->setMethodName("getRowsInGroup");

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE stt_standardtexttypeno = " . $standardTextTypeID
        );

        return (parent::getRows());
    }
}

?>