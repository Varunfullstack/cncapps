<?php /*
* customerItem document table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerItemDocument extends DBEntity
{
    /**
     * customerItems constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("customeritemdocument");
        $this->addColumn("customerItemDocumentID", DA_ID, DA_NOT_NULL);
        $this->addColumn("customerItemID", DA_ID, DA_NOT_NULL);
        $this->addColumn("description", DA_STRING, DA_NOT_NULL);
        $this->addColumn("filename", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("file", DA_BLOB, DA_ALLOW_NULL);
        $this->addColumn("fileLength", DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn("fileMIMEType", DA_STRING, DA_NOT_NULL);
        $this->addColumn("createDate", DA_DATE, DA_NOT_NULL);
        $this->addColumn("createUserID", DA_ID, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

class DBEJCustomerItemDocument extends DBEcustomerItemDocument
{
    /**
     * customerItems constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("createUserName", DA_ID, DA_NOT_NULL, "CONCAT(firstName, ' ', lastName)");
        $this->setAddColumnsOff();
    }

    function getRowsByColumn($column, $sortColumn = '')
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            $this->raiseError("Column " . $column . " out of range");
            return DA_OUT_OF_RANGE;
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . " LEFT JOIN consultant ON cns_consno = createUserID" .
            " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn);

        if ($sortColumn != '') {
            $ixSortColumn = $this->columnExists($sortColumn);
            if ($ixSortColumn == DA_OUT_OF_RANGE) {
                $this->raiseError("Sort Column " . $column . " out of range");
                return DA_OUT_OF_RANGE;
            } else {
                $queryString .= " ORDER BY " . $this->getDBColumnName($ixSortColumn);
            }
        }
        $this->setQueryString($queryString);
        return ($this->getRows());
    }
}

?>