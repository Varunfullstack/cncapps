<?php
/**
 *    SC_DB class
 *
 * @package sc
 * @author Karim Ahmed
 * @version 1.0
 * @copyright Sweetcode Ltd 2005
 * @public
 */
//require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'mysql.php');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'string.php');
//require_once(CONFIG_PATH_SC_CLASSES . 'binary.php');
require_once(CONFIG_PATH_SC_CLASSES . 'date.php');
/*
List of validation functions to call when validating field types
*/
/*
String
*/
define('SC_DB_STRING', 'SC_DB_STRING');
define('SC_DB_STRING_1', 'SC_DB_STRING_1');
define('SC_DB_STRING_5', 'SC_DB_STRING_5');
define('SC_DB_STRING_10', 'SC_DB_STRING_10');
define('SC_DB_STRING_20', 'SC_DB_STRING_20');
define('SC_DB_STRING_30', 'SC_DB_STRING_30');
define('SC_DB_STRING_50', 'SC_DB_STRING_50');
define('SC_DB_FILE_NAME', 'SC_DB_FILE_NAME');
define('SC_DB_STRING_100', 'SC_DB_STRING_100');
define('SC_DB_NAME', 'SC_DB_NAME');
define('SC_DB_SCRIPT_NAME', 'SC_DB_SCRIPT_NAME');
define('SC_DB_PHONE', 'SC_DB_PHONE');
define('SC_DB_EMAIL', 'SC_DB_EMAIL');
define('SC_DB_EMAIL_HEADERS', 'SC_DB_EMAIL_HEADERS');
define('SC_DB_EMAIL_BODY', 'SC_DB_EMAIL_BODY');
define('SC_DB_IP', 'SC_DB_IP');
define('SC_DB_PASSWORD', 'SC_DB_PASSWORD');
define('SC_DB_UNMASKED_PASSWORD',
    'SC_DB_UNMASKED_PASSWORD');
define('SC_DB_REPEAT_PASSWORD',
    'SC_DB_REPEAT_PASSWORD');
define('SC_DB_TIME_24', 'SC_DB_TIME_24');
define('SC_DB_POSTCODE', 'SC_DB_POSTCODE');
define('SC_DB_ADDRESS', 'SC_DB_ADDRESS');
define('SC_DB_TOWN', 'SC_DB_TOWN');
define('SC_DB_LONGTEXT', 'SC_DB_LONGTEXT');
define('SC_DB_PROCESS_STATUS',
    'SC_DB_PROCESS_STATUS');
define('SC_DB_METHOD_NAME', 'SC_DB_METHOD_NAME');
/*
Date
*/
define('SC_DB_MYSQL_DATE', 'SC_DB_MYSQL_DATE');
define('SC_DB_MYSQL_DATETIME',
    'SC_DB_MYSQL_DATETIME');
define('SC_DB_UK_DATE', 'SC_DB_UK_DATE');
/*
Numeric
*/
define('SC_DB_ID', 'SC_DB_ID');
define('SC_DB_BOOL', 'SC_DB_BOOL');
define('SC_DB_INTEGER', 'SC_DB_INTEGER');
define('SC_DB_INTEGER_5', 'SC_DB_INTEGER_5');
define('SC_DB_INTEGER_6', 'SC_DB_INTEGER_6');
define('SC_DB_DECIMAL_POS_5_2',
    'SC_DB_DECIMAL_POS_5_2');
define('SC_DB_DECIMAL',
    'SC_DB_DECIMAL');
/*
Binary
*/
define('SC_DB_BLOB', 'SC_DB_BLOB');
define('SC_DB_SMALL_GIF', 'SC_DB_SMALL_GIF');
/*
END OF FIELD TYPES
*/
/*
field validation error messages
*/
define('DB_MSG_FIELD_REQUIRED', 'Required field');
define('DB_MSG_DUPLICATE_VALUE', 'Already in the database');
define('SC_DB_MSG_UPDATED', 'Updated');
define('SC_DB_MSG_PROBLEMS_ON_FORM', 'Please correct the fields as indicated');
define('DB_MSG_NOT_A_VALID_OPTION', 'Not a valid selection');

// for entry forms
define('SC_DB_REQUIRED_FLAG', '*');

class SC_DB extends SC_Object
{
    var $row_before = array();
    var $row = array();
    var $fields = array();
    var $postedRow = array();
    var $statement = false;
    var $connection;
    var $errors = array();
    var $name;
    var $table_name;
    var $pk_name;
    var $display_help = false;

