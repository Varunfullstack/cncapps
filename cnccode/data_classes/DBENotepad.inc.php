<?
/*
* Notepad table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBENotepad extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBENotepad(&$owner){
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
		$this->setTableName("Notepad");
 		$this->addColumn("noteType", DA_STRING, DA_NOT_NULL, "not_type");
 		$this->addColumn("noteKey",DA_INTEGER, DA_NOT_NULL, "not_key");
 		$this->addColumn("lineNo",DA_INTEGER, DA_NOT_NULL, "not_line");
 		$this->addColumn("noteText", DA_STRING, DA_ALLOW_NULL, "not_text");
// 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	/**
	* Return rows from DB by type and key
	* @access public
	* @return bool Success
	*/
	function getRowsByTypeAndKey($noteType, $noteKey){
		$this->setMethodName("getRowsByTypeAndKey");
		if ($noteType==''){
			$this->raiseError('noteType not passed');
		}
		if ($noteKey==''){
			$this->raiseError('noteKey not passed');
		}
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('noteType')."='".mysql_escape_string($noteType)."'".
			" AND ".$this->getDBColumnName('noteKey')."='".mysql_escape_string($noteKey)."'".
			" ORDER BY ".$this->getDBColumnName('lineNo')
		);
		return($this->getRows());
	}
	function deleteRowsByTypeAndKey($noteType, $noteKey){
		$this->setMethodName("deleteRowsByTypeAndKey");
		if ($noteType==''){
			$this->raiseError('noteType not passed');
		}
		if ($noteKey==''){
			$this->raiseError('noteKey not passed');
		}
		$this->setQueryString(
			"DELETE FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('noteType')."='".mysql_escape_string($noteType)."'".
			" AND ".$this->getDBColumnName('noteKey')."='".mysql_escape_string($noteKey)."'"
		);
		$ret = (parent::runQuery());
		$this->resetQueryString();
		return $ret;
	}
}
?>