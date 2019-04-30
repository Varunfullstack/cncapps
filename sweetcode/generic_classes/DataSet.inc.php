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
    'SUPPORT_LEVEL_MSG_INCORRECT_VALUE',
    'The value is not acceptable'
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

define(
    'DATASET_MSG_INVALID_SUPPORT_LEVEL',
    'The given value does not match the possible values: null, "main", "supervisor", "support", "delegate"'
);

class DataSet extends DataAccess
{

    var $rows;        // array of rows of data
    var $ixCurrentRow;    // Index to current $rows element

    /**
     * constructor
     * If arguments are passed then these are used as string columns to create
     * @access public
     * @param $owner
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
     * @param $ixColumn
     * @return boolean
     */
    function sortAscending($ixColumn
    )
    {
        $this->setMethodName("sortAscending");
        $ret = FALSE;

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
                    $this->raiseError('columnSort(). Column ' . $arg . " out of range");
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
     * @param $fileName
     * @return boolean
     */
    function loadFromCSVFile($fileName)
    {
        $this->setMethodName("loadFromCSVFile");
        // Open the file
        $pointer = fopen(
            $fileName,
            "r"
        );
        if (!$pointer) {
            $this->raiseError("Unable to open file " . $fileName);
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
     * @param $fileName
     * @return boolean
     */
    function saveToCSVFile($fileName)
    {
        $this->setMethodName("saveToCSVFile");
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
                preg_replace(
                    "/[\n\r]/",
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
        $searchArray = [];
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

    function getAllMessages()
    {
        $messages = [];

        foreach ($this->colName as $index => $name) {
            if (in_array(
                'Message',
                $name
            )) {
                $messages[] = $name;
            }
        }
        return $messages;
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
    function populateFromArray($entityArray)
    {
        if ($this->debug) {
            echo '<br>';
            var_dump($entityArray);
            echo '<br>';
        }
        $this->setMethodName('populateFromArray');
        $ret = TRUE;
        if (!is_array($entityArray)) {
            $this->raiseError('entityArray not an array');
        }
        foreach ($entityArray as $key => $row) {
            $this->setUpdateModeInsert();
            foreach ($row as $fieldName => $value) {
                if ($this->debug) {
                    var_dump($fieldName, $value);
                }
                $columnIdx = $this->columnExists($fieldName);
                if ($columnIdx == DA_OUT_OF_RANGE) {
                    $this->setMessage(
                        $fieldName,
                        'This column does not exist'
                    );
                    continue;
                }

                $this->setValueNoCheckByColumnNumber(
                    $columnIdx,
                    $value
                );
                $validation = $this->checkValid($fieldName, $value);
                if ($validation !== true) {
                    $this->setMessage(
                        $fieldName,
                        $validation
                    );
                    $ret = false;
                    continue;
                }

                $this->setValue(
                    $fieldName,
                    $value
                );
            }
        }
        $this->post();
        return $ret;
    }

    /**
     * @param string $columnName Column for which to set message
     * @param string $message Message
     * @desc Set a form error message
     * @return void
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

}