    var $types = array(
        SC_DB_STRING =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 255,
                'msg' => 'Enter a valid string',
                'is_numeric' => false
            ),
        SC_DB_SCRIPT_NAME =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => 5,
                'max' => 50,
                'msg' => 'Enter a valid PHP script name',
                'is_numeric' => false
            ),
        SC_DB_STRING_1 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 1,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_5 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 5,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_10 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                                    => false,
                'min' => false,
                'max' => 10,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_20 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 20,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_30 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 30,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_50 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 50,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_FILE_NAME =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 50,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_STRING_100 =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'max' => 100,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_METHOD_NAME =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => 2,
                'max' => 20,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_DECIMAL_POS_5_2 =>
            array(
                'validate_function' => 'SC_String:isDecimal',
                'format_function'
                => 'SC_String:formatDecimal2',
                'min' => 0,
                'max' => 999.99,
                'is_numeric' => true,
                'msg' => 'Number to 2dps'
            ),
        SC_DB_DECIMAL =>
            array(
                'validate_function' => 'SC_String:isDecimal',
                'format_function'
                => false,
                'min' => 0,
                'max' => 9999.99,
                'is_numeric' => true,
                'msg' => 'Number to 2dps'
            ),
        SC_DB_PROCESS_STATUS =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => false,
                'is_numeric' => false,
                'max' => 5,
                'msg' => false
            ),
        SC_DB_NAME =>
            array(
                'validate_function' => 'SC_String:isName',
                'format_function'
                => 'SC_String:ucWords',
                'min' => 2,
                'max' => 50,
                'is_numeric' => false,
                'msg' => false
            ),
        SC_DB_ID =>
            array(
                'validate_function' => 'SC_String:isInteger',
                'format_function'
                => false,
                'min' => 0,
                'max' => 32765,
                'is_numeric' => true,
                'msg' => 'Enter a valid ID'
            ),
        SC_DB_PHONE =>
            array(
                'validate_function' => 'SC_String:isPhone',
                'format_function'
                => 'SC_String:strToUpper',
                'min' => 3,
                'max' => 20,
                'is_numeric' => false,
                'msg' => 'Enter a valid phone no'
            ),
        SC_DB_EMAIL =>
            array(
                'validate_function' => 'SC_String:isEmail',
                'format_function'
                => 'SC_String:strToLower',
                'min' => 7,
                'is_numeric' => false,
                'max' => 50,
                'msg' => 'Enter a valid address'
            ),
        SC_DB_EMAIL_BODY =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'is_numeric' => false,
                'min' => 0,
                'max' => 10240000,
                'msg' => ''
            ),
        SC_DB_EMAIL_HEADERS =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'is_numeric' => false,
                'min' => 0,
                'max' => 2000,
                'msg' => ''
            ),
        SC_DB_IP =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => 8,
                'max' => 15,
                'is_numeric' => false,
                'msg' => ''
            ),
        SC_DB_UNMASKED_PASSWORD =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'min' => 6,
                'is_numeric' => false,
                'max' => 10,
                'msg' => 'Enter a valid password'
            ),
        SC_DB_PASSWORD =>
            array(
                'validate_function' => 'SC_String:isPassword',
                'format_function'
                => false,
                'min' => 6,
                'is_numeric' => false,
                'max' => 15,
                'msg' => 'Enter a valid password'
            ),
        SC_DB_REPEAT_PASSWORD =>
            array(
                'validate_function' => 'SC_String:isRepeatPassword',
                'format_function'
                => false,
                'is_numeric' => false,
                'min' => 6,
                'max' => 15,
                'msg' => 'Must match password'
            ),
        SC_DB_BOOL =>
            array(
                'validate_function' => 'SC_String:isBoolean',
                'format_function'
                => false,
                'min' => false,
                'max' => false,
                'is_numeric' => true,
                'msg' => 'Enter a valid boolean'
            ),
        SC_DB_INTEGER =>
            array(
                'validate_function' => 'SC_String:isInteger',
                'format_function'
                => false,
                'min' => 0,
                'max' => 10000000,
                'is_numeric' => true,
                'msg' => 'Enter a valid integer value'
            ),
        SC_DB_INTEGER_5 =>
            array(
                'validate_function' => 'SC_String:isInteger',
                'format_function'
                => false,
                'min' => 0,
                'is_numeric' => true,
                'max' => 5,
                'msg' => 'Enter a valid integer value'
            ),
        SC_DB_INTEGER_6 =>
            array(
                'validate_function' => 'SC_String:isInteger',
                'format_function'
                => false,
                'min' => 0,
                'is_numeric' => true,
                'max' => 6,
                'msg' => 'Enter a valid integer value'
            ),
        SC_DB_MYSQL_DATE =>
            array(
                'validate_function' => 'SC_Date:isMysqlDate',
                'format_function'
                => false,
                'min' => 10,
                'is_numeric' => false,
                'max' => 10,
                'msg' => 'Must be YYYY-MM-DD'
            ),
        SC_DB_TIME_24 =>
            array(
                'validate_function' => 'SC_Date:isTime24',
                'format_function'
                => false,
                'min' => false,
                'is_numeric' => false,
                'max' => false,
                'msg' => 'Enter HH:MM'
            ),
        SC_DB_MYSQL_DATETIME =>
            array(
                'validate_function' => 'SC_Date:isMysqlDateTime',
                'format_function'
                => false,
                'min' => false,
                'is_numeric' => false,
                'max' => false,
                'msg' => 'Must be YYYY-MM-DD H:M:S'
            ),
        SC_DB_UK_DATE =>
            array(
                'validate_function' => 'SC_Date:isUKDate',
                'format_function'
                => false,
                'min' => false,
                'is_numeric' => false,
                'max' => 12,
                'msg' => 'Must be a valid date in D/M/YYYY format'
            ),
        SC_DB_POSTCODE =>
            array(
                'validate_function' => 'SC_String:isPostcode',
                'format_function'
                => 'SC_String:strToUpper',
                'is_numeric' => false,
                'min' => 6,
                'max' => 8,
                'msg' => 'Enter a valid postcode'
            ),
        SC_DB_TOWN =>
            array(
                'validate_function' => 'SC_String:isTown',
                'format_function'
                => 'SC_String:strToUpper',
                'is_numeric' => false,
                'min' => 3,
                'max' => 30,
                'msg' => 'Enter a valid town'
            ),
        SC_DB_ADDRESS =>
            array(
                'validate_function' => 'SC_String:isAddress',
                'format_function'
                => 'SC_String:ucWords',
                'is_numeric' => false,
                'min' => 2,
                'max' => 50,
                'msg' => 'Enter a valid address line'
            ),
        SC_DB_SMALL_GIF =>
            array(
                'validate_function' => 'SC_String:isGif',
                'format_function'
                => false,
                'min' => 1024,
                'max' => 10240,
                'is_numeric' => false,
                'msg' => ''
            ),
        SC_DB_LONGTEXT =>
            array(
                'validate_function' => 'SC_String:isString',
                'format_function'
                => false,
                'is_numeric' => false,
                'min' => false,
                'max' => 10240000,
                'msg' => ''
            ),
        SC_DB_BLOB =>
            array(
                'validate_function' => 'SC_Binary:isBlob',
                'format_function'
                => false,
                'is_numeric' => false,
                'min' => false,
                'max' => 10240000,
                'msg' => ''
            ),
    );

    var $string_types =
        array(
            SC_DB_STRING,
            SC_DB_STRING_1,
            SC_DB_STRING_5,
            SC_DB_STRING_20,
            SC_DB_STRING_30,
            SC_DB_STRING_50,
            SC_DB_FILE_NAME,
            SC_DB_STRING_100,
            SC_DB_NAME,
            SC_DB_SCRIPT_NAME,
            SC_DB_PHONE,
            SC_DB_EMAIL,
            SC_DB_EMAIL_HEADERS,
            SC_DB_EMAIL_BODY,
            SC_DB_IP,
            SC_DB_PASSWORD,
            SC_DB_UNMASKED_PASSWORD,
            SC_DB_REPEAT_PASSWORD,
            SC_DB_TIME_24,
            SC_DB_POSTCODE,
            SC_DB_ADDRESS,
            SC_DB_TOWN,
            SC_DB_LONGTEXT,
            SC_DB_PROCESS_STATUS,
            SC_DB_METHOD_NAME,
            SC_DB_ID,
            SC_DB_BOOL
        );
    var $date_types =
        array(
            SC_DB_MYSQL_DATE,
            SC_DB_MYSQL_DATETIME,
            SC_DB_UK_DATE
        );
    var $numeric_types =
        array(
            SC_DB_INTEGER,
            SC_DB_INTEGER_5,
            SC_DB_INTEGER_6,
            SC_DB_DECIMAL_POS_5_2,
            SC_DB_DECIMAL
        );
    var $binary_types =
        array(
            SC_DB_BLOB,
            SC_DB_SMALL_GIF
        );

    /**
     * @access public
     */
    function SC_DB($table_name = false)
    {
        $this->__construct($table_name);
    }

    /**
     *
     * IMPORTANT: descendents MUST call this at the END of their __construct method!!!
     * @access public
     */
    function __construct($table_name = false)
    {
        if (!$table_name) {
            $this->raiseError('SC_DB::__construct: table_name not passed');
        }
        $this->setTableName($table_name);
        $this->addFields();                    // will call the overriden method on the descendant
        $this->setPKName($this->getTableName() . '.' . $this->getTableName() . '_id');
        $this->initialiseRow();
    }

    function addFields()
    {
        /*
        fields that all db classes must have. you should append to this array in your descendent classes
        __construct() function after a call to parent::__construct()
        $this->addField(
            $this->getTableName() . '.modified_by_user_id',
            array(
                'label' 		=> 'Modify ID',
                'help'			=> 'User who last modified this record',
                'type'			=> SC_DB_ID,
                'required'	=> false,
                'unique'		=> false,
                'default' 	=> '',
                'in_db' 		=> true,
                'is_select'	=> false,
                'can_edit'	=> false
            )
        );
        $this->addField(
            $this->getTableName() . '.modified_date',
            array(
                'label' 		=> 'Modified',
                'help'			=> 'Date this record was last modified',
                'type'			=> SC_DB_MYSQL_DATETIME,
                'required'	=> false,
                'unique'		=> false,
                'default' 	=> '',
                'in_db' 		=> true,
                'is_select'	=> false,
                'can_edit'	=> false
            )
        );
        $this->addField(
            $this->getTableName() . '.created_by_user_id',
            array(
                'label' 		=> 'Create ID',
                'help'			=> 'User who created this record',
                'type'			=> SC_DB_ID,
                'required'	=> false,
                'unique'		=> false,
                'default' 	=> '',
                'is_select'	=> false,
                'in_db' 		=> true,
                'can_edit'	=> false
            )
        );
        $this->addField(
            $this->getTableName() . '.created_date',
            array(
                'label' 		=> 'Created',
                'help'			=> 'Date this record was created',
                'type'			=> SC_DB_MYSQL_DATETIME,
                'required'	=> false,
                'unique'		=> false,
                'default' 	=> '',
                'in_db' 		=> true,
                'is_select'	=> false,
                'can_edit'	=> false
            )
        );
        */
    }

    function setPKName($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            $this->pk_name = $field;
        }
    }

    function getTableFieldname($field)
    {
        return $this->getTableName() . '.' . $field;
    }

    /*
    * isDisplayingHelp
    *
    * Are we displaying help tool tips?
    */
    function isDisplayingHelp()
    {
        return $this->display_help;
    }

    function setDisplayName($name)
    {
        $this->name = $name;
    }

    function getDisplayName()
    {
        return $this->name;
    }

    function setTableName($name)
    {
        $this->table_name = $name;
    }

    /**
     * addField
     *
     * Add to the assoc array of field attributes
     *
     * @param array $attributes array of attributes
     */
    function addField($field, $attributes)
    {
        $field = trim($field);
        $this->fields[$field] =
            array(
                'label' => $attributes['label'],
                'help' => $attributes['help'],
                'type' => $attributes['type'],
                'required' => $attributes['required'],
                'unique' => $attributes['unique'],
                'default' => $attributes['default'],
                'can_edit' => $attributes['can_edit'],
                'is_select'
                => isset($attributes['is_select']) ? $attributes['is_select'] : false,
                'in_db' => $attributes['in_db'],
                'select_statement'
                => isset($attributes['select_statement']) ? $attributes['select_statement'] : false,
                'select_fields'
                => isset($attributes['select_fields']) ? $attributes['select_fields'] : false,
                'validate_function'
                => isset($attributes['validate_function']) ? $attributes['validate_function'] : false,
                'is_dropdown'
                => isset($attributes['is_dropdown']) ? $attributes['is_dropdown'] : false,
                'calculation'
                => isset($attributes['calculation']) ? $attributes['calculation'] : false,
                'msg'
                => isset($attributes['msg']) ? $attributes['msg'] : false,
                'align' => isset($attributes['align']) ? $attributes['align'] : false
            );
    }

    /*
    *	formFocusField
    *
    * Determine which form field should have focus when the page loads
    */
    function getFormFocusField()
    {
        if (
            $this->getFirstErrorField() &&
            $this->fieldCanHaveFocus($this->getFirstErrorField())
        ) {
            $ret = $this->getFirstErrorField();
        } else {
            $ret = $this->getFirstFieldThatCanHaveFocus();
        }
        return $ret;
    }

    function getFirstErrorField()
    {
        $first_error_field = reset($this->errors);
        if ($first_error_field) {
            return key($this->errors);
        } else {
            return false;                    // default to first element on form
        }
    }

    /*
    *	fieldCanHaveFocus
    */
    function fieldCanHaveFocus($field)
    {
        $field = trim($field);
        if (!$this->isDropdown($field) && $this->canEdit($field)) {
            return true;
        } else {
            return false;
        }
    }

    /*
    *	getFirstFieldThatCanHaveFocus
    */
    function getFirstFieldThatCanHaveFocus()
    {
        $ret = false;
        foreach ($this->fields as $field => $value) {
            if ($this->fieldCanHaveFocus($field)) {
                $ret = $field;
                break;
            }
        }
        return $ret;
    }

    /*
    Initialise the row with default values from $this->fields[]['default] array
    Note that any row values that have already been set will remain so that you can
    initialise them with your own, passed in, values. e.g. foreign key values or status
    flags.
    */
    function initialiseRow()
    {
        foreach ($this->fields as $field => $value) {
            if (!@array_key_exists($field, $this->row)) {    // in case any have been set already
                $this->row[$field] = $this->fields[$field]['default'];
            }
        }
    }

    /**
     *    update()
     * Store current row to the database
     *
     * @access virtual
     */
    function update()
    {
//		global $authenticate;
        $ret = false;

//		if ( !isset($authenticate) || !is_object($authenticate) ){
//			$this->raiseError( 'update(): $authenticate object not set' );
//		}
//		$modified_by_user_id = $authenticate->user->getUserID();
        if ($this->getPKValue() == null) {
            return $this->insert();
        }

        $this->fixCheckboxes();
        if ($this->validateDBFields()) {
//			$this->setValue( $this->getTableName() . '.modified_date', date('Y-m-d H:i:s') );
//			$this->setValue( $this->getTableName() . '.modified_by_user_id', $modified_by_user_id );

            $statement =
                'UPDATE' . CR .
                TAB . $this->table_name . CR .
                $this->getSQLFromSection() . CR .
                TAB . TAB . 'SET' . CR .
                $this->dbFieldNamesForInsert() . CR .
                'WHERE' . CR .
                TAB . $this->getPKName() . ' =~' . $this->getDBFieldCount() . CR .
                TAB . $this->getSQLWhereConstraint();

            $this->checkConnection();

            $this->statement = $this->connection->prepare($statement);

//			if(
            $this->statement->execute($this->getDBFieldValuesForUpdate());
//				$log_update
//			){
//				$this->logUpdate( 'U', $this->row_before );
//			}

            $ret = true;
        }
        return $ret;
    }

    /**
     *    update()
     *
     * @access virtual
     */
    function insert()
    {
        $ret = false;
        $this->fixCheckboxes();
        if ($this->validateDBFields()) {
//			$this->setValue( $this->getTableName() . '.created_date', date('Y-m-d H:i:s') );
//			$this->setValue( $this->getTableName() . '.created_by_user_id', $created_by_user_id );
//			$this->setValue( $this->getTableName() . '.modified_date', date('Y-m-d H:i:s') );
//			$this->setValue( $this->getTableName() . '.modified_by_user_id', $created_by_user_id );

            $this->checkConnection();

            $query_string =
                'INSERT INTO' . CR .
                TAB . $this->table_name . CR .
                'SET' . CR .
                $this->dbFieldNamesForInsert();

            $this->statement =
                $this->connection->prepare($query_string);

//			if(
            $this->statement->execute($this->getDBFieldValuesForInsert()) &&
            $this->setPKValue($this->connection->lastID()) &&
//				$log_update
//			){
//				$this->logUpdate( 'I' );
//			}	

            $ret = true;
        }
        return $ret;
    }

    /**
     * validateDBFields
     *
     * Check types and required of database fields and builds an
     * array of error messages for display to the user.
     *
     * @return bool success
     */
    function validateDBFields()
    {
        return $this->validateFields(true);
    }

    /**
     * validateFields
     *
     * Check types and required of all fields and builds an
     * array of error messages for display to the user.
     *
     * @return bool success
     */
    function validateFields($mustBeInDB = false)
    {

        $this->errors = array();

        foreach ($this->row as $field => $value) {

            /*
            validate this field if we NOT only including database fields
            OR: we are only including them AND this field is in the DB
            */
            if (!$mustBeInDB || ($mustBeInDB && $this->isInDB($field))) {

                if ($this->getType($field) == SC_DB_REPEAT_PASSWORD) {
                    $value = $value . ':' . $this->getValue('password');
                }

                $error_message = $this->validateField(
                    $field,
                    $value
                );

                if ($error_message != '') {
                    $this->errors[$field] = $error_message;
                }

            } //	if $mustBeInDB ||		( $mustBeInDB && $this->isInDB($field) )

        }    // end foreach

        if ($this->areErrors()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * validateField
     *
     * Check types and required of one field
     * @param string field
     * @return string error message
     */
    function validateField($field, &$value)
    {
        $field = trim($field);
        /*
        1st of all, pretty things up a bit
        Now, this IS clever because we use the AJAX in validate_field.php to dynamically
        set the pretty value as the user moves through the form ;-)
        */
        if ($format_function = $this->getFormatFunction($field)) {
            $value =
                call_user_func(
                    array(
                        SC_String::reverseStrrchr($format_function, ':'),
                        SC_String::strrchr($format_function, ':')
                    ),
                    $value
                );
            $this->row[$field] = $value;
        }
        // check for required except bool
        if ($this->getType($field) != SC_DB_BOOL) {
            if (($value == '' OR $value == null)) {
                if ($this->isRequired($field)) {
                    return SC_String::display(DB_MSG_FIELD_REQUIRED, SC_STRING_FMT_UPPERCASE_FIRST_LETTER);
                } else {
                    return '';
                }
            }
        }
        $callback = $this->getValidateFunction($field);
        $min = $this->getMinLength($field);
        $max = $this->getMaxLength($field);

        $class = SC_String::reverseStrrchr($callback, ':');
        $method = SC_String::strrchr($callback, ':');

        if ($class == 'this') {
            $callback_array =
                array(
                    &$this,
                    SC_String::strrchr($callback, ':')
                );
        } else {
            $callback_array =
                array(
                    SC_String::reverseStrrchr($callback, ':'),
                    SC_String::strrchr($callback, ':')
                );
        }

        $got_error =
            !call_user_func(
                $callback_array,
                $value,
                $min,
                $max
            );

        if ($this->getType($field) == SC_DB_REPEAT_PASSWORD) {
            $value = SC_String::reverseStrrchr($value, ':');
        }

        if ($got_error) {
            $msg = $this->getValidateErrorMessage($field);
            return $msg;
        }
        /*
        See whether any field value has to be within a list (e.g. referential integrity)
        */
        if ($this->getSelectStatement($field) && !$this->isUnique($field)) {
            if (!$this->isInOptions($field, $value)) {
                return DB_MSG_NOT_A_VALID_OPTION;
            } else {
                return '';
            }
        }
        /*
        See whether any restrictions exist for allowed field values
        */
        if ($this->isUnique($field)) {
            if ($this->getSelectStatement($field)) {                        // we have a special statement defined for this field
                $query_string = $this->getSelectStatement($field);

                if ($this->row[$this->getPKName()] != '') {
                    $query_string .=
                        "\r\nAND\r\n\t" . $this->getPKName() . " <> '" . mysqli_real_escape_string($this->connection, $this->getPKValue()) . "'";
                }

                $select_fields_array = $this->getSelectFieldsAsArray($field);
            } else {                                                                                                // build the default duplicate test statement
                $query_string =
                    "SELECT\r\n\tCOUNT(*) as duplicates" .
                    "\r\nFROM\r\n\t" . $this->table_name .
                    "\r\nWHERE\r\n\tLOWER(" . $field . ") = " .
                    "'" . mysqli_real_escape_string($this->connection, strtolower($value)) . "'";
                /* if we are updating then exclude ourselves! */
                if ($this->row[$this->getPKName()] != '') {
                    $query_string .=
                        "\r\nAND\r\n\t" . $this->getPKName() . " <> '" . mysqli_real_escape_string($this->connection, $this->getPKValue()) . "'";
                }
                $select_fields_array = false;
            }
            /*
            Run the test
            */
            $this->checkConnection();

            $unique_statement = $this->connection->prepare($query_string);
            if ($unique_statement->execute($select_fields_array)) {                                        // blank for default
                $duplicateRow = $unique_statement->fetchAssoc();
                if ($duplicateRow['duplicates'] > 0) {
                    return DB_MSG_DUPLICATE_VALUE;
                } else {
                    return '';
                }
            } else {
                $this->raiseError('Failed execute');
            }
        } // end if ( $this->isUnique($field)  )
    }

    function updateRowFrom(&$posted_row)
    {

        $ret = false;
        $this->postedRow = &$posted_row;

        $modified_date = $this->getTableName() . '.modified_date';
        $modified_by_user_id = $this->getTableName() . '.modified_by_user_id';

        // has the row been changed by someone else?
        if (
            array_key_exists($modified_date, $this->postedRow) &&
            $this->postedRow[$modified_date] != '' &&
            $this->fieldExists($modified_date) &&
            $this->getValue($modified_date) != '' &&
            trim($this->getValue($modified_date)) != trim($this->postedRow[$modified_date])
        ) {
            /*
            Attempt to notify the other user by email that we have tried to change this record
            */
            $authenticate = &$GLOBALS['authenticate'];
            $authenticate->user->getRow($this->getValue($modified_by_user_id));

            $send_to_email = $authenticate->user->getUsername();
            $send_from_email = $authenticate->getUsername();

            $body =
                'I tried to update the record below and the system warned me there are conflicts with changes you just made.' . CR . CR .
                'http://' . $_SERVER['SERVER_NAME'] . '/' . basename($_SERVER['SCRIPT_FILENAME']) . '?' . $this->getPKName() . '=' . $this->getPKValue() . CR . CR .
                'I will decide whether or not I still need to change it but please let me know ASAP if you need to discuss.' . CR . CR . 'Thanks, ' . $send_to_email;

            require_once(CONFIG_PATH_SC_CLASSES . 'mail.php');
            $mail = new SC_Mail();

            $mail->setTo($send_to_email);
            $mail->setFrom($send_from_email);
            $mail->setSubject($_SERVER['SERVER_NAME'] . ' record change request');
            $mail->addBCC($send_to_email);

            $email_was_sent = $mail->send();
            /*
            End email sending
            */
            $ret =
                'The record was changed by ' . $send_from_email .
                ' whilst you were in the middle of editing your page so your values haven\'t been written to the database yet. Review your changes and resinstate where necessary.';

            if ($email_was_sent) {
                $ret .=
                    CR . CR .
                    'An email has been sent to them to let them know you have tried to make the change(s).';
            }
            // build a list of fields that have changed
            // all the ignore_repsonse stuff prevents the ajax validation from screwing us
            foreach ($this->row as $field => $value) {
                if (array_key_exists($field, $this->postedRow) && strtolower($value) != strtolower($this->postedRow[$field])) {
                    if ($this->getType($field) == SC_DB_BOOL) {
                        $field_value = SC_HTML::checkedValue($this->postedRow[$field]);
                        $reinstate_message = SC_HTML::checkedValue($this->postedRow[$field]) == '' ? 'unchecked' : 'checked';
                    } else {
                        $field_value = $this->postedRow[$field];
                        $reinstate_message = $field_value;
                    }
                    $this->errors[$field] = 'Changed (';
                    $this->errors[$field] .=
                        '<a href="#" onClick="' .
                        'ignoreXMLHTTPRequestResponse();' .            // so any triggered ajax validation is bypassed
                        'if(!confirm(\'Has ' . $send_to_email . ' approved the reinstatement to ' . $reinstate_message . '?\' ) ) return(false);' .
                        // resinstate the value
                        'im=document.getElementById(\'' .
                        $field .
                        '\');';
                    if ($this->getType($field) == SC_DB_BOOL) {
                        $this->errors[$field] .= 'im.checked = \'' . $field_value . '\';';
                    } else {
                        $this->errors[$field] .= 'im.value = \'' . $field_value . '\';';
                    }
                    $this->errors[$field] .= 'im.style.backgroundColor = \'#FFFFFF\';';
                    // clear the error
                    $this->errors[$field] .=
                        'im=document.getElementById(\'' .
                        $field . '_error' .
                        '\');' .
                        'im.innerHTML = \'\';"' .
                        '>reinstate your "' . $reinstate_message . '"</A>);';
                }
            } // foreach
        } else {
            /* have we changed the row? */
            $we_have_changed_row = false;
            foreach ($this->row as $field => $value) {
                if (
                    array_key_exists($field, $this->postedRow) &&
                    $value != $this->postedRow[$field]
                ) {
                    $we_have_changed_row = true;
                }
            }
            if (!$we_have_changed_row) {
                $ret = 'You haven\'t changed anything!';
            } else {
                // update our business object
                foreach ($this->postedRow as $field => $value) {
                    if ($this->fieldExists($field)) {
                        $this->setValue($field, $value);
                    }
                }
            }
        }
        return $ret;
    }

    function dbFieldNames($include_select_names = false)
    {
        $ret = '';
        foreach ($this->fields as $field => $attributes) {

            if ($attributes['in_db'] | ($include_select_names && $this->isSelect($field))) {
                if ($ret != '') {
                    $ret .= ",\r\n\t";
                } else {
                    $ret .= "\r\n\t";
                }
                if ($this->getCalculation($field)) {
                    $first_part = $this->getCalculation($field);
                } else {
                    $first_part = $field;
                }

                if ($this->getType($field) == SC_DB_UK_DATE) {
                    $ret .= 'DATE_FORMAT(' . $first_part . ',\'%d/%m/%Y\') as \'' . $field . '\'';
                } else {
                    $ret .= $first_part . ' as \'' . $field . '\'';
                }
            }
        }
        return $ret;
    }

    function getDBSelectFieldNames()
    {
        return $this->dbFieldNames(true);
    }

    function dbFieldNamesForInsert()
    {
        $ix = 0;
        $ret = '';
        $pk_name = $this->getPKName();

        foreach ($this->fields as $field => $attributes) {
            if (
                $field != $pk_name &&            // only if not the PK and in database and not
                $attributes['in_db'] &&    // just a select field
                !$this->isSelect($field)
            ) {
                $ix++;                                        // our statement marker
                if ($ret != '') {
                    $ret .= ",\r\n\t";
                } else {
                    $ret .= "\r\n\t";
                }
                $ret .= $field . " =\t\t\t~" . $ix;
            }
        }
        return $ret;
    }

    function getDBFieldValuesForInsert()
    {

        $pk_name = $this->getPKName();
        $ret = array();

        foreach ($this->fields as $field => $attributes) {
            if (
                $field != $pk_name &&            // only if not the PK and in database
                $attributes['in_db'] &&    // just a select field
                !$this->isSelect($field)
            ) {
                // turn UK date into mySQL date for database
                if ($this->getType($field) == SC_DB_UK_DATE) {
                    $ret[] = SC_Date::dateFormat($this->getValue($field), SC_DB_MYSQL_DATETIME);
                } else {
                    $ret[] = $this->getValue($field);
                }
            }
        }
        return $ret;
    }

    function getDBFieldValuesForUpdate()
    {
        $ret = $this->getDBFieldValuesForInsert();
        $ret[] = $this->getPKValue();
        return $ret;
    }

    function areErrors()
    {
        return (count($this->errors) > 0);
    }

    function fieldExists($field)
    {
        $field = trim($field);
        return array_key_exists($field, $this->fields);
    }

    function valueExists($field)
    {
        $field = trim($field);
        return array_key_exists($field, $this->row);
    }

    /*
    * error
    *
    * Returns the error text of the given array element
    */
    function getErrorText($field)
    {
        $field = trim($field);
        if (array_key_exists($field, $this->errors)) return $this->errors[$field];
    }

    function requiredFlag($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->isRequired($field) ? SC_DB_REQUIRED_FLAG : '';
        }
    }

    function getLabel($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['label'];
        }
    }

    function getValue($field)
    {
        $field = trim($field);
        $ret = $field . ' NOT DEFINED';
        if ($this->fieldExists($field)) {
            $value = $this->row[$field];
            // booleans need special treatment in case they are null or empty
            if ($this->getType($field) == SC_DB_BOOL) {
                if ($value == '' OR $value == null) {
                    $ret = 0;
                } else {
                    $ret = $value;
                }
            } else {
                // everything else should return null if it is empty and not a number
                $ret = ($value == '' & !is_numeric($value)) ? null : $value;
            }
            return $ret;
        }
        return $ret;
    }

    function getPKValue()
    {
        return $this->getValue($this->getPKName());
    }

    function getPKName()
    {
        return $this->pk_name;
    }

    function getTableName()
    {
        return $this->table_name;
    }

    function setPKValue($value)
    {
        $this->row[$this->getPKName()] = $value;
    }

    function getType($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['type'];
        }
    }

    function getAlign($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['align'];
        } else {
            return false;
        }
    }

    /**
     * clever way of using user function calls to format values for us
     */
    function getFormatFunction($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->types[$this->fields[$field]['type']]['format_function'];
        }
    }

    function getHelpText($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field) && $this->hasHelp($field)) {
            return $this->fields[$field]['help'];
        } else {
            return false;
        }
    }

    function hasHelp($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['help'] == '' ? false : true;
        }
    }

    function isInDB($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['in_db'];
        }
    }

    function canEdit($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['can_edit'];
        }
    }

    function isNumeric($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->types[$this->fields[$field]['type']]['is_numeric'];
        }
    }

    function isSelect($field)
    {
        $field = trim($field);
        if ($this->fieldExists($field)) {
            return $this->fields[$field]['is_select'];
        }
    }

    /*
    * fixCheckboxes
    *
    * Set checkbox boolean values to false if not passed in from form
    */
    function fixCheckboxes()
    {
        foreach ($this->fields as $field => $attributes) {
            $this->row[$field] = ($attributes['type'] == SC_DB_BOOL & !array_key_exists($field, $this->postedRow)) ? 0 : $this->row[$field];
        }
    }

    function getMaxLength($field)
    {
        $field = trim($field);
        $ret = $this->types[$this->fields[$field]['type']]['max'];
        return $ret;
    }

    /*
    * This gives us an approximate width for rendering fields
    * It is impossible to get it exactly right because we generally use proportionate
    * fonts on the front-end!
    */
    function getWidth($field, $icon_width = 30)
    {

        $field = trim($field);

        if ($this->isNumeric($field)) {
            //		if ( $this->isNumeric($field)){

            $ret = 6;

//      }
//			else{
//				if ( $this->getMaxLength($field) > 50 ){
//					$ret = 20;
//				}
//				else{
//					$ret = strlen($this->getMaxLength($field)) + $icon_width;				// 10 for the order_by icon
//				}
//			}
        } else {
            $ret = $this->getMaxLength($field) + $icon_width;
        }
        return $ret - 5;
    }

    function getCombinedWidth($fields = false)
    {
        if (!$fields) {
            $fields = array();
        }

        $width = 0;

        foreach ($this->fields as $field => $attributes) {
            if (in_array($field, $fields)) {
                $width += $this->getWidth($field);
            }
        }
        return $width;
    }

    function getPercentageWidth($field, $fields = false, $additional_width = 0)
    {
        if (is_integer($field)) {
            $my_width = $field;
        } else {
            $my_width = $this->getWidth($field);
        }
        $total_width = $this->getCombinedWidth($fields) + $additional_width;
        $ret = ($my_width / $total_width) * 100;
        return $ret;
    }

    function getMinLength($field)
    {
        $field = trim($field);
        return $this->types[$this->fields[$field]['type']]['min'];
    }

    function isRequired($field)
    {
        $field = trim($field);
        return $this->fields[$field]['required'];
    }

    function isUnique($field)
    {
        $field = trim($field);
        return $this->fields[$field]['unique'];
    }

    function isDropdown($field)
    {
        $field = trim($field);
        return $this->fields[$field]['is_dropdown'];
    }

    function setDropdown($field, $state = true)
    {
        $field = trim($field);
        $this->fields[$field]['is_dropdown'] = $state;
    }

    function getSelectFieldsAsArray($field)
    {
        $field = trim($field);
        $field_array = explode(',', $this->getSelectFields($field));
        foreach ($field_array as $index => $field) {
            $value_array[] = $this->getValue($field);
        }
        return $value_array;
    }

    function getSelectOptions($field)
    {
        $field = trim($field);
        $select_fields = $this->getSelectFields($field);
        $this->checkConnection();
        $my_statement = $this->connection->prepare($this->getSelectStatement($field));

        if ($select_fields == '') {
            $my_statement->execute();
        } else {
            $my_statement->execute($this->getSelectFieldsAsArray($field));
        }

        return $my_statement->fetchAllAssoc();
    }

    function getSelectStatement($field)
    {
        $field = trim($field);
        if (array_key_exists('select_statement', $this->fields[$field])) {
            return $this->fields[$field]['select_statement'];
        }
    }

    function getCalculation($field)
    {
        $field = trim($field);
        if (array_key_exists('calculation', $this->fields[$field])) {
            return $this->fields[$field]['calculation'];
        }
    }

    function getSelectFields($field)
    {
        $field = trim($field);
        if (array_key_exists('select_fields', $this->fields[$field])) {
            return $this->fields[$field]['select_fields'];
        }
    }

    function isInOptions($field, $value)
    {
        $field = trim($field);
        $options = $this->getSelectOptions($field);
        $found = false;
        foreach ($options as $index => $row) {
            if ($value = $row['value']) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    function isDateType($field)
    {
        $type = $this->getType($field);
        return in_array($type, $this->date_types);
    }

    function isStringType($field)
    {
        $type = $this->getType($field);
        return in_array($type, $this->string_types);
    }

    function isNumericType($field)
    {
        $type = $this->getType($field);
        return in_array($type, $this->numeric_types);
    }

    function getRow($pk_id = false)
    {
        $ret = false;
        if ($pk_id) {

            $this->checkConnection();

            $this->statement =
                $this->connection->prepare(
                    'SELECT' . CR .
                    TAB . $this->getDBSelectFieldNames() . CR .
                    'FROM' . CR .
                    TAB . $this->getSQLFromSection() . CR .
                    'WHERE' . CR .
                    TAB . $this->getPKName() . ' = ~1' . CR .
                    TAB . $this->getSQLWhereConstraint()
                );
            if (
                $this->statement->execute($pk_id) &&
                $this_row = $this->statement->fetchAssoc()
            ) {
                $this->updateRowFrom($this_row);
                $this->row_before = $this->row;
                $ret = true;
            }
        } else {
            $this->raiseError('SC_DB::getRow: pk_id not passed in');
        }
        $this->statement = false; // clear down

        return $ret;        // success status
    }


    /**
     * get all rows
     */
    function getAllRows(
        $start_row = 0,
        $order_by = false,
        $filters = false,
        $where_statement = false,
        $row_count = false
    )
    {

        $this->checkConnection();

        if (!$row_count) {
            $row_count = (int)CONFIG_ROWS_PER_PAGE;
        }

        $statement =
            'SELECT' . CR .
            TAB . $this->getDBSelectFieldNames() . CR .
            'FROM' . CR .
            TAB . $this->getSQLFromSection() . CR .
            'WHERE 1=1' . CR .
            TAB . $this->getSQLWhereConstraint();

        if ($where_statement) {
            $statement .= ' AND ' . $where_statement;
        }

        // filters?
        if ($filters) {
            foreach ($this->fields as $field => $attributes) {
                if (
                    array_key_exists($field, $filters) &&
                    trim($filters[$field]) != '' &&
                    !(
                        $this->getType($field) == SC_DB_BOOL &&
                        trim($filters[$field]) == 'all'
                    )
                ) {
                    $value = trim($filters[$field]);

                    if ($value == 'null') {
                        $statement .= ' AND ' . $field . ' IS NULL ';
                    } else {

                        $statement .= ' AND lower(' . $field . ')';

                        $value = strtolower($value);

                        $first_char = $value[0];


                        switch ($first_char) {
                            case '>':
                                $value = str_replace('>', '', $value);
                                if (strlen($value) > 0) {
                                    if ($this->isNumeric($field) || strpos(strtolower($value), 'date') !== false) {
                                        $statement .= ' >' . $value;
                                    } else {
                                        $statement .= ' >"' . $value . '"';
                                    }
                                }
                                break;

                            case '<':
                                $value = str_replace('<', '', $value);
                                if (strlen($value) > 0) {
                                    if ($this->isNumeric($field) || strpos(strtolower($value), 'date') !== false) {
                                        $statement .= ' <' . $value;
                                    } else {
                                        $statement .= ' <"' . $value . '"';
                                    }
                                }
                                break;

                            case '=':
                                $value = str_replace('=', '', $value);
                                if (strlen($value) > 0) {
                                    if ($this->isNumeric($field) || strpos(strtolower($value), 'date') !== false) {
                                        $statement .= ' =' . $value;
                                    } else {
                                        $statement .= ' ="' . $value . '"';
                                    }
                                }
                                break;

                            default:
                                /*
                                If there are commas in the value then match to each value
                                */
                                if (strpos($value, ',') !== false) {
                                    $pieces = explode(',', $value);
                                    $first = true;
                                    foreach ($pieces as $piece) {

                                        if (!$first) {
                                            $statement .= ' OR ' . $field;
                                        }
                                        $statement .= ' LIKE "%' . $piece . '%"';

                                        $first = false;
                                    }
                                } else {
                                    $statement .= ' LIKE "%' . $value . '%"';
                                }

                                break;
                        } // end switch
                    }
                }
            }
        }
        /*
        Build the ORDER BY
        */
        $statement .= CR . 'ORDER BY ';

        if (!$this->fieldExists(str_replace(' DESC', '', $order_by))) {
            $order_by = $this->getPKName() . ' DESC';
        }

        /*
        always put inactive rows at end of results
        */
        if (
            $this->fieldExists($this->getTableFieldname('active')) &&
            $order_by != $this->getTableFieldname('active')
        ) {
            $statement .= $this->getTableFieldname('active') . ' DESC, ';
        }

        $statement .= $order_by;

        $statement .= CR . 'LIMIT ' . (int)$start_row . ',' . $row_count;
        $this->statement = $this->connection->prepare($statement);
        $this->statement->execute();

        return $this->statement;
    }

    function getSQLFromSection()
    {
        return $this->getTableName();
    }

    /**
     * Override this in descendent to, for example, ensure users can only see there own details
     */
    function getSQLWhereConstraint()
    {
        return false;
    }

    function setValue($field, $value = null)
    {
        if ($this->fieldExists($field)) {
            $this->row[$field] = $value;
        }
    }

    function getPKValueIsNull()
    {
        return (is_null($this->row[$this->getPKName()]) OR $this->row[$this->getPKName()] == '');
    }

    function getDBFieldCount()
    {
        $ret = 0;
        foreach ($this->fields as $field => $attributes) {
            if ($this->isInDB($field) & !$this->isSelect($field)) {
                $ret++;
            }
        }
        return $ret;
    }

    function checkConnection()
    {
        if (!isset($GLOBALS['connection'])) {
            require_once(CONFIG_PATH_SC_CLASSES . 'mysql.php');
            $this->connection = new SC_MysqlConnection(
                CONFIG_DB_USERNAME,
                CONFIG_DB_PASSWORD,
                CONFIG_DB_HOST,
                CONFIG_DB_NAME
            );
            $GLOBALS['connection'] = $this->connection;
            $this->connection->connect();
        }
        if (!$this->connection) {
            $this->connection = $GLOBALS['connection'];
        }
    }

    function getValidateFunction($field)
    {
        $field = trim($field);
        if ($this->fields[$field]['validate_function']) {            // does this field have it's
            return $this->fields[$field]['validate_function'];        // own validate function?
        } else {
            return $this->types[$this->fields[$field]['type']]['validate_function'];
        }
    }

    function getValidateErrorMessage($field)
    {
        $field = trim($field);
        if ($this->fields[$field]['msg']) {            // does this field have it's
            $msg = $this->fields[$field]['msg'];        // own message
        } else {                        // use the standard data type error
            $msg = $this->types[$this->fields[$field]['type']]['msg'];
            $min = $this->getMinLength($field);
            $max = $this->getMaxLength($field);
            if ($min) {
                $msg .= ' Min ' . $min;
            }
            if ($max) {
                $msg .= ' Max ' . $max;
            }
        }
        return $msg;
    }

    /*
    * to override default type message
    */
    function setValidateErrorMessage($field, $msg)
    {
        $field = trim($field);
        return $this->fields[$field]['msg'] = $msg;
    }

    /*
    * override in descendent to alter return
    */
    function canEditRecord()
    {
        return true;
    }

    /**
     * count rows by field
     */
    function countRowsByField($field, $value = false)
    {
        if (!$this->fieldExists($field)) {
            $this->raiseError('countRowsByField: field ' . $field . ' does not exist');
        }

        $this->checkConnection();

        $statement =
            $this->connection->prepare(
                'SELECT' . CR .
                TAB . 'COUNT(*) AS \'row_count\'' . CR .
                'FROM' . CR .
                TAB . $this->getSQLFromSection() . CR .
                'WHERE ' . $field . '= ~1' . CR .
                TAB . $this->getSQLWhereConstraint()
            );

        if ($statement->execute($value)) {
            $row = $statement->fetchAssoc();
            return $row['row_count'];
        } else {
            $this->raiseError('Failed execute');
        }

    }

    /**
     * count rows by where clause
     */
    function countRowsByWhereClause($where_clause = false)
    {
        if (!$where_clause) {
            $this->raiseError('countRowsByWhereClause: $where_clause not passed');
        }

        $this->checkConnection();

        $statement =
            $this->connection->prepare(
                'SELECT' . CR .
                TAB . 'COUNT(*) AS \'row_count\'' . CR .
                'FROM' . CR .
                TAB . $this->getSQLFromSection() . CR .
                'WHERE ' . $where_clause . CR .
                TAB . $this->getSQLWhereConstraint()
            );

        if ($statement->execute($value)) {
            $row = $statement->fetchAssoc();
            return $row['row_count'];
        } else {
            $this->raiseError('Failed execute');
        }

    }

    /**
     * get all rows
     *
     * Possible attaributes:
     *
     * var    $start_row                = false;
     * var    $row_count                = false;
     * var    $order_by                    = false;
     * var    $filters                    = false;
     * var $where_statement    = false;
     */
    function getRows($parameters = false, $return_array = false)
    {

        // used by getRows() method
        $start_row = 0;
        $row_count = false;
        $order_by = false;
        $filters = false;
        $where_statement = false;

        if (!$parameters) {
            $parameters = array();
        }

        // set the params
        if (count($parameters) > 0) {
            foreach ($parameters as $var => $value) {
                if (isset($var)) {
                    $$var = $value;
                } else {
                    $this->raiseError(
                        'getRows: variable "' . $var . '" not in method scope'
                    );
                }
            }
        }

        $this->checkConnection();

        if (!$row_count) {
            $row_count = (int)CONFIG_ROWS_PER_PAGE;
        }

        $statement =
            'SELECT SQL_CALC_FOUND_ROWS ' . CR .
            TAB . $this->getDBSelectFieldNames() . CR .
            'FROM' . CR .
            TAB . $this->getSQLFromSection() . CR .
            'WHERE 1=1' . CR .
            TAB . $this->getSQLWhereConstraint();

        if ($where_statement) {
            $statement .= ' AND ' . $where_statement;
        }

        // filters?
        if ($filters) {
            foreach ($this->fields as $field => $attributes) {
                if (
                    array_key_exists($field, $filters) &&
                    trim($filters[$field]) != '' &&
                    !(
                        $this->getType($field) == SC_DB_BOOL &&
                        trim($filters[$field]) == 'all'
                    )
                ) {
                    $value = trim($filters[$field]);

                    if ($value == 'null') {
                        $statement .= ' AND ' . $field . ' IS NULL ';
                    } else {

                        /*
                        If there are commas in the value then match to each value
                        */
                        if (strpos($value, ',') !== false) {
                            $pieces = explode(',', $value);
                            $first = true;

                            //$statement .= ' AND lower(' . $field . ')';

                            foreach ($pieces as $piece) {

                                if (!$first) {
                                    $statement .= ' OR ' . $field;
                                } else {
                                    $statement .= ' AND (' . $field;
                                }

                                $statement .= ' LIKE "%' . $piece . '%"';

                                $first = false;
                            }
                            $statement .= ')';
                        } else {

                            $statement .= ' AND lower(' . $field . ')';

                            $value = strtolower($value);

                            $first_char = $value[0];

                            // Look for > < = symbol at start of value
                            if (
                                (
                                    $first_char == '>' ||
                                    $first_char == '<' ||
                                    $first_char == '='
                                ) &&
                                strlen($value) > 1
                            ) {
                                $value = str_replace($first_char, '', $value);
                                $operator = ' ' . $first_char . ' ';
                            } else {
                                $operator = false;
                            }

                            /*
                            if value is a date then this will make it an SQL date
                            */
                            $value = SC_Date::dateFormat($value, SC_DB_MYSQL_DATE);

                            /*
                            If we have an operator then we use that to do our match
                            */
                            if ($operator) {

                                $statement .= $operator;            // prefix
                                // string field or date value must have quotes
                                if (SC_Date::isMySQLDate($value) || $this->isStringType($field)) {
                                    $statement .= '"' . mysqli_real_escape_string($this->connection->connection, $value) . '"';
                                } else {
                                    $statement .= mysqli_real_escape_string($this->connection->connection, $value);
                                }
                            } /*
						  No operator so we simply do a LIKE match
						  */
                            else {
                                $statement .= ' LIKE "%' . mysqli_real_escape_string($this->connection->connection, $value) . '%"';
                            }

                        }
                    }
                }
            }
        }
        /*
        Build the ORDER BY
        */
        $statement .= CR . 'ORDER BY ';

//		if (!	$this->fieldExists( str_replace( ' DESC', '' , $order_by ) ) ){
        if (!$order_by) {
            $order_by = $this->getPKName();
        }

        /*
        always put inactive rows at end of results
        */
        if (
            $this->fieldExists($this->getTableFieldname('active')) &&
            $order_by != $this->getTableFieldname('active')
        ) {
            $statement .= $this->getTableFieldname('active') . ' DESC, ';
        }

        $statement .= $order_by;

        $statement .= CR . 'LIMIT ' . (int)$start_row . ',' . $row_count;

        $this->statement = $this->connection->prepare($statement);

        $this->statement->execute();

        if ($return_array) {
            return $this->statement->fetchAllAssoc();
        } else {
            return $this->statement;
        }
    }

    function fetchNext()
    {
        if (
            $this->statement->result &&
            $this_row = $this->statement->fetchAssoc()
        ) {
            $this->updateRowFrom($this_row);
            return true;
        } else {
            return false;
        }
    }
} //end class SC_db
?>