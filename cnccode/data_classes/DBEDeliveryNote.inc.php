<?
/*
* Delivery Note table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEDeliveryNote extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEDeliveryNote(&$owner){
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
		$this->setTableName("deliverynote");
 		$this->addColumn("deliveryNoteID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("ordheadID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("noteNo", DA_INTEGER, DA_NOT_NULL);
 		$this->addColumn("dateTime", DA_DATETIME, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	function getNextNoteNo(){
		$this->setQueryString(
			'SELECT MAX('.$this->getDBColumnName('noteNo').') + 1 FROM '. $this->getTableName().
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
			'DELETE FROM '.$this->getTableName().' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getFormattedValue('ordheadID')
		);
		return (parent::runQuery());
	}
}
?>