<?php
/**
 * Base data access class.
 * Must be extended to be useful
 * @author Karim Ahmed
 * @access virtual
 */
require_once($cfg["path_gc"] . "/BaseObject.inc.php");

define(
    "DA_NOT_NULL",
    0
);
define(
    "DA_ALLOW_NULL",
    1
);
// Column type constants
define(
    "DA_STRING",
    "string"
);
define(
    "DA_TEXT",
    "string"
);
define(
    "DA_INTEGER",
    "int"
);
define(
    "DA_MEMO",
    "memo"
);

define(
    "DA_FLOAT",
    "float"
);
define(
    "DA_BLOB",
    "blob"
);
define(
    "DA_ID",
    "id"
);
define(
    "DA_YN",
    "yn"
);
define(
    "DA_YN_FLAG",
    'yn'
);
define(
    "DA_DATETIME",
    "datetime"
);
define(
    "DA_DATE",
    "date"
);
define(
    "DA_TIME",
    "time"
);
define(
    "DA_BOOLEAN",
    "bool"
);
define(
    "DA_ARRAY",
    "array"
);

define(
    'DA_SUPPORT_LEVEL',
    'supportLevel'
);

// a PHP array of values
// Update modes
define(
    "DA_MODE_NONE",
    0
);
define(
    "DA_MODE_INSERT",
    1
);
define(
    "DA_MODE_UPDATE",
    2
);
define(
    "DA_MODE_DELETE",
    3
);

define(
    "DA_PK_NOT_SET",
    -1
);        // Primary key not set
define(
    "DA_OUT_OF_RANGE",
    -1
);        // Column out of range
define(
    "DA_COLUMN_NOT_ADDED",
    -1
);

define(
    "DA_QUOTE_SINGLE",
    "'"
);
define(
    "DA_QUOTE_DOUBLE",
    "\""
);
// Classnames - are returned as lower-case by PHP
define(
    "DA_CLASSNAME_DBENTITY",
    "dbentity"
);
define(
    "DA_CLASSNAME_DATASET",
    "dataset"
);
define(
    "DA_CLASSNAME_DATAACCESS",
    "dataaccess"
);

define(
    "DA_AFTER_COLUMNS_CREATED",
    0
);    // Available callback methods
define(
    "DA_BEFORE_POST",
    1
);
define(
    "DA_AFTER_POST",
    2
);
// Other
define(
    "DA_NAME",
    "name"
);
define(
    "DA_TYPE",
    "type"
);
define(
    "DA_NULL",
    "null"
);
define(
    "DA_NOT_SET",
    -1
);
define(
    "DA_DATE_NOT_SET",
    "null"
);
define(
    "DA_COLUMN_SEPARATOR",
    ","
);
define(
    "DA_SERIAL_LIMIT",
    1000
);                // Attempt to tune the columnExists sort method
// messages
define(
    "DA_MSG_COLUMN_DOES_NOT_EXIST",
    'Column does not exist'
);                // Attempt to tune the columnExists sort method
class DataAccess extends BaseObject
{

// Instance variables

    var $eof = FALSE;                                    // End of file flag(TRUE/FALSE)
//	var	$col=array();								// Array of column information
    var $colName = array();                                // Array of column names
    var $colNameInverse = array();                // Array of column names
    var $colType = array();                                // Array of column types
    var $colNull = array();                                // Array of column null flags
//	var $colNameXRef=array();
    var $row = array();                                // Current row values array
    var $updateMode = DA_MODE_NONE;    // insert, delete or update
    var $pk = DA_PK_NOT_SET;                    // Primary key column
    var $allowAddColumns = TRUE;        // Add columns allowed?
// These are used to control the behaviour of the replicate method:
    var $clearRowsBeforeReplicate = FALSE;
    var $firstRowFetched = FALSE;
    var $beforePostObject = "";
    var $beforePostMethod = "";            // Happens just before each data row is posted to destination
    var $afterPostObject = "";
    var $afterPostMethod = "";            // Happens just after each data row is posted to destination
    var $afterColumnsCreatedObject = "";
    var $afterColumnsCreatedMethod = "";// Happens just after the columns have been created on dest
    var $postRow = "";                                // Flag to indicate whether row is posted to destination
    var $quoteForColumnValues = DA_QUOTE_DOUBLE;
    var $allowUpdate = FALSE;            // Flag to indicate whether row is posted to destination
    var $ignoreNULL = FALSE;            // Flag to indicate allow NULL rule is enforced
    var $newRowValue = 0;                    // This value in a primary key column indicates to INSERT a new row into a Data Access object
    var $failOutOfRange = TRUE;
    var $_colCount = 0;
    protected $colValidation = [];
    protected $debug;

    function enableDebugging()
    {
        $this->debug = true;
    }

    function disableDebugging()
    {
        $this->debug = false;
    }

