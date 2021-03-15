<?php /** @noinspection PhpMissingBreakStatementInspection */
/**
 * Database entity access class
 *
 * All database access classes to be derived from this including tables, views and stored procedures
 *
 * Future super-iImprovement:
 *
 * Make this class independent of PHPLib by creating a new base
 * container class instance with all the necessary virtual database access
 * methods.
 *
 * Then derive a specialised class (e.g. ctPHPLib) that implements container
 * specific method overrides and pass it into a new instance variable on DBEntity
 * at construction so that it is "contained by" Tbobase. It could be called
 * $ctDataAccess, for example.
 *
 * Modify Tbobase to call the methods on the container class instead of calling
 * the PHPLib methods directly. e.g. $this->ctDataAccess->runQuery(),
 * fetchRow() etc.
 *
 * NOTE: Since the change to using the DB->Record to hold row instead of
 * DataAccess->row we now create a new copy of the global database object
 * $db. Otherwise, existing code was broken when queries followed each other.
 *
 * @access virtual
 * @author Karim Ahmed
 * @cur_version    1.1
 *
 * @version V1.1
 * @date        18/10/2004
 * @author    Karim Ahmed
 * @mods
 * Change to assume no values have already been escaped (i.e. if magic_quotes_gpc = on then stripslashes()
 * has been applied to POST, GET and COOKIE arrays)
 * This means we can be safe to always apply mysql_escape_string() to values going into DB without doubling-up.
 */
global $cfg;

use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\Exceptions\DBQueryException;

require_once($cfg["path_gc"] . "/DataAccess.inc.php");
define(
    "DBE_DB_COLUMN_NAME",
    "dbcolumnname"
);

class DBEntity extends DataAccess
{
    public const ORDER_DIRECTION_ASCENDING  = "ASC";        // a new database connection purely for nextid function.
    public const ORDER_DIRECTION_DESCENDING = "DESC";            // Initialised PHPLib database object
    static $count = 0;
    /** @var dbSweetcode|MDB_PEAR_PROXY|mixed|object|PDO */
    public $pkdb;// SQL query statement
    /** @var dbSweetcode */
    public $db;    // RDBMS table name
    public $queryString = "";    // For debug purposes - TRUE causes all SQL statements to be output
    public $tableName   = "";    // For debug purposes - TRUE causes all SQL statements to be output
    public $showSQL     = true;        // For comparison during update
    public $logSQL      = false;    // For comparison during update
    public $rowBefore;
    public $arrayRowBefore;
    public $rowCount    = 0;
    public $dbColName   = [];

    // Array of database column names
    function __construct(&$owner)
    {
        parent::__construct($owner);
        global $db; //PHPLib DB object
        if (!is_object($db)) {
            $this->raiseError("Requires an initialised db object");
        } else {
            $this->db = clone $db;            // creates a copy of the global connection
            $this->db->connect();
            $this->pkdb = clone $db;            // COPIES the global connection to a NEW VARIABLE
            $this->pkdb->connect();
        }
        $this->setShowSQLOff();
        $this->setLogSQLOff();
    }

    /**
     * Set flag NOT to display SQL statements to output buffer
     * @access public
     * @return Bool Success
     */
    function setShowSQLOff()
    {
        $this->showSQL = FALSE;
        return TRUE;
    }

    /**
     * Set flag NOT to log SQL statements
     * @access public
     * @return Bool Success
     */
    function setLogSQLOff()
    {
        $this->logSQL = FALSE;
        return TRUE;
    }

    /**
     * Set flag to display SQL statements to output buffer
     * @access public
     * @return Bool Success
     */
    function setShowSQLOn()
    {
        $this->showSQL = TRUE;
        return TRUE;
    }

    /**
     * Set flag to log SQL statements to file
     * @access public
     * @return Bool Success
     */
    function setLogSQLOn()
    {
        $this->logSQL = TRUE;
        return TRUE;
    }

    /**
     * Clear variables as if this dataset object has just been created
     * @access public
     * @return boolean
     */
    function clear()
    {
        parent::clear();
        $this->setClearRowsBeforeReplicateOff(); // VERY important
        $this->setQuoteForColumnValuesSingle();
        return TRUE;
    }

    /**
     * Return number of db rows
     * @access public
     * @return  integer Number of rows
     */
    function getNumRows()
    {
        $this->setMethodName("getNumRows");
        return $this->db->num_rows();
    }

