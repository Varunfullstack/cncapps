<?php
/**
* File handling classes
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
class SC_File extends SC_Object{
	/**
	* delete all files in $dir
	*/
	function rmr($dir) {
		 if($objs = glob($dir."/*")){
				 foreach($objs as $obj) {
						 is_dir($obj)? rmdirr($obj) : unlink($obj);
				 }
		 }
	}
} // end class SC_File
?>