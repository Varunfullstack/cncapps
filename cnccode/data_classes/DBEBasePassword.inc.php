<?
/*
* Base Password table
* @authors Karim Ahmed
* @access public
*
* Holds a list of base words to be used during password generation
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEBasePassword extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEBasePassword(&$owner){
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
		$this->setTableName("base_password");
 		$this->addColumn("basePasswordID", DA_ID, DA_NOT_NULL);
    $this->addColumn("passwordString", DA_STRING, DA_NOT_NULL);
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
  
  public function getRandomRow()
  {
    $this->setQueryString(
      "SELECT " . $this->getColumnNamesAsString() .
      " FROM " . $this->getTableName() . 
      " ORDER BY RAND() LIMIT 0,1"
    );
    return parent::getRow();
  }
}
?>