    /**
     * Fetch the first row of the result set.
     * Calls runQuery() to re-execute SQL
     * @access public
     * @return  bool Success (EOF causes False)
     */
    function fetchFirst()
    {
        $this->setMethodName("fetchFirst");
        if ($this->runQuery()) {
            return ($this->fetchNext());
        } else {
            return false;
        }
    }

    /**
     * Execute the current query statement
     * @access private
     * @return bool Success
     */
    function runQuery()
    {
        $this->setMethodName("runQuery");
        if (!is_object($this->db)) {
            $this->raiseError("\$this->db is not an object");
        }
        if ($this->getShowSQL()) {
            try {
                throw new Exception('test');
            } catch (Exception $exception) {
                echo $this->getClassname() . ": " . $this->getQueryString() . "<BR/>" . $exception->getTraceAsString(
                    ) . " <HR/>";

            }
        }
        if ($this->getLogSQL()) {
            $logFile = CONFIG_SQL_LOG;
            $handle  = fopen(
                $logFile,
                'a'
            );
            fwrite(
                $handle,
                $this->getClassname() . ": " . $this->getQueryString() . "<BR/><HR/>"
            );
        }
        // Save current row values
        if ($this->db->query($this->getQueryString())) {
            $this->rowCount        = $this->db->num_rows();
            $this->firstRowFetched = FALSE;
            $ret                   = TRUE;
        } else {
            throw new DBQueryException($this->db->Error, $this->queryString);
        }
        return $ret;
    }

    /**
     * Get value of flag to display SQL statements to output buffer
     * @access public
     * @return Bool Show SQL statements
     */
    function getShowSQL()
    {
        return $this->showSQL;
    }

    /**
     * Get the SQL statement.
     * @access public
     * @return string SQL statement
     */
    function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Set the SQL statement. Setting it this way will allow validation etc
     * @access public
     * @param $queryString
     */
    function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Get value of flag to log SQL statements to file
     * @access public
     * @return Bool Log SQL statements
     */
    function getLogSQL()
    {
        return $this->logSQL;
    }

    /**
     * Fetch next database row. Sets eof flag accordingly.
     * @access public
     * @return bool Success Failure indicates EOF
     */
    function fetchNext()
    {
        parent::fetchNext();
        if ($this->db->next_record()) {
            $this->eof = FALSE;
            return TRUE;
        } else {
            $this->eof = TRUE;
            $this->resetQueryString();
            return FALSE;
        }
        // inherited class to set result vars next
    }

    /**
     * Reset the SQL statement.
     * @access public
     * @return bool Success
     */
    function resetQueryString()
    {
        $this->queryString = "";
        return True;
    }

    function fetchArray()
    {
        return $this->db->fetchAll();
    }

    /**
     * Return the number of columns in the database object result set
     * @access private
     * @return integer Number of columns on the entity
     */
    function numDBColumns()
    {
        $this->setMethodName("numDBColumns");
        return $this->db->num_fields();
    }

    /**
     * Add a new column to the object
     * @param $name
     * @param $type
     * @param $allowNull
     * @param null $dbColumnName
     * @param null $defaultValue
     * @param null $validationFunction
     * @return integer New column number or DA_COLUMN_NOT_ADDED
     * @access public
     */
    function addColumn($name,
                       $type,
                       $allowNull,
                       $dbColumnName = null,
                       $defaultValue = null,
                       $validationFunction = null
    )
    {
        $ixColumnNo = parent::addColumn(
            $name,
            $type,
            $allowNull,
            $defaultValue,
            $validationFunction
        );
        $ret        = $ixColumnNo;
        if ($ixColumnNo != DA_OUT_OF_RANGE) {
            if ($dbColumnName) {
                $this->setDBColumnName(
                    $ixColumnNo,
                    $dbColumnName
                );
            } else {
                $this->setDBColumnName(
                    $ixColumnNo,
                    $name
                );
            }
        }
        return $ret;
    }

    /**
     * Set database column-name.
     * Use where the database column name does not match the corresponding app column-name
     * @access private
     * @param string Application column number or name
     * @param string Database column name
     * @return boolean Success
     */
    function setDBColumnName($ixColumn,
                             $dbColumnName
    )
    {
        $ixColumn = $this->columnExists($ixColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->dbColName[$ixColumn] = $dbColumnName;
            return TRUE;
        } else {
            $this->raiseError("Could not set name because column " . $ixColumn . " out of range");
            return DA_OUT_OF_RANGE;
        }
    }

