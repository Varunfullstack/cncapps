<?php
/**
*	CNC_DB class
*
* @author Karim Ahmed
*	@client CNC Ltd
* (C) CNC Ltd 2005
* @access Public
*/
//require_once($_SERVER['DOCUMENT_ROOT']	. '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES 		. 'db.php');
class CNC_DB extends SC_DB{
	/**
	* @access public
	*/
	function CNC_DB($table_name=false) {
		$this->__construct($table_name);
	}
	/**
	* @access public
	*/
	function __construct($table_name=false) {
		parent::__construct($table_name);
	}
} //end class SC_Authenticate
?>