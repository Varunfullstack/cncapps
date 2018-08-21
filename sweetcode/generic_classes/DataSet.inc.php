<?php
/**
 * Contains and manipulates a set of data in memory
 *
 * If you subclass this class then you will need to look at the use of the DA_CLASSNAME_DATASET
 * as it is key to the correct operation of DataAccess->replicate()
 *
 * @access public
 * @author Karim Ahmed
 */
require_once($cfg["path_gc"] . "/DataAccess.inc.php");
define(
    'DATASET_MSG_REQUIRED',
    'Required'
);
define(
    'DATASET_MSG_NOT_NUMERIC',
    'Must be a number'
);
define(
    'DATASET_MSG_BAD_DATE_FORMAT',
    'Please use DD/MM/YYYY'
);
define(
    'DATASET_MSG_INVALID_DATE',
    'This date is not valid'
);
define(
    'DATASET_MSG_BAD_TIME',
    'Please enter a valid time (HH:MM)'
);
define(
    'DATASET_MSG_INVALID',
    'The given value does not conform with the format'
);

class DataSet extends DataAccess
{

    var $rows;        // array of rows of data
    var $ixCurrentRow;    // Index to current $rows element

    /**
     * constructor
     * If arguments are passed then these are used as string columns to create
     * @access public
     * @return void
     */

    /*
    I am not using the usual "constructor" function because I can't see how to preserve the
    variable-length argument list accross the call.

    As this class is unlikely to be inherrited from, it probably doesn't matter
    anyhow.
    */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    /**
     * Clear variables as if this dataset object has just been created
     * @access public
     * @return boolean
     */
    function clear()
    {
        parent::clear();
        $this->setClearRowsBeforeReplicateOn();
        $this->setQuoteForColumnValuesDouble();
        return TRUE;
    }

    /**
     * Initialise internal row counter to first row in the dataset
     * @access public
     * @return boolean
     */
    function initialise()
    {
        parent::initialise();
        $this->ixCurrentRow = DA_NOT_SET;
        return TRUE;
    }

    /**
     * Increment internal row counter
     * @access private
     * @return boolean
     */
    function incrementRowCounter()
    {
        $this->setMethodName("incrementRowCounter");
        $this->ixCurrentRow++;
        return true;
    }

    /**
     * Commit update to the dataset based upon current update_mode
     * @access public
     * @return boolean
     */
    function post()
    {
        parent::post();
        switch ($this->getUpdateMode()) {
            case DA_MODE_INSERT:
                $this->ixCurrentRow = $this->rowCount(); // One past the end
                $this->rows[$this->ixCurrentRow] = $this->row;
                $ret = TRUE;
                break;
            case DA_MODE_UPDATE:
                $this->rows[$this->ixCurrentRow] = $this->row;
                $ret = TRUE;
                break;
            default:
                $ret = FALSE;
                break;
        }
        $this->resetUpdateMode();
        return $ret;
    }

    /**
     * Advance one row in the dataset
     * @access public
     * @return boolean Success
     */
    function fetchNext()
    {
        parent::fetchNext();
        if (($this->ixCurrentRow + 1) >= $this->rowCount()) {
            $this->eof = TRUE;
            return FALSE;
        } else {
            $this->ixCurrentRow++;
            // Move new current row in data set to row array
            $this->row = $this->rows[$this->ixCurrentRow];
            return TRUE;
        }
    }

    /**
     * Get number of rows in dataset
     * @access public
     * @return integer Number of rows
     */
    function rowCount()
    {
        return count($this->rows);
    }

    /**
     * Delete all rows in the dataset
     * @access private
     * @return boolean
     */
    function clearRows()
    {
        parent::clearRows();
        $this->rows = array();
        return TRUE;
    }

    /**
     * Reverse the order of the rows in the dataset
     * @access public
     * @return boolean
     */
    function reverse()
    {
        $this->setMethodName("reverse");
        $this->rows = array_reverse($this->rows);
        $this->initialise();
        return TRUE;
    }

