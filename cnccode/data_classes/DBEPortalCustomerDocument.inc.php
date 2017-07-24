<?php /*
* portal document table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEPortalCustomerDocumentWithoutFile extends DBEntity{
  /**
  * portals constructor()
  * @access public
  * @return void
  * @param  void
  * @see constructor()
  */
  function __construct(&$owner){
    $this->constructor($owner);
  }
  /**
  * constructor
  * @access public
  * @return void
  * @param  void
  */
  function constructor(&$owner){
    parent::__construct($owner);
    $this->setTableName("portal_customer_document");
     $this->addColumn("portalCustomerDocumentID", DA_ID, DA_NOT_NULL);         
     $this->addColumn("customerID", DA_ID, DA_ALLOW_NULL);         
     $this->addColumn("description", DA_STRING, DA_NOT_NULL);
    $this->addColumn("startersFormFlag", DA_YN, DA_NOT_NULL);
    $this->addColumn("leaversFormFlag", DA_YN, DA_NOT_NULL);
    $this->addColumn("mainContactOnlyFlag", DA_YN, DA_NOT_NULL);
     $this->addColumn("createdDate", DA_DATE, DA_NOT_NULL);
     $this->addColumn("createdUserID", DA_ID, DA_NOT_NULL);
    $this->setPK(0);
     $this->setAddColumnsOff();
  }
}
class DBEPortalCustomerDocument extends DBEPortalCustomerDocumentWithoutFile{
	/**
	* portals constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function __construct(&$owner){
		$this->constructor($owner);
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::__construct($owner);
    $this->setAddColumnsOn();
 		$this->addColumn("filename", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("file", DA_BLOB, DA_ALLOW_NULL);
 		$this->addColumn("fileMimeType", DA_STRING, DA_NOT_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>