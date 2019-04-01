<?php
/**
* String classes
*
* @access static
*/
require_once(CONFIG_PATH_SC_CLASSES .		'object.php');
class MPM_String extends SC_Object{
	/*
	* isClientOrderNo
	*
	* Is this a valid client order number:
	* <= 15 and >=5
	* @access public
	*/
	function isClientOrderNo($value, $min=false, $max=false) {
		trim($value);
		return ( strlen($value) <= $max &  strlen($value) >= $min);
		
	}
}	// end of class SC_String
?>