    /**
     * Sort the order of the rows in the dataset by given column
     * @parameter integer Column Column to sort on
     * @access public
     * @return boolean
     */
    function sortAscending($ixColumn,
                           $sortType = false
    )
    {
        $this->setMethodName("sortAscending");
        $ret = FALSE;

        if ($sortType === false) {
            $sortType = SORT_STRING;
        }

        if ($this->rowCount() > 0) {
            $ixColumnNumber = $this->columnExists($ixColumn);
            if ($ixColumnNumber != DA_OUT_OF_RANGE) {
                foreach ($this->rows as $val) {
                    $sortarray[] = $val[$ixColumnNumber];
                }
                array_multisort(
                    $sortarray,
                    $this->rows
                );
                $this->initialise();
                $ret = TRUE;
            } else {
                $this->raiseError("Search(). Column " . $ixColumn . " out of range");
            }
        }
        return $ret;
    }

    /**
     * array_column_sort
     *
     * function to sort an "array of rows" by its columns
     * exracts the columns to be sorted and then
     * uses eval to flexibly apply the standard
     * array_multisort function
     *
     * flexible syntax:
     * $new_array = array_column_sort($array [, 'col1' [, SORT_FLAG [, SORT_FLAG]]]...);
     *
     * original code credited to Ichier (www.ichier.de) here:
     * http://uk.php.net/manual/en/function.array-multisort.php
     *
     */
    function columnSort()
    {
        if ($this->rowCount() == 0) {
            return;
        }
        $args = func_get_args();
        $i = 0;
        $multi_sort_line = "return array_multisort( ";
        foreach ($args as $arg) {
            $i++;
            if (is_string($arg)) {
                $columnNo = $this->columnExists($arg);
                if ($columnNo == DA_OUT_OF_RANGE) {
                    $this->raiseError('columnSort(). Column ' . $ixColumn . " out of range");
                }
                foreach ($this->rows as $row_key => $row) {
                    $sort_array[$i][] = $row[$columnNo];
                }
            } else {
                $sort_array[$i] = $arg;
            }
            $multi_sort_line .= "\$sort_array[" . $i . "], ";
        }
        $multi_sort_line .= "\$this->rows );";
        eval($multi_sort_line);
        $this->initialise();
    }

    /**
     * Load data from a CSV file assuming column names in first row
     * @access public
     * @parameter String $fileName The local filesystem path from which data will be loaded
     * @return boolean
     */
    function loadFromCSVFile($fileName)
    {
        $this->setMethodName("loadFromCSVFile");
        $ret = FALSE;
        // Open the file
        $pointer = fopen(
            $fileName,
            "r"
        );
        if (!$pointer) {
            $this->raiseError("Unable to open file " . $filename);
        }
        $this->setAddColumnsOn();
        // Create the columns from first row data
        $line = fgetcsv(
            $pointer,
            1000
        );
        $numberOfColumns = count($line);
        for ($col = 0; $col < $numberOfColumns; $col++) {
            $this->addColumn(
                $line[$col],
                DA_STRING,
                DA_ALLOW_NULL
            );
        }
        // Get the data
        while ($line = fgetcsv(
            $pointer,
            1000
        )) {
            $this->setUpdateModeInsert();
            for ($col = 0; $col < $numberOfColumns; $col++) {
                $this->setValue(
                    $col,
                    $line[$col]
                );
            }
            $this->post();
        }
        fclose($pointer);
        $this->initialise();
        $ret = TRUE;
        return $ret;
    }

    /**
     * Dump data to a CSV file with column names in first row
     * @access public
     * @parameter String $fileName The local filesystem path to which data will be dumped
     * @return boolean
     */
    function saveToCSVFile($fileName)
    {
        $this->setMethodName("saveToCSVFile");
        $ret = FALSE;
        // Open the file
        $pointer = fopen(
            $fileName,
            "w"
        );
        if (!$pointer) {
            $this->raiseError("Unable to open file " . $fileName);
        }
        // Create the first row with column names
        fwrite(
            $pointer,
            $this->getColumnNamesAsString() . "\n"
        );
        // Create the data rows, all surrounded with quotes and remove any CRs
        $this->initialise();
        while ($this->fetchNext()) {
            fwrite(
                $pointer,
                ereg_replace(
                    "\n",
                    "",
                    $this->getColumnValuesAsString()
                ) . "\n"
            );
        }
        fclose($pointer);
        $ret = TRUE;
        return $ret;
    }