    /**
     * Copy columns from current DB cursor row into current object row
     * @access private
     * @return void Success
     */
    function copyColumnsFromDB()
    {
        $colCount = $this->colCount();
        for ($ixCol = 0; $ixCol < $colCount; $ixCol++) {
            parent::setValueNoCheckByColumnNumber(
                $ixCol,
                $this->db->Record[$ixCol]
            );
        }
    }

    /**
     * Prepare for row update
     * Get the current row ready for post() call
     * @access public
     * @return bool
     */
    function setUpdateModeUpdate()
    {
        parent::setUpdateModeUpdate();
        // Get the database row before the update so that nothing is
        // overwritten(we always update all database columns).
        $ret = ($this->getRow());
        // Save the values for comparison in post()
        $this->setRowBefore();
        return $ret;
    }

    /**
     * Get one row and place in the buffer.
     * To be overriden in descendent class and called after queryString set using parent::getRow()
     * I assume that you have validated the PK yourself!
     * @param mixed Primary key value (optional)
     * @access public
     * @return bool Success
     */
    function getRow($pkValue = null)
    {
        $this->setMethodName("getRow");
        $ret = FALSE;
        if ($this->getQueryString() == "") {                    // allow use of own query string
            if ($this->getPK() != DA_PK_NOT_SET) {        // if we have a PK then validate value
                if ($pkValue) {
                    $this->setPKValue($pkValue);
                } else {
                    if ($this->getPKValue() == "" && $this->getPKValue() != 0) {
                        $this->raiseError("PK value not set");
                        return $ret;
                    }
                }
            }
            $this->setQueryString(
                "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
                ) . " WHERE " . $this->getPKWhere()
            );
        }
        if (!$this->runQuery()) {
            $this->raiseError("Problem running query");
        } else {
            if (!$this->fetchNext()) {
                $this->setRowBlank();
                $ret = FALSE;
            } else {
                $ret = TRUE;
            }
        }
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Build and return string of CSV database column names
     * @access private
     * @return string Database Column names as CSV string
     */
    function getDBColumnNamesAsString()
    {
        $this->setMethodName("getDBColumnNamesAsString");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            if ($colString != "") $colString = $colString . ",";
            $colString = $colString . $this->getDBColumnName($ixCol);
        }
        return $colString;
    }

    /**
     * Get DB Column Name
     * @access public
     * @param $ixColumnPassed
     * @return string Database column name.
     */
    function getDBColumnName($ixColumnPassed)
    {
        $ixColumn = $this->columnExists($ixColumnPassed);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            return $this->dbColName[$ixColumn];
        } else {
            $this->raiseError(
                "getDBColumnName: Could not get DB column name because column " . $ixColumnPassed . " out of range"
            );
            return DA_OUT_OF_RANGE;
        }
    }

    /**
     * Return current database entity name
     * @access public
     * @return  string Entity name
     */
    function getTableName()
    {
        $this->setMethodName("getTableName");
        return strtolower($this->tableName);
    }

    /**
     * Set current database table name
     * @access public
     * @param $tableName
     * @return void
     */
    function setTableName($tableName)
    {
        $this->setMethodName("setTableName");
        if ($tableName == "") {
            $this->raiseError("Nothing passed");
        }
        $this->tableName = $tableName;
    }

    /**
     * Get string to be used as WHERE statement for standard update statements.
     * @access public
     * @return string Where clause for update statements
     */
    function getPKWhere()
    {
        if ($this->getPK() == DA_PK_NOT_SET) {
            $this->raiseError('getPKWhere(): No Primary Key Defined');
            return null;
        }
        return ($this->getPKDBName() . "=" . $this->getFormattedValue($this->getPK()));
    }

    /**
     * Get the primary key column database name
     * @access public
     * @return string Column name or empty string
     */
    function getPKDBName()
    {
        if ($this->getPK() == DA_PK_NOT_SET) {
            return "";
        } else {
            return $this->getDBColumnName($this->getPK());
        }
    }

    /**
     * Get formatted column value(quoted, if string)
     * @access public
     * @param $ixColumn
     * @return string Formatted Column value
     */
    function getFormattedValue($ixColumn)
    {
        if (!is_numeric($ixColumn)) {
            $ixColumn = $this->colNameInverse[$ixColumn];
        }
        return $this->prepareForSQL($ixColumn);
    }

    function prepareForSQL($colIdx)
    {
        $colType = $this->colType[$colIdx];
        $value   = $this->getSQLValue($colIdx);
        if ($value === null) {
            return 'null';
        }
        $value = $this->escapeValue($value);
        switch ($colType) {
            case DA_BOOLEAN:
                return $value ? 1 : 0;
            case DA_INTEGER:
            case DA_FLOAT:
            case DA_ID:
                if ($value === '') {
                    return 'null';
                }
                return $value;
            case DA_DATETIME:
                if ($value == '0000-00-00 00:00:00') {
                    return 'null';
                }
            case DA_DATE:
                if ($value == '0000-00-00') {
                    return 'null';
                }
            case DA_PHONE:
                if ($value === '') {
                    return 'null';
                }
            default:
                return $this->quoteForColumnValues . $value . $this->quoteForColumnValues;
        }

    }

    function getSQLValue($ixPassedColumn)
    {
        $this->setMethodName('getValue');
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            if (!$this->db->Record) {
                return null;
            }
            if (!key_exists($ixColumn, $this->db->Record)) {
                return $this->getDefaultValue($ixColumn);
            }
            if ($this->db->Record[$ixColumn] === null) {
                return $this->getDefaultValue($ixColumn);
            }
            return $this->db->Record[$ixColumn];
        } else {
            throw new ColumnOutOfRangeException($ixPassedColumn);
        }
    }

    function escapeValue($value)
    {
        return mysqli_real_escape_string(
            $this->db->link_id(),
            $value
        );
    }

    function getArrayRowBefore()
    {
        return $this->arrayRowBefore;
    }

    /**
     * Commit the current row to the database. Action is dependent upon value of update_mode
     * @access public
     * @return bool Success
     */
    function post()
    {
        parent::post();
        switch ($this->getUpdateMode()) {
            case DA_MODE_INSERT:
                $result = $this->insertRow();
                break;
            case DA_MODE_DELETE:
                $result = $this->deleteRow();
                break;
            case DA_MODE_UPDATE:
                if ($this->getRowBefore() != $this->getColumnValuesAsString(
                    )) {            // Only apply update if the row has been changed.
                    $result = $this->updateRow();
                } else {
                    $this->resetQueryString();
                    $result = TRUE;
                }
                break;
            default:
                $result = false;
                $this->raiseError("Invalid update mode");
                break;
        }
        return $result;
    }

    /**
     * Insert current row to database
     * @access public
     * @return bool Success
     */
    function insertRow()
    {
        $this->setMethodName("insertRow");
        // Only set the default query if not already set in
        // descendent class.
        $this->setYNFlags();
        if ($this->getQueryString() == "") {
            if ($this->getPK() != DA_PK_NOT_SET) {
                if ($this->pkAutoIncrement) {
                    $this->setPKValue($this->getNextPKValue());
                }
            }
            $query = "INSERT INTO " . $this->getTableName() . "(" . $this->getDBColumnNamesAsString(
                ) . ")VALUES(" . $this->getColumnValuesAsString() . ")";
            if ($this->debug) {
                var_dump($query);
            }
            $this->setQueryString($query);
        }
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Ensure empty DA_YN flag fields are set to N
     * @access public
     * @return void Number of rows
     */
    function setYNFlags()
    {
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            if ($this->getType($ixCol) == DA_YN && $this->getValue($ixCol) == '') {
                $this->setValue(
                    $ixCol,
                    'N'
                );
            }
        }
    }

    /**
     * Get column value by name or column number and trim trailing spaces
     * @access public
     * @param string|int $ixPassedColumn
     * @return string|int|float|boolean Right-trimmed column value
     */
    function getValue($ixPassedColumn)
    {
        $this->setMethodName('getValue');
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            if (!$this->db->Record) {
                return null;
            }
            if (!key_exists($ixColumn, $this->db->Record)) {
                return $this->getDefaultValue($ixColumn);
            }
            if ($this->db->Record[$ixColumn] === null) {
                return $this->getDefaultValue($ixColumn);
            }
            return $this->getValueNoCheckByColumnNumber($ixColumn);
        } else {
            throw new ColumnOutOfRangeException($ixPassedColumn);
        }
    }

    function getValueNoCheckByColumnNumber($ixColumnNumber)
    {
        if ($this->debug) {
            echo '<div> getValueNoCheckByColumnNumber: ';
            var_dump($ixColumnNumber);
            echo '</div>';
        }
        if (!count($this->db->Record) || !key_exists(
                $ixColumnNumber,
                $this->db->Record
            ) || !isset($this->db->Record[$ixColumnNumber])) {
            return null;
        }
        $type = $this->getTypeByColumnNumberNoCheck($ixColumnNumber);
        if ($type == DA_ID || $type == DA_INTEGER) {
            return (int)$this->db->Record[$ixColumnNumber];
        }
        if ($type == DA_FLOAT) {
            return (float)$this->db->Record[$ixColumnNumber];
        }
        if (in_array($type, [DA_JSON_ARRAY, DA_BOOLEAN])) {
            return json_decode($this->db->Record[$ixColumnNumber]);
        }
        return $this->db->Record[$ixColumnNumber];
    }

    /**
     * Set column value by name or column number and trim trailing spaces
     * @access public
     * @param string|int $ixPassedColumn String name or col no
     * @param string $value value to set
     * @return bool
     */
    function setValue($ixPassedColumn,
                      $value
    )
    {
        DBEntity::$count++;
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn == DA_OUT_OF_RANGE) {

            $this->raiseError("Could not set column value because " . $ixPassedColumn . " out of range");
            return false;
        }
        $value = $this->prepareValue($ixColumn, $value);
        if ($this->debug) {
            echo '<div> Value after prepare: ';
            var_debug($value);
            echo '</div>';
        }
        $this->db->Record[$ixColumn] = $value;
        return TRUE;
    }

    /**
     * Get next primary key value to use for insert
     * Keeps us DB independent
     * @access private
     * @return integer Next PK to use
     */
    function getNextPKValue()
    {
        $tableName = $this->getTableName();
        $data      = $this->pkdb->nextid($tableName);
        return $data;
    }

    /**
     * Build and return string of CSV column values with single quotes
     * @access private
     * @return string
     */
    function getColumnValuesAsString()
    {
        $this->setMethodName("getColumnValuesAsString");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            if ($colString != "") {
                $colString = $colString . DA_COLUMN_SEPARATOR;
            }
            $colString .= $this->prepareForSQL($ixCol);
            if ($this->debug) {
                echo '<br>';
                var_dump(
                    $this->dbColName[$ixCol],
                    $this->colType[$ixCol],
                    $this->prepareForSQL($ixCol),
                    $colString
                );
                echo '<br>';
            }

        }
        return $colString;
    }

    /**
     * delete one row . May be overriden and called after setQueryString() using parent::getRow()
     * @access public
     * @param mixed Primary key value (optional)
     * @return bool Success
     */
    function deleteRow($pkValue = '')
    {
        $this->setMethodName("deleteRow");
        $ret = FALSE;
        // I assume that you have validated the PK yourself!
        if ($this->getQueryString() == "") {
            if ($this->getPK() != DA_PK_NOT_SET) {
                if ($pkValue != '') {
                    $this->setPKValue($pkValue);
                } else {
                    if ($this->getPKValue() == "" & $this->getPKValue() != 0) {
                        $this->raiseError("PK value not set");
                        return $ret;
                    }
                }
            }
            $query = "DELETE FROM " . $this->getTableName() . " WHERE " . $this->getPKWhere();
            if ($this->debug) {
                var_dump($query);
            }
            $this->setQueryString($query);
        }
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Return the row string before the update operation
     * @access public
     * @return String Row value before post
     */
    function getRowBefore()
    {
        return $this->rowBefore;
    }

    /**
     * Record the row before the update operation
     * @access public
     * @return void Success
     */
    function setRowBefore()
    {
        $this->rowBefore      = $this->getColumnValuesAsString();
        $this->arrayRowBefore = array();
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            $this->arrayRowBefore[] = $this->getValueByColumnNumber($ixCol);
        }
    }

    /**
     * Update all columns on the current row
     * WARNING:
     * The default query will update all columns with the current
     * row columns.
     * @access public
     * @return bool
     */
    function updateRow()
    {
        $this->setMethodName("updateRow");
        // Only set the default query if not already set in
        // descendent class.
        $this->setYNFlags();
        if ($this->getQueryString() == "") {
            if ($this->getPK() != DA_PK_NOT_SET) {
                if ($this->getPKValue() == "" & $this->getPKValue() != 0) {
                    $this->raiseError("PK value not set");
                }
            }
            $this->setQueryString(
                "UPDATE " . $this->getTableName() . " SET " . $this->getUpdateString() . " WHERE " . $this->getPKWhere()
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
        $this->setMethodName("getUpdateString");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            // exclude primary key column if it exists
            if (($this->getPK() == DA_PK_NOT_SET) || ($this->getPKName() != $this->getName($ixCol))) {
                if ($colString != "") $colString = $colString . ",";
                $colString = $colString . $this->getDBColumnName($ixCol) . "=" . $this->prepareForSQL($ixCol);
            }
        }
        return $colString;
    }

    /**
     * Build and return string of CSV database column names with table prefixes
     * @access private
     * @return string Database Column names as CSV string
     */
    function getFullDBColumnNamesAsString()
    {
        $this->setMethodName("getFullDBColumnNamesAsString");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            if ($colString != "") $colString = $colString . ",";
            $colString = $colString . $this->getTableName() . '.' . $this->getDBColumnName($ixCol);
        }
        return $colString;
    }

    function getRowAsArray()
    {
        $this->setMethodName("getRowAsArray");
        $arrayRow = array();
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            $arrayRow[] = $this->getValue($ixCol);
        }
        return $arrayRow;
    }

    function getRowAsAssocArray()
    {
        $this->setMethodName("getRowAsArrayAssoc");
        $arrayRow = array();
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            $arrayRow[$this->colName[$ixCol]] = $this->getValue($ixCol);
        }
        return $arrayRow;
    }

    /**
     * Delete all rows in the dataset
     * @access private
     * @return boolean Success
     */
    function clearRows()
    {
        $this->setMethodName("clearRows");
        return parent::clearRows();
    }

    /**
     * Get formatted column value for SQL LIKE: no quotes around it
     * @access public
     * @param $ixColumn
     * @return string Formatted Column value
     */
    function getFormattedLikeValue($ixColumn)
    {
        return $this->quoteForColumnValues . '%' . $this->escapeValue(
                $this->getValue($ixColumn)
            ) . '%' . $this->quoteForColumnValues;
    }

    function getValueByColumnNumber($ixColumnNumber)
    {
        return ($this->getValueNoCheckByColumnNumber($ixColumnNumber));
    }

    /**
     * Set column value by index without any error checking OVERRIDEN from DataAccess
     * Adds slashes to escape any quote characters
     * @access public
     * @param string $ixColumn Column number
     * @param string $value Value
     * @return boolean Success
     */
    function setValueNoCheckByColumnNumber($ixColumn,
                                           $value
    )
    {
        $this->db->Record[$ixColumn] = $value;
        return TRUE;
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
            ) . " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn);
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

    /**
     * Return all rows from DB
     * @access public
     * @param string $sortColumn
     * @param string $orderDirection
     * @return bool Success
     */
    function getRows($sortColumn = '', $orderDirection = null)
    {
        $this->setMethodName("getRows");
        if (!$this->getQueryString()) {
            $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName();
            if ($sortColumn != '') {
                $sortColumnNo = ($this->columnExists($sortColumn));
                if ($sortColumnNo == DA_OUT_OF_RANGE) {
                    $this->raiseError($sortColumn . ' ' . DA_MSG_COLUMN_DOES_NOT_EXIST);
                } else {
                    $queryString .= ' ORDER BY ' . $this->getDBColumnName($sortColumnNo);
                }
                if ($orderDirection) {
                    $queryString .= " $orderDirection ";
                }

            }
            $this->setQueryString($queryString);
        }
        return ($this->runQuery());
    }

    function getRowByColumn($column)
    {
        $this->setMethodName("getRowColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            throw new ColumnOutOfRangeException($column);
        }
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn);
        $this->setQueryString($queryString);
        return ($this->getRow());
    }

    /**
     * count rows on table by column value
     * @access public
     * @param $column
     * @return bool Success
     */
    function countRowsByColumn($column)
    {
        $this->setMethodName("countRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            throw new ColumnOutOfRangeException($column);
        }
        $this->setQueryString(
            "SELECT COUNT(*)" . " FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                $ixColumn
            ) . "=" . $this->getFormattedValue($ixColumn)
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return false;
    }

    /**
     * Advance one record in the database set. Sets eof flag accordingly.
     * @access public
     * @return bool Success Failure indicates EOF
     */
    function nextRecord()
    {
        return ($this->db->next_record());
    }

    /**
     * Get DB column value
     * @access private
     * @param $ixCol
     * @return boolean Success
     */
    function getDBColumnValue($ixCol)
    {
        return $this->db->Record[$ixCol];
    }

    /**
     * Get number of rows in dataset
     * @access public
     * @return integer Number of rows
     */
    function rowCount()
    {
        return $this->rowCount;
    }
}
