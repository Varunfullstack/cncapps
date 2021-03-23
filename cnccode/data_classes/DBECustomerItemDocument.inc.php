<?php /*
* customerItem document table
* @authors Karim Ahmed
* @access public
*/

use CNCLTD\Exceptions\ColumnOutOfRangeException;

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerItemDocument extends DBEntity
{
    const customerItemDocumentID = "customerItemDocumentID";
    const customerItemID         = "customerItemID";
    const description            = "description";
    const filename               = "filename";
    const file                   = "file";
    const fileLength             = "fileLength";
    const fileMIMEType           = "fileMIMEType";
    const createDate             = "createDate";
    const createUserID           = "createUserID";

    /**
     * customerItems constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("customeritemdocument");
        $this->addColumn(
            self::customerItemDocumentID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerItemID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::filename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::file,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileLength,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileMIMEType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createDate,
            DA_DATE,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createUserID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

class DBEJCustomerItemDocument extends DBEcustomerItemDocument
{
    const createUserName = "createUserName";

    /**
     * customerItems constructor()
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
            self::createUserName,
            DA_ID,
            DA_NOT_NULL,
            "CONCAT(firstName, ' ', lastName)"
        );
        $this->setAddColumnsOff();
    }

    function getRowsByColumn($column,
                             $sortColumn = ''
    )
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            throw new ColumnOutOfRangeException($column);
        }
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN consultant ON cns_consno = createUserID" . " WHERE " . $this->getDBColumnName(
                $ixColumn
            ) . "=" . $this->getFormattedValue($ixColumn);
        if ($sortColumn != '') {
            $ixSortColumn = $this->columnExists($sortColumn);
            if ($ixSortColumn == DA_OUT_OF_RANGE) {
                throw new ColumnOutOfRangeException($column);
            } else {
                $queryString .= " ORDER BY " . $this->getDBColumnName($ixSortColumn);
            }
        }
        $this->setQueryString($queryString);
        return ($this->getRows());
    }
}

?>