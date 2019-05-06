<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 15:25
 */

require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEStaffAppraisalObjectives extends DBCNCEntity
{

    const id = "id";
    const questionnaireAnswerID = "questionnaireAnswerID";
    const requirement = "requirement";
    const measure = "measure";
    const comment = "comment";

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
        $this->setTableName("staffAppraisalObjectives");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::questionnaireAnswerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::requirement,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::measure,
            DA_ID,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::comment,
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(self::id);

        var_debug($this->getPK());
    }

    /**
     * It is a workaround for the lack of a single PK column.
     * @access public
     * @return bool Success
     * function getPKValue(){
     * return $this->getValue(self::SiteNo);
     * }
     * Get string to be used as WHERE statement for update/get/delete statements.
     * @access public
     * @return string Where clause for update statements
     */
    function getPKWhere()
    {
        return (
            $this->getDBColumnName(self::id) . '=' . $this->getFormattedValue(self::id) .
            ' AND ' . $this->getDBColumnName(self::questionnaireAnswerID) . '=' . $this->getFormattedValue(
                self::questionnaireAnswerID
            )
        );
    }

    /**
     * Allocates the next site number for this customer
     * @access private
     * @param void
     * @return integer Next Siteno
     */
    function getNextPKValue()
    {
        $this->dbeNextPK->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName(self::id) . ') + 1 FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName(self::questionnaireAnswerID) . '=' . $this->getFormattedValue(
                self::questionnaireAnswerID
            )
        );
        $id = 1;
        if ($this->dbeNextPK->runQuery()) {
            if ($this->dbeNextPK->nextRecord()) {
                $id = $this->dbeNextPK->getDBColumnValue(0);
            }
        }

        $this->dbeNextPK->resetQueryString();
        return $id;
    }

    function insertRow()
    {
        $this->setMethodName("insertRow");
        // Only set the default query if not already set in
        // descendent class.

        $this->setYNFlags();
        if ($this->getQueryString() == "") {
            $this->setQueryString(
                "INSERT INTO " . $this->getTableName() .
                "(" .
                $this->getDBColumnNamesAsString() .
                ")VALUES(" .
                $this->getColumnValuesAsString() .
                ")"
            );
        }

        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Build and return string that can be used by update() function
     * @access private
     * @return string Update SQL statement
     */
    function getUpdateString()
    {
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            // exclude primary key columns
            if (($this->getName($ixCol) != self::questionnaireAnswerID) & ($this->getName($ixCol) != self::id)) {
                if ($colString != "") $colString = $colString . ",";
                $colString = $colString . $this->getDBColumnName($ixCol) . "=" .
                    $this->prepareForSQL($this->getValue($ixCol));
            }
        }
        return $colString;
    }

    public function getRowsByAnswerID(string $questionnaireAnswerID)
    {
        $query = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::questionnaireAnswerID
            ) . " = $questionnaireAnswerID";
        $this->setQueryString($query);
        $this->getRows(self::id);
    }
}