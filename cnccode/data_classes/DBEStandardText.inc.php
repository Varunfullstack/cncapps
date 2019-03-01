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

    const stt_standardtextno = "stt_standardtextno";
    const stt_desc = "stt_desc";
    const stt_text = "stt_text";
    const stt_standardtexttypeno = "stt_standardtexttypeno";
    const salesRequestEmail = 'salesRequestEmail';
    const salesRequestUnassignFlag = 'salesRequestUnassignFlag';

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

        $this->addColumn(
            self::salesRequestEmail,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::salesRequestUnassignFlag,
            DA_YN_FLAG,
            DA_ALLOW_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    private function defaultOrdering()
    {
        return " order by " . $this->getDBColumnName(self::stt_standardtexttypeno) . " asc , " . $this->getDBColumnName(
                self::stt_desc
            );
    }

    function getRows($sortColumn = '')
    {
        $this->setMethodName("getRows");
        if ($this->getQueryString() == "") {
            $queryString =
                "SELECT " . $this->getDBColumnNamesAsString() .
                " FROM " . $this->getTableName();
            if ($sortColumn != '') {
                $sortColumnNo = ($this->columnExists($sortColumn));
                if ($sortColumnNo == DA_OUT_OF_RANGE) {
                    $this->raiseError($sortColumn . ' ' . DA_MSG_COLUMN_DOES_NOT_EXIST);
                } else {
                    $queryString .= ' ORDER BY ' . $this->getDBColumnName($sortColumnNo);
                }
            } else {
                $queryString .= $this->defaultOrdering();
            }
            $this->setQueryString($queryString);
        }
        return ($this->runQuery());
    }

    function getRowsByTypeID($standardTextTypeID)
    {
        $this->setMethodName("getRowsInGroup");

        $query = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE stt_standardtexttypeno = " . $standardTextTypeID;

        $query .= $this->defaultOrdering();
        $this->setQueryString($query);

        return (parent::getRows());
    }

}

?>