    function __construct(&$owner)
    {
        BaseObject::__construct($owner);
        $this->clear();
    }

    function setIgnoreNULLOn()
    {
        $this->ignoreNULL = TRUE;
    }

    function setIgnoreNULLOff()
    {
        $this->ignoreNULL = FALSE;
    }

    function getIgnoreNULL()
    {
        return $this->ignoreNULL;
    }

    function setNewRowValue($value)
    {
        $this->newRowValue = $value;
    }

    function getNewRowValue()
    {
        return $this->newRowValue;
    }

    /**
     * Copy columns and rows from another DataAccess subclassed object to this one.<BR/>
     *<BR/>
     *    The most extensively used method in the framework so run the unit tests regularly<BR/>
     * when modifying it.<BR/>
     *<BR/>
     *<BR/>
     * For each row processed, it determines whether sufficient information exists<BR/>
     * (i.e. PK columns on both data and this object, PK value on current row)<BR/>
     * etc) to perform an update. If the requirements are not met, then inserts will be performed.<BR/>
     * <BR/>
     * Use of call-back methods for special functionality<BR/>
     * --------------------------------------------------<BR/>
     * By specifying callback methods, you may extend replicate<BR/>
     * so that it calls your specified object/method at specific points within the replicate method.<BR/>
     *<BR/>
     * Call these methods before you call replicate() in order to control program-flow:<BR/>
     *  Hint: You can be really clever and call these methods from within callback methods to<BR/>
     *  control program flow dynamically during replicate()<BR/>
     *<BR/>
     * setCallbackMethod()<BR/>
     * setPostRowOff()<BR/>
     *        Hint: In beforePostMethod() you can decide whether or not a row is not to be posted<BR/>
     *                    by calling setPostRowOff()<BR/>
     *<BR/>
     * @parameter DataAccess &$data reference to DataAccess object from which to copy
     * @access public
     * @return boolean Returns TRUE if any rows have been posted to the target
     */
    function replicate(&$data)
    {
        $this->setMethodName("replicate");
        $ret = FALSE;
        $this->_colCount = $this->colCount();
        if (!is_subclass_of(
            $data,
            DA_CLASSNAME_DATAACCESS
        ))
            $this->raiseError("The object passed is not a subclass of " . DA_CLASSNAME_DATAACCESS);
        // For safty's sake, at the moment we do not allow replicate to delete all rows from Tdobase
        // objects.
        if (
            is_subclass_of(
                $this,
                DA_CLASSNAME_DATASET
            ) or
            ($this->getClassname() == DA_CLASSNAME_DATASET)
        ) {
            if ($this->getClearRowsBeforeReplicate()) {
                $this->clearRows();
            }
        }
        if (        // Only relevant to reset row pointer on tdataset classes
            is_subclass_of(
                $data,
                DA_CLASSNAME_DATASET
            ) or
            ($data->getClassname() == DA_CLASSNAME_DATASET)
        ) {
            $data->initialise();
        }
        $this->allowUpdate = FALSE;
        /*
        Copy columns
        */
        $this->copyColumnsFrom($data);
        /*
        Determine whether rows on this object may be updated by data object
        */
        $crossRef = array();
        for ($ixCol = 0; $ixCol < $data->_colCount; $ixCol++) {
            $ixThisColumn = $this->columnExists($data->getName($ixCol));
            if ($ixThisColumn != -1) { // column exists

                // add to cross-ref table for columns
                $crossRef[$ixCol] = $ixThisColumn;
                $thisName = $this->getName($ixThisColumn);
                if (($this->getPKName() != "") & ($thisName == $this->getPKName())) {
                    $data->setPK($thisName);
                    $this->allowUpdate = TRUE;
                }
            }
        }
        if ($this->afterColumnsCreatedMethod != "") {
            $this->callback(
                DA_AFTER_COLUMNS_CREATED,
                $data
            );
        }
        /*
        Copy data rows(update or insert)
        */
        if (!$data->firstRowFetched) {        // in case get_row has been called
            $data->fetchNext();
        }

        while (!$data->eof) {
            /*
            Update row on this object if allowed and the primary key column in $data has a value
            otherwise insert a new row.

            If the primary key value in data is not found on any row in this then do nothing to this
            */
            $this->setPostRowOn();                    // Default = call the post() method

            $this->setUpdateModeInsert();    // Default data set operation is insert
            /*
            Set mode to update if we are allowed to and data has a pk value.
            NOTE: do not set a PK on Tdataset
            objects unless you really intend to update their rows!
            You will just end up with an empty dataset.
            */
            if ($this->allowUpdate) {
                if ($data->getPKValue() != $this->getNewRowValue()) {
                    $this->setPKValue($data->getPKValue());
                    if (!$this->setUpdateModeUpdate()) {
                        $this->setUpdateModeInsert();    // In case row does not exist
                    }
                }
            }
            /*
            Note: The callback may be used in order to set postRow to FALSE thus skipping post()
            */
            if ($this->beforePostMethod != "") {
                $this->callback(
                    DA_BEFORE_POST,
                    $data
                );
            }
            // Replaced use of composeRow because ot was far too slow with it's column name lookups
            // Using the new $crossRef array to resolve column numbers accross dataaccess objects
            if ($this->postRow) {
                for ($ixCol = 0; $ixCol < $data->_colCount; $ixCol++) {
                    $this->setValueNoCheckByColumnNumber(
                        $crossRef[$ixCol],
                        $data->getValueNoCheckByColumnNumber($ixCol)
                    );
                }
                $this->post();
                $ret = TRUE;
            }
            /*
            Unless the source is a DBEntity, update the source data row with the new PK generated if it
            exists and doesn't already have a value.
            */
            if (
                (!is_subclass_of(
                    $data,
                    DA_CLASSNAME_DBENTITY
                )) &
                ($this->getUpdateMode() == DA_MODE_INSERT) &
                ($data->getPKName() != "") &&
                ($data->getPKValue() == $this->getNewRowValue())
            ) {
                $data->setUpdateModeUpdate();
                $data->setPKValue($this->getPKValue());
                $data->post();
            }
            if ($this->afterPostMethod != "") {
//				$ret=($this->callback(DA_AFTER_POST, $data));
                $this->callback(
                    DA_AFTER_POST,
                    $data
                );
            }
            $data->fetchNext();
        }
        if (
            is_subclass_of(
                $this,
                DA_CLASSNAME_DATASET
            ) or
            ($this->getClassname() == DA_CLASSNAME_DATASET)
        ) {
            $this->initialise();                // So the dataset is ready to use
        }

        return $ret;
    }

