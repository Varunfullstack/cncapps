<?
/**
* Database entity access class for CNC
*
*@access virtual
*@author Karim Ahmed
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
//require_once($cfg["path_dbe"]."/DBEScoTrans.inc.php");
class DBCNCEntity extends DBEntity{
	var $dbeNextPK='';				// need this one for getting next PK. it is created in descendent 
	function DBCNCEntity(&$owner){
		$this->constructor($owner);
	}
	/**
	* Constructor
	* @return void
	* @access public
	*/
	function constructor(&$owner){
		parent::constructor($owner);
		//$this->dbeScoTrans = new DBEScoTrans($this);
		$this->dbeNextPK = new DBEntity($this);// for getting next key
		$this->dbeNextPK->addColumn('nextID', DA_ID, DA_ALLOW_NULL);
	}
}
?>