    /**
     * Copy columns from another dataaccess object
     * @access private
     * @parameter DataAccess $dataaccess Data access object to copy
     * @return boolean
     */
    function copyColumns($dataaccess)
    {
        $this->setMethodName("copyColumns");
        $this->clear();
        for ($col = 0; $col < $dataaccess->col_count(); $col++) {
            $this->addColumn(
                $dataaccess->getName($col),
                $dataaccess->getType($col),
                $dataaccess->getNull($col)
            );
        }
        return true;
    }

    function setValue($ixPassedColumn,
                      $value
    )
    {
//		return(parent::setValue($ixPassedColumn, stripslashes($value)));
        return (parent::setValue(
            $ixPassedColumn,
            (string)$value
        ));
    }

    function getValue($ixPassedColumn)
    {
        return ((string)parent::getValue($ixPassedColumn));
//		return(stripslashes(parent::getValue($ixPassedColumn)));
//		return(parent::getValue($ixPassedColumn));
    }

    /**
     * Search the given column for the first value matching parameter passed
     * Overriden to optomise for dataset array
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
        if ($this->rowCount() > 0) {
            $ixColumnNumber = $this->columnExists($ixColumn);
            if ($ixColumnNumber != DA_OUT_OF_RANGE) {
                // Put all the column values into a temporary search array
                foreach ($this->rows as $val) {
                    $searchArray[] = $val[$ixColumnNumber];
                }
                $ixElement = array_search(
                    $value,
                    $searchArray
                );
                if ($ixElement !== FALSE) {
                    $this->ixCurrentRow = $ixElement;
                    $this->row = $this->rows[$this->ixCurrentRow];
                    $ret = TRUE;
                }
            } else {
                $this->raiseError("search(). Column " . $ixColumn . " out of range");
            }
        }
        return $ret;
    }

    /*
    * Populate Dataset object from array
    * It uses the getNull method of the dataset to determine whether a "Field Required" message is needed
    * @param Array $entityArray Array from which to populate
    * @return Bool TRUE=No error messages: FALSE=Errors found
    *
    *	NOTE1: Designed primarily for population from HTML POST arrays in this format:
    * array['ID']['columnName']['columnValue']
    *
    * NOTE2: Wishlist:
    *		raise message on duplicates in non-duplicate fields
    *
    * NOTE3: Dates:
    *		Expects dates passed in to be DD/MM/YYYY (as entered on HTML form) BUT always
    *		converts to YYYY-MM-DD (internal Sweetcode standard) EVEN IF the date was bad
    */
    function populateFromArray(&$entityArray)
    {
        $this->setMethodName('populateFromArray');
        $ret = TRUE;
        if (!is_array($entityArray)) {
            $this->raiseError('entityArray not an array');
        }
        while (list($key, $row) = each($entityArray)) {                            // loop though rows
            $this->setUpdateModeInsert();
            while (list($fieldName, $value) = each($row)) {                        // loop through column values
                if (
                    ($this->getNull($fieldName) == DA_NOT_NULL) & ($value == '')
                ) {
                    $ret = FALSE;
                    $this->setMessage(
                        $fieldName,
                        DATASET_MSG_REQUIRED
                    );
                } else {
                    $columnType = $this->getType($fieldName);
                    // Because blank is returned from HTML form if not checked
                    if ($columnType == DA_YN) {
                        $this->setValue(
                            $fieldName,
                            ($value == 'Y' ? 'Y' : 'N')
                        );
                    }
                    if (($this->getNull($fieldName) == DA_ALLOW_NULL) & ($value == '')) {
                        $this->setValue(
                            $fieldName,
                            ''
                        );
                    } else {                // this is a not null column with a value so validate the data type
                        // Column type validation

                        $validationFunction = $this->getValidationFunction($fieldName);
                        if ($validationFunction) {
                            if (!$validationFunction($value)) {
                                $this->setMessage(
                                    $fieldName,
                                    DATASET_MSG_INVALID
                                );
                                $ret = FALSE;
                            } else {
                                $this->setValue(
                                    $fieldName,
                                    $value
                                );
                            }
                        } else {
                            switch ($columnType) {
                                case DA_DATE:
                                    if ($value != '') {

                                        $date = DateTime::createFromFormat(
                                            'd/m/Y',
                                            $value
                                        );
                                        if (!$date) {
                                            $this->setValue(
                                                $fieldName,
                                                $value
                                            );
                                            $this->setMessage(
                                                $fieldName,
                                                DATASET_MSG_BAD_DATE_FORMAT
                                            );
                                            $ret = FALSE;
                                        } else {
                                            $this->setValue(
                                                $fieldName,
                                                $date->format('Y-m-d')
                                            );
                                        }
                                    } else {
                                        $value = '0000-00-00';    // signifies no date
                                    }
                                    break;
                                case DA_TIME:
                                    if ($value != '') {
                                        if (!$this->isTime($value)) {
                                            $this->setMessage(
                                                $fieldName,
                                                DATASET_MSG_BAD_TIME
                                            );
                                            $ret = FALSE;
                                        }
                                        $this->setValue(
                                            $fieldName,
                                            $value
                                        );
                                    }
                                    break;
                                case DA_INTEGER:
                                case DA_ID:
                                case DA_FLOAT:
                                    $value = trim($value);                // remove trailing spaces
                                    if (!is_numeric($value)) {
                                        $this->setMessage(
                                            $fieldName,
                                            DATASET_MSG_NOT_NUMERIC
                                        );
                                        $ret = FALSE;
                                    }
                                    $this->setValue(
                                        $fieldName,
                                        $value
                                    );
                                    break;
                                default:
                                    $this->setValue(
                                        $fieldName,
                                        $value
                                    );
                                    break;
                            }
                        }
                    }
                }
            }
            $this->post();
        }
        return $ret;
    }