    function composeRow(&$data)
    {
        for ($ixCol = 0; $ixCol < $data->_colCount; $ixCol++) { // Only add data column exists
//			if ($this->columnExists($data->getName($ixCol))!=DA_OUT_OF_RANGE){
            $this->failOutOfRange = FALSE;
            $this->setValue(
                $data->getName($ixCol),
                $data->getValueNoCheckByColumnNumber($ixCol)
            );
        }
    }

    /**
     * Assign one of the callback methods to call the given method on the given callback object
     * @access public
     * @param integer $methodType DA_AFTER_COLUMNS_CREATED DA_BEFORE_POST DA_AFTER_POST
     * @param BaseObject $object Reference to callback object
     * @param string $methodname Name of the method on the callback object
     * @return  bool
     */
    function setCallbackMethod($methodType,
                               &$object,
                               $methodName
    )
    {
        $this->setMethodName("setCallbackMethod");
        if (!is_object($object))
            $this->raiseError("No callback object passed");
        if ($methodName == "")
            $this->raiseError("No method passed");
        if (!method_exists(
            $object,
            $methodName
        ))
            $this->raiseError("Method " . $methodName . "() does not exist on the callback object");

        switch ($methodType) {
            case DA_AFTER_COLUMNS_CREATED:
                $this->afterColumnsCreatedObject =& $object;
                $this->afterColumnsCreatedMethod = $methodName;
                break;
            case DA_BEFORE_POST:
                $this->beforePostObject =& $object;
                $this->beforePostMethod = $methodName;
                break;
            case DA_AFTER_POST:
                $this->afterPostObject =& $object;
                $this->afterPostMethod = $methodName;
                break;
            default:
                $this->raiseError($methodType . " is not a valid callback method type");
        }
        return TRUE;
    }

    /**
     * Reset one of the callback methods
     * @access public
     * @param integer $methodType DA_AFTER_COLUMNS_CREATED DA_BEFORE_POST DA_AFTER_POST
     * @return  bool
     */
    function resetCallbackMethod($methodType)
    {
        $this->setMethodName("resetCallbackMethod");
        switch ($methodType) {
            case DA_AFTER_COLUMNS_CREATED:
                $this->afterColumnsCreatedMethod = "";
                break;
            case DA_BEFORE_POST:
                $this->beforePostMethod = "";
                break;
            case DA_AFTER_POST:
                $this->afterPostMethod = "";
                break;
            default:
                $this->raiseError($methodType . " is not a valid callback method type");
        }
        return TRUE;
    }

