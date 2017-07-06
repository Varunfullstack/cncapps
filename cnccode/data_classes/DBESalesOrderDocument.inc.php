<?
/*
* portal document table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBESalesOrderDocumentWithoutFile extends DBEntity{
  /**
  * portals constructor()
  * @access public
  * @return void
  * @param  void
  * @see constructor()
  */
  function DBESalesOrderDocumentWithoutFile(&$owner){
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
    $this->setTableName("salesorder_document");
     $this->addColumn("salesOrderDocumentID", DA_ID, DA_NOT_NULL);         
     $this->addColumn("ordheadID", DA_ID, DA_ALLOW_NULL);         
     $this->addColumn("description", DA_STRING, DA_NOT_NULL);
     $this->addColumn("createdDate", DA_DATE, DA_NOT_NULL);
     $this->addColumn("createdUserID", DA_ID, DA_NOT_NULL);
    $this->setPK(0);
     $this->setAddColumnsOff();
  }
}
class DBESalesOrderDocument extends DBESalesOrderDocumentWithoutFile{
	/**
	* portals constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBESalesOrderDocument(&$owner){
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
 		$this->addColumn("filename", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("file", DA_BLOB, DA_ALLOW_NULL);
 		$this->addColumn("fileMimeType", DA_STRING, DA_NOT_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>