    /**
     * @return void
     * @param string $columnName Column for which to set message
     * @param string $message Message
     * @desc Set a form error message
     */
    function setMessage($columnName,
                        $message
    )
    {
        $this->addColumn(
            $columnName . 'Message',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setValue(
            $columnName . 'Message',
            $message
        );
    }

    function setAllowNullsOnAllColumns()
    {
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            $this->setNull(
                $ixCol,
                DA_ALLOW_NULL
            );
        }
    }

    function isTime($time)
    {
        if ($time == '') {
            $ret = TRUE;
        } else {
            $ret =
                (
                    is_numeric(
                        substr(
                            $time,
                            0,
                            2
                        )
                    ) &&
                    (substr(
                            $time,
                            0,
                            2
                        ) < 24) &&
                    is_numeric(
                        substr(
                            $time,
                            3,
                            2
                        )
                    ) &&
                    (substr(
                            $time,
                            3,
                            2
                        ) < 60) &&
                    (substr(
                            $time,
                            2,
                            1
                        ) == ':') &&
                    (strlen($time) == 5)
                );
        }
        return $ret;
    }

    function convertDateYMD($dateDMY)
    {
        if ($dateDMY != '') {
            $dateArray = explode(
                '/',
                $dateDMY
            );
            return ($dateArray[2] . '-' . str_pad(
                    $dateArray[1],
                    2,
                    '0',
                    STR_PAD_LEFT
                ) . '-' . str_pad(
                    $dateArray[0],
                    2,
                    '0',
                    STR_PAD_LEFT
                ));
        } else {
            return '';
        }
    }

    function currentRowNo()
    {
        return $this->ixCurrentRow;
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
            $value = str_replace(
                ',',
                '',
                $this->getValue($ixCol)
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
            $colString = $colString .
                $this->quoteForColumnValues .
                $value .
                $this->quoteForColumnValues;
        }
        return $colString;
    }

    /**
     * @param $ixPassedColumn
     * @return Callable
     */
    private function getValidationFunction($ixPassedColumn)
    {
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $ret = $this->colValidation[$ixColumn];
        } else {
            $this->raiseError("GetValidationFunction(): Column " . $ixPassedColumn . " out of range");
            $ret = DA_OUT_OF_RANGE;
        }
        return $ret;

    }
}

?>