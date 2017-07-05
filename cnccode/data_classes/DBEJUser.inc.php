<?
/*
* User table join to userExt
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEUser.inc.php");
class DBEJUser extends DBEUser{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJUser(&$owner){
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
		$this->setAddColumnsOn();
 		$this->addColumn("signatureFilename", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("jobTitle", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("firstName", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("lastName", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("activeFlag", DA_YN, DA_NOT_NULL);
 		$this->setAddColumnsOff();
 	}
	/**
	* Return all rows from DB
	* @access public
	* @return bool Success
	*/
	function getRows(){
		$this->setMethodName("getRows");
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
					" FROM ".$this->getTableName().' LEFT JOIN userext ON '.$this->getTableName().'.'.$this->getPKDBName().'=userext.userID'.
					' WHERE activeFlag = "Y"'
				);
		return(parent::getRows());
	}
	function getRow(){
		$this->setMethodName("getRow");
		$this->setQueryString("SELECT ".$this->getDBColumnNamesAsString().
						" FROM ".$this->getTableName().
						' LEFT JOIN userext ON '.$this->getTableName().'.'.$this->getPKDBName().'=userext.userID'.
						" WHERE ".$this->getPKWhere()
		);
		return(parent::getRow());
	}
}
?>