    /**
     * Call a callback method on the callback object
     *    Notes: Builds and executes the appropriate PHP code dynamically. To speed things up, we assume
     * that all validation has been done when callback object and methods were assigned!
     * @access private
     * @param integer $method DA_AFTER_COLUMNS_CREATED DA_BEFORE_POST DA_AFTER_POST
     * @param DataAccess &$data Reference of source dataaccess object to be passed back
     * @return  bool
     */
    function callback($methodType,
                      &$data
    )
    {
        $ret = TRUE;
        switch ($methodType) {
            case DA_AFTER_COLUMNS_CREATED:
                $cmd = "\$ret=\$this->afterColumnsCreatedObject->" . $this->afterColumnsCreatedMethod;
                break;
            case DA_BEFORE_POST:
                $cmd = "\$ret=\$this->beforePostObject->" . $this->beforePostMethod;
                break;
            case DA_AFTER_POST:
                $cmd = "\$ret=\$this->afterPostObject->" . $this->afterPostMethod;
                break;
            default:
                $this->raiseError($methodType . " is not a valid callback method");
        }
        $cmd = $cmd . "(\$data,\$this);";
        eval($cmd);
        return $ret;
    }

    /**
     * Cause replicate function to call the post() function on the destination
     * @access public
     * @return  bool
     */
    function setPostRowOn()
    {
        $this->postRow = TRUE;
        return TRUE;
    }

    /**
     * Cause replicate function to not call the post() function on the destination
     * @access public
     * @return  bool
     */
    function setPostRowOff()
    {
        $this->postRow = FALSE;
        return TRUE;
    }

    /**
     * Search the given column for the first value matching parameter passed
     * @access public
     * @param string $ixColumn Name or number of column
     * @param string $value Value to search for
     * @return bool Found: TRUE or FALSE
     */
    function search($ixColumn,
                    $value
    )
    {
        $ret = FALSE;
        $ixColumnNumber = $this->columnExists($ixColumn);
        if ($ixColumnNumber != DA_OUT_OF_RANGE) {
            $this->initialise();
            while ($this->fetchNext()) {
                if ($this->getValue($ixColumnNumber) == $value) {
                    $ret = TRUE;
                    break;// out of the loop
                }
            }
        } else {
            $this->raiseError("Search Column " . $ixColumn . " out of range");
            $ret = FALSE;
        }
        return $ret;
    }

    /**
     * Set the literal for quoting column values to single quote character
     * @access public
     * @return bool Success: TRUE or FALSE
     */
    function setQuoteForColumnValuesSingle()
    {
        $this->quoteForColumnValues = DA_QUOTE_SINGLE;
        return TRUE;
    }

    /**
     * Set the literal for quoting column values to double quote character
     * @access public
     * @return bool Success: TRUE or FALSE
     */
    function setQuoteForColumnValuesDouble()
    {
        $this->quoteForColumnValues = DA_QUOTE_DOUBLE;
        return TRUE;
    }

    /** don't think this is needed KA20/5/2002
     *
     * function set_class_name($class_name){
     * $this->class_name=$class_name;
     * return TRUE;
     * }
     *
     * function get_class_name(){
     * return $this->class_name;
     * }
     */
    /**
     * Reset the primary key
     * @access public
     * @return bool Success: TRUE or FALSE
     */
    function resetPK()
    {
        $this->pk = DA_PK_NOT_SET;
        return TRUE;
    }

    /**
     * Set the primary key value
     * @access public
     * @param string PK value
     * @return bool Success: TRUE or FALSE
     */
    function setPKValue($value)
    {
        return ($this->setValue(
            $this->getPKName(),
            $value
        ));
    }

