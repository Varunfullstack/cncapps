<?
/*
* Manufacturer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBCNCEntity.inc.php");
class DBEManufacturer extends DBCNCEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEManufacturer(&$owner){
		$this->constructor($owner);
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::constructor($owner);
		$this->setTableName("manufact");
 		$this->addColumn("manufacturerID", DA_ID, DA_NOT_NULL, "man_manno");
 		$this->addColumn("name", DA_STRING, DA_NOT_NULL, "man_name");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	function getRowsByNameMatch(){
		$this->setMethodName("getRowsByNameMatch");
		$ret=FALSE;
		if ($this->getValue('name')==''){
			$this->raiseError('name not set');
		}

		$queryString =
				"SELECT ".$this->getDBColumnNamesAsString().
	     			" FROM ".$this->getTableName().
						" WHERE 1=1";
		$queryString .=					
				" AND man_name LIKE '%" . $this->getValue('name') .	"%'";


		$queryString .=					
				" ORDER BY " . $this->getDBColumnName('name').
				" LIMIT 0,200";
				
		$this->setQueryString($queryString);

		$ret=(parent::getRows());
		return $ret;
	}
}
?>