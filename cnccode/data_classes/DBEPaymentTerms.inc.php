<?
/*
* Supplier payment terms table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEPaymentTerms extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEPaymentTerms(&$owner){
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
		$this->setTableName("paymentterms");
 		$this->addColumn("paymentTermsID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
		$this->addColumn("days", DA_INTEGER, DA_NOT_NULL);
		$this->addColumn("generateInvoiceFlag", DA_YN, DA_NOT_NULL);
		$this->addColumn("automaticInvoiceFlag", DA_YN, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
}
?>