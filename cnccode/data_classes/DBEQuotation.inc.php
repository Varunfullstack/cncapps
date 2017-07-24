<?php /*
* Quotation table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEQuotation extends DBEntity{
	/**
	* calls constructor()
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
		$this->setTableName("quotation");
 		$this->addColumn("quotationID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("ordheadID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("versionNo", DA_INTEGER, DA_NOT_NULL);
 		$this->addColumn("salutation", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("emailSubject", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("sentDateTime", DA_DATETIME, DA_NOT_NULL);
 		$this->addColumn("userID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("fileExtension", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("documentType", DA_STRING, DA_ALLOW_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	function getNextVersionNo(){
		$this->setQueryString(
			'SELECT MAX('.$this->getDBColumnName('versionNo').') + 1 FROM '. $this->getTableName().
			' WHERE '.$this->getDBColumnName('ordheadID').'='.$this->getFormattedValue('ordheadID')
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$ret=($this->getDBColumnValue(0));
			}
		}
		$this->resetQueryString();
		if ($ret==null){
			$ret=1;
		}
		return $ret;
	}
	function deleteRowsByOrderID(){
		$this->setMethodName('deleteRowsByOrderID');
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			'DELETE from '.$this->getTableName().' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID')
		);
		return (parent::runQuery());
	}
}
?>