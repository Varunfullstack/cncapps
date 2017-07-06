<?
/**
* Customer business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
class BUCNC extends Business{
	/**
	* Constructor
	* @access Public
	*/
	function BUCNC(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
	}
	/**
	* Get all rows from a data access object but NOT into a dataset (as in Business base class)
	* Instead the data object is copied to the dataset which is just a cheat
	* to get BUSalesOrder working very quickly without fiddling about with existing code too much :)
	* @param Database &$Source Data access object
	* @param DataSet &$Destinantion Set of data
	* @return bool
	* @access private
	*/
	function getData(&$dbSource, &$dsDestination){
/*
		if (!is_object($dsDestination)){
			$dsDestination=new Dataset($this);
		}
		else{
			if (
				(get_class($dsDestination)!=DA_CLASSNAME_DATASET)&
				(!is_subclass_of($dsDestination, DA_CLASSNAME_DATASET))
			){
				$this->raiseError("dsDestination must be subclass or class of ".
					DA_CLASSNAME_DATASET);
			}
		}
*/
		if (gettype($dbSource)!="object")
			$this->raiseError("dbSource is not initialised");
		if (!is_subclass_of($dbSource, DA_CLASSNAME_DBENTITY))
			$this->raiseError("dbSource must be subclass of ".DA_CLASSNAME_DBENTITY);
		$dsDestination = $dbSource;
		return ($dsDestination->rowCount()>0);
	}
}// End of class
?>