    /**
     * Set the primary key column (name or column number)
     * @access public
     * @return bool Success: TRUE or FALSE
     */
    function setPK($ixPassedColumn)
    {
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->pk = $ixColumn;
            $ret = TRUE;
        } else {
            $this->raiseError("Primary Key Column " . $ixPassedColumn . " out of range");
            $ret = FALSE;
        }
        return $ret;
    }

    /**
     * Get the primary key column number
     * @access public
     * @return integer Column number
     */
    function getPK()
    {
        return $this->pk;
    }

    /**
     * Get the primary key column name
     * @access public
     * @return string Column name or empty string
     */
    function getPKName()
    {
        if ($this->getPK() == DA_PK_NOT_SET) {
            return "";
        } else {
            return $this->getName($this->getPK());
        }
    }

    /**
     * Get the primary key value
     * @access public
     * @return string PK Column value
     */
    function getPKValue()
    {
        if ($this->getPK() == DA_PK_NOT_SET) {
            $this->raiseError("No PK set");
        } else {
            return $this->getValue($this->getPK());
        }
    }

    /**
     * Control the action of the replicate method so that the destination is cleared first
     * @access public
     * @return bool success
     */
    function setClearRowsBeforeReplicateOn()
    {
        $this->clearRowsBeforeReplicate = TRUE;
        return TRUE;
    }

    /**
     * Control the action of the replicate method so that the destination set is added to
     * @access public
     * @return bool success
     */
    function setClearRowsBeforeReplicateOff()
    {
        $this->clearRowsBeforeReplicate = FALSE;
        return TRUE;
    }

    /**
     * Report value of class var
     * @access public
     * @return bool on or off
     */
    function getClearRowsBeforeReplicate()
    {
        return $this->clearRowsBeforeReplicate;
    }

    /**
     * Check whether column already exists and return column number
     * @access private
     * @param string column Column name or number
     * @return string column number or DA_OUT_OF_RANGE (instead of FALSE because FALSE is zero)
     */
    function columnExists($ixColumn)
    {
        if ($this->_colCount == 0) return DA_OUT_OF_RANGE;
        $ixColumnNo = $this->colNameInverse[$ixColumn];

        if ($ixColumnNo === FALSE or is_null($ixColumnNo)) {

            if (gettype($ixColumn) != DA_STRING) {

                if ($ixColumn < $this->_colCount) {

                    return $ixColumn;

                } else {

                    return DA_OUT_OF_RANGE;

                }
            } else {

                return DA_OUT_OF_RANGE;

            }
        } else {

            return $ixColumnNo;

        }
    }

    /**
     * Return column number
     * This is very similar to columnExists but it will raise an error if the column
     * does not exist. Use it to save checking yourself afterwards
     * @access private
     * @param string column Column name or number
     * @return string column number or DA_OUT_OF_RANGE (instead of FALSE because FALSE is zero)
     */
    function getValidColumnNo($ixColumn)
    {
        $columnNo = $this->columnExists($ixColumn);
        if ($columnNo == DA_OUT_OF_RANGE) {
            $this->raiseError(DA_MSG_COLUMN_DOES_NOT_EXIST);
        } else {
            return $columnNo;
        }
    }

    /**
     * Allow columns to be added to the object
     * @access public
     * @return bool Success
     */
    function setAddColumnsOn()
    {
        return ($this->allowAddColumns = TRUE);
    }

    /**
     * Prevent columns from being added to the object - useful during replicate
     * @access public
     * @return bool Success
     */
    function setAddColumnsOff()
    {
        return ($this->allowAddColumns = FALSE);
    }

    /**
     * Add a new column to the object
     * @param string Column name
     * @param string type Column type - default is string
     * @param null Column type - default is string
     * @access public
     * @return integer New column number or DA_COLUMN_NOT_ADDED
     */
    function addColumn($arg)
    {
        // In case the destination dataset doesn't want all of the
        // source dataset's columns
        $ret = DA_COLUMN_NOT_ADDED;
        if ($this->allowAddColumns) {
            $numArgs = func_num_args();
            if ($numArgs < 3) {
                $this->raiseError("AddColumn: Must pass at least three parameters");
            } else {
                $name = func_get_arg(0);
                $type = func_get_arg(1);
                $null = func_get_arg(2);
                $validationFunction = null;
                if ($numArgs > 3) {
                    $validationFunction = func_get_arg(3);
                }
                // add a column name only once
                $ixColumn = $this->columnExists($name);
                if ($ixColumn == DA_OUT_OF_RANGE) {
                    $ixColumn = $this->_colCount;    // Add to end
                    $this->setNameAndType(
                        $ixColumn,
                        $name,
                        $type,
                        $null,
                        $validationFunction
                    );
                }
                $ret = $ixColumn;        // found column
            }
            $this->_colCount = $this->colCount(); // Added this to avoid overhead of calling colCount()
//			if ($this->_colCount > DA_SERIAL_LIMIT){ 
//				$this->setXRef();
//			}
        }                                            // between index(column no) and name
        return $ret;
    }
    /*
        function setXRef(){
            // columnExists need to do a fast binary search but this requires some buggering about!
            // We need to asort the column names array to retain association between name and col no
            // So... we need a new array to Xref the each of the column names to an ordinal representing
            // their position in the sorted array
            asort($this->colName); 					// Sort the names array and maintain association
            reset($this->colName);
            // Add a new key, order, to each element for use in columnexists binary search:
            $this->colNameXRef=array();				// clear down
            foreach ($this->colName as $key => $value) {
               $this->colNameXRef[]=$key;
            }
        }
    */
    /**
     * Add a new column to the object without checking whether it exists (for performance puposes)
     * @param string Column name
     * @param string type Column type
     * @param null Column type
     * @access public
     * @return integer New column number
     */
    function addColumnNoCheck($name,
                              $type,
                              $null
    )
    {
        $ixColumn = $this->_colCount;    // Add to end
        $this->setNameAndType(
            $ixColumn,
            $name,
            $type,
            $null
        );
//		if ($this->_colCount > DA_SERIAL_LIMIT){ //Use xref for binary search?s
//			$this->setXRef();
//		}
        return $ixColumn;
    }

    /**
     * Set-up columns on the object
     * @access private
     * @param $ixColumn
     * @param string $name Column name
     * @param integer $type Column type See DA_ constants for values
     * @param integer $null Nulls allowed: DA_ALLOW_NULL DA_NOT_NULL
     * @param $validationFunction
     * @return bool Success
     */
    function setNameAndType($ixColumn,
                            $name,
                            $type,
                            $null,
                            $validationFunction = null
    )
    {
        // Note: Must call setName first to create column
        $this->setName(
            $ixColumn,
            $name
        );
        $this->setType(
            $ixColumn,
            $type
        );
        $this->setNull(
            $ixColumn,
            $null
        );
        $this->setValidationFunction(
            $ixColumn,
            $validationFunction
        );
        return TRUE;
    }

    /**
     * Get column name by index
     * @access public
     * @param integer $ixColumn Column number
     * @return string Column name or empty string of out of range
     */
    function getName($ixColumn)
    {
        if ($this->columnExists($ixColumn) != DA_OUT_OF_RANGE) {
            return $this->colName[$ixColumn];
        } else {
            $this->raiseError("Column " . $ixColumn . " out of range");
            return "";
        }
    }

    /**
     * Set column name for given column
     * NOTE: Adds new column if it doesn't exist already
     * @access public
     * @param integer $ixColumn Column number
     * @param string Column name
     * return boolean Success
     */
    function setName($ixColumn,
                     $name
    )
    {
        $this->colName[$ixColumn] = $name;
        $this->colNameInverse[$name] = $ixColumn;
        $this->_colCount = $this->colCount();
        return TRUE;
    }

    /**
     * Get column type
     * @access public
     * @param integer $ixColumn Column number
     * @return string Column type. See constants for list.
     */
    function getType($ixPassedColumn)
    {
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            return $this->colType[$ixColumn];
        } else {
            $this->raiseError("Column " . $ixPassedColumn . " out of range");
            return DA_OUT_OF_RANGE;
        }
    }

    /**
     * Get column type
     * @access public
     * @param integer $ixColumn Column number
     * @return string Column type. See constants for list.
     */
    function getTypeByColumnNumberNoCheck($ixColumn)
    {
        return $this->colType[$ixColumn];
    }

    /**
     * Set column type
     * @access private
     * @param integer $ixColumn Column number
     * @param integer $type Column type. See constants for list.
     * @return boolean Success
     */
    function setType($ixPassedColumn,
                     $type
    )
    {
        $ret = FALSE;
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->colType[$ixColumn] = $type;
            $ret = TRUE;
        } else {
            $this->raiseError("SetType(): Column " . $ixPassedColumn . " out of range");
        }
        return $ret;
    }

    /**
     * Get column null status
     * @access public
     * @param integer $ixColumn Column number
     * @return integer Nulls allowed: DA_ALLOW_NULL DA_NOT_NULL or DA_OUT_OF_RANGE
     */
    function getNull($ixPassedColumn)
    {
        $ret = FALSE;
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $ret = $this->colNull[$ixColumn];
        } else {
            $this->raiseError("GetNull(): Column " . $ixPassedColumn . " out of range");
            $ret = DA_OUT_OF_RANGE;
        }
        return $ret;
    }

    /**
     * Set column type
     * @access private
     * @param integer $ixColumn Column number
     * @param integer $nullFlag DA_ALLOW_NULL DA_NOT_NULL
     * @return boolean Success
     */
    function setNull($ixColumn,
                     $nullflag
    )
    {
        $ret = FALSE;
        $ixColumn = $this->columnExists($ixColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->colNull[$ixColumn] = $nullflag;
            $ret = TRUE;
        } else {
            $this->raiseError("SetNull(): Column " . $ixColumn . " out of range");
        }
        return $ret;
    }

    /**
     * Set column to allow empty values
     * @access private
     * @param integer $ixColumn Column number
     * @param integer $nullFlag DA_ALLOW_NULL DA_NOT_NULL
     * @return boolean Success
     */
    function setAllowEmpty($ixColumn)
    {
        return ($this->setNull(
            $ixColumn,
            DA_ALLOW_NULL
        ));
    }

    /**
     * Set column to NOT allow empty values
     * @access private
     * @param integer $ixColumn Column number
     * @param integer $nullFlag DA_ALLOW_NULL DA_NOT_NULL
     * @return boolean Success
     */
    function setNotAllowEmpty($ixColumn)
    {
        return ($this->setNull(
            $ixColumn,
            DA_NOT_NULL
        ));
    }

    /**
     * Set all column values to empty
     * @access private
     * @return boolean Success
     */
    function setRowBlank()
    {
        for ($ix = 0; $ix < $this->_colCount; $ix++) {
            $this->row[$ix] = "";
        }
        return TRUE;
    }

    /**
     * Get count of columns
     * @access private
     * @return integer Number of columns
     */
    function colCount()
    {
        return count($this->colName);
    }

    /**
     * Reset update mode of dataset
     * @access private
     * @return boolean Success
     */
    function resetUpdateMode()
    {
        $this->updateMode = DA_MODE_NONE;
        return TRUE;
    }

    /**
     * When post is called, insert a new row
     * @access private
     * @return boolean Success
     */
    function setUpdateModeInsert()
    {
        $this->updateMode = DA_MODE_INSERT;
        return TRUE;
    }

    /**
     * When post is called, update the current row
     * @access private
     * @return boolean Success
     */
    function setUpdateModeUpdate()
    {
        $this->updateMode = DA_MODE_UPDATE;
        return TRUE;
    }

    /**
     * When post is called, delete the current row
     * @access private
     * @return boolean Success
     */
    function setUpdateModeDelete()
    {
        $this->updateMode = DA_MODE_DELETE;
        return TRUE;
    }

    /**
     * Get the current update mode
     * @access private
     * @return integer DA_MODE_NONE,DA_MODE_DELETE, DA_MODE_INSERT, DA_MODE_UPDATE
     */
    function getUpdateMode()
    {
        return $this->updateMode;
    }

    /**
     * Make an update to the current row on the data set
     * Action depends upon value of $this->getUpdateMode()
     * @access public
     * @return boolean Success status
     */
    function post()
    {
        if ($this->getUpdateMode() == DA_MODE_NONE) {
            $this->raiseError("Could not post row because UpdateMode not set");
            return FALSE;
        } else {
            return TRUE;
        }
        // inherited post method must be implemented here
    }

    /**
     * fetch next row in result set. Must be overriden and called first in the
     * overriden method. Make sure you set the return parameter with EOF value in your extended class
     * @access public
     * @return boolean EOF
     */
    function fetchNext()
    {
        $this->eof = FALSE;
        $this->firstRowFetched = TRUE;
    }

    /**
     * Get column value by name or column number
     * @access public
     * @param string $ixColumn
     * @return string|int|float|bool Column value
     */
    function getValue($ixPassedColumn)
    {
        $this->setMethodName('getValue');
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            return $this->getValueNoCheckByColumnNumber($ixColumn);
        } else {
            $this->raiseError("column " . $ixPassedColumn . " out of range");
            return DA_OUT_OF_RANGE;
        }
    }

    /**
     * Alias for getValueNoCheckByColumnNumber
     * @access public
     * @param string $ixColumn
     * @return variant Column value
     */
    function getValueByColumnNumber($ixColumnNumber)
    {
        return $this->getValueNoCheckByColumnNumber($ixColumnNumber);
    }

    /**
     * Get column value by name or column number without any error trapping
     * @access public
     * @param string $ixColumn
     * @return variant Column value
     */
    function getValueNoCheckByColumnNumber($ixColumnNumber)
    {
        $type = $this->getTypeByColumnNumberNoCheck($ixColumnNumber);
//		if (($type==DA_ID)|($type==DA_INTEGER)){ //I changed this for CNC numeric form checking
        if ($type == DA_ID) {
            return (int)$this->row[$ixColumnNumber];
        } else {
            return $this->row[$ixColumnNumber];
        }
    }
    /**
     * Set row values assuming the arguments are in column index order
     * NOTE: Not sure this is called - please test and remove if not
     * @access public
     * function setRowValues($arg){
     * $numArgs = func_num_args();
     * if ($numArgs>0){
     * for($ixArg=0;$ixArg<($numArgs);$ixArg++){
     * $value = func_get_arg($ixArg);
     * $this->setValue($ixArg, $value);
     * }
     * }
     * }
     */
    /**
     * Set column value by name or index
     * @access public
     * @param string $ixColumn Column number or name
     * @param string $value Value
     * @return boolean Success
     */
    function setValue($ixPassedColumn,
                      $value
    )
    {
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            if (
                ($this->getNull($ixColumn) == DA_NOT_NULL) &&
                ($this->colType[$ixColumn] != DA_BOOLEAN) &&
                ($value == "") &&
                ($this->getPK() != $ixColumn) &&
                (!$this->getIgnoreNULL())
            ) {
                $this->raiseError(
                    "Could not set column value because " . $ixPassedColumn . " does not accept NULL values."
                );
                return FALSE;
            } else {
                $this->row[$ixColumn] = $value;
                return TRUE;
            }
        } else {
            if ($this->failOutOfRange) {
                $this->raiseError("Could not set column value because " . $ixPassedColumn . " out of range");
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }

    /**
     * Set column value by index without any error checking
     * @access public
     * @param string $ixColumn Column number
     * @param string $value Value
     * @return boolean Success
     */
    function setValueNoCheckByColumnNumber($ixPassedColumn,
                                           $value
    )
    {
        $this->row[$ixPassedColumn] = $value;
    }

    /**
     * Initialise the object so that the row pointer is at the first row. Does not affect data rows.
     * Override this and call parent::initialise() at end
     * @access public
     * @return boolean Success
     */
    function initialise()
    {
        $this->firstRowFetched = FALSE;
        return TRUE;
    }

    /**
     * Clear-down the object as if it has just been created with no rows or columns
     * @access public
     * @return boolean Success
     */
    function clear()
    {
        $this->clearColumns();
        $this->clearRows();
        $this->resetPK();
        $this->setAddColumnsOn();
        $this->setClearRowsBeforeReplicateOff();
        $this->resetUpdateMode();
        $this->resetCallbackMethod(DA_AFTER_COLUMNS_CREATED);
        $this->resetCallbackMethod(DA_BEFORE_POST);
        $this->resetCallbackMethod(DA_AFTER_POST);
        $this->eof = FALSE;
        $this->initialise();
        return TRUE;
    }

    /**
     * Remove all columns
     * @access public
     * @return boolean Success
     */
    function clearColumns()
    {
        $this->setMethodName("clearColumns");
        $this->clearRows();
        $this->colName = array();
        $this->colType = array();
        $this->colNull = array();
        return TRUE;
    }

    /**
     * Remove all row data
     * @access public
     * @return boolean Success
     */
    function clearRows()
    {
        $this->setMethodName("clearRows");
        $this->clearCurrentRow();
        return TRUE;
    }

    /**
     * Clear current row data
     * @access public
     * @return boolean Success
     */
    function clearCurrentRow()
    {
        $this->row = array();    // clear the "current row" array
        return TRUE;
    }

    /**
     * Build and return string of CSV column names
     * @access private
     * @return string Column names as CSV string
     */
    function getColumnNamesAsString()
    {
        $this->setMethodName("getColumnNamesAsString");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->_colCount; $ixCol++) {
            if ($colString != "") $colString = $colString . DA_COLUMN_SEPARATOR;
            $colString = $colString . $this->getName($ixCol);
        }
        return $colString;
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
        for ($ixCol = 0; $ixCol < $this->_colCount; $ixCol++) {
            if ($colString != "") $colString = $colString . DA_COLUMN_SEPARATOR;
            $colString = $colString .
                $this->quoteForColumnValues .
                $this->getValue($ixCol) .
                $this->quoteForColumnValues;
        }
        return $colString;
    }

    /**
     * Copy columns from given dataset
     * @access public
     * @return boolean Success
     */
    function copyColumnsFrom(&$data)
    {
        $this->setMethodName("copyColumnsFrom");
        if (!is_subclass_of(
            $data,
            DA_CLASSNAME_DATAACCESS
        )) {
            $this->raiseError("The object passed is not a subclass of " . DA_CLASSNAME_DATAACCESS);
        }
        for ($ixCol = 0; $ixCol < $data->_colCount; $ixCol++) {
            $this->copyColumn(
                $data,
                $ixCol
            );
        }
        return TRUE;
    }

    /**
     * Add a column to this dataset
     * if the column does not exist and we are allowed to add columns, add it
     * @access Public
     * @param integer $ixCol Column on source dataset to be added to this one
     */
    function copyColumn(&$data,
                        $ixCol
    )
    {
        $ixThisColumn = $this->columnExists($data->getName($ixCol));
        if (($ixThisColumn == -1) & ($this->allowAddColumns)) {
            $ixThisColumn =
                $this->addColumnNoCheck(
                    $data->getName($ixCol),
                    $data->getType($ixCol),
                    $data->getNull($ixCol)
                );
        }
    }

    /**
     * Build and return string of escaped CSV column values for use in Excel
     * @access private
     * @return string
     */
    function getColumnValuesForExcel()
    {
        $this->setMethodName("getColumnValuesForExcel");
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            if ($colString != "") $colString = $colString . DA_COLUMN_SEPARATOR;
            $value = $this->getExcelValue($ixCol);
            $colString = $colString .
//				$this->quoteForColumnValues.
                $value;//.
//				$this->quoteForColumnValues;
        }
        return $colString;
    }

    function getExcelValue($ixCol)
    {

        $value = $this->getValue($ixCol);
        $value = str_replace(
            ',',
            '',
            $value
        );
        $value = str_replace(
            "\r\n",
            " ",
            $value
        );            // remove carrage returns
        $value = str_replace(
            "\"",
            "",
            $value
        );                // and double quotes

        return $value;
    }

    private function setValidationFunction($ixColumn,
                                           $validationFunction
    )
    {
        $ret = FALSE;
        $ixColumn = $this->columnExists($ixColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->colValidation[$ixColumn] = $validationFunction;
            $ret = TRUE;
        } else {
            $this->raiseError("SetNull(): Column " . $ixColumn . " out of range");
        }
        return $ret;
    }

}

?>