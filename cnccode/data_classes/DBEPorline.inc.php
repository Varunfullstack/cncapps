<?
/*
* Porline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEPorline extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEPorline(&$owner){
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
		$this->setTableName("Porline");
 		$this->addColumn("porheadID", DA_ID, DA_NOT_NULL, "pol_porno");
 		$this->addColumn("sequenceNo", DA_INTEGER, DA_NOT_NULL, "pol_lineno");
 		$this->addColumn("itemID", DA_ID, DA_NOT_NULL, "pol_itemno");
 		$this->addColumn("qtyOrdered", DA_FLOAT, DA_NOT_NULL, "pol_qty_ord");
 		$this->addColumn("qtyReceived", DA_FLOAT, DA_ALLOW_NULL, "pol_qty_rec");
 		$this->addColumn("qtyInvoiced", DA_FLOAT, DA_ALLOW_NULL, "pol_qty_inv");
 		$this->addColumn("curUnitCost", DA_FLOAT, DA_NOT_NULL, "pol_cost");
 		$this->addColumn("stockcat", DA_STRING, DA_NOT_NULL, "pol_stockcat");
 		$this->addColumn("expectedDate", DA_DATE, DA_ALLOW_NULL, "pol_exp_date");
 		$this->setAddColumnsOff();
	}
	function deleteRowsByOrdheadID(){
		$this->setMethodName('deleteRowsByOrdheadID');
		if ($this->getValue('porheadID')==''){
				$this->raiseError('porheadID not set');
		}
		$this->setQueryString(
			"DELETE FROM ". $this->getTableName().
			" WHERE ".$this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID')
		);
		$ret = ($this->runQuery());
		$this->resetQueryString();
		return $ret;
	}
	function getPKWhere(){
		return(
			$this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID').
			" AND " . $this->getDBColumnName('sequenceNo').' = '.$this->getValue('sequenceNo')
		);
	}
	/**
	* Shuffle down rows ready for insertion of new one
	* @access public
	* @return bool
	*/
	function shuffleRowsDown()
	{
		$this->setMethodName("shuffleRowsDown");
		if ($this->getValue('porheadID') == ''){
			$this->raiseError('porheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		$sequenceNo = $this->getDBColumnName('sequenceNo');
		$porheadID = $this->getDBColumnName('porheadID');
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '+1'.
			' WHERE ' . $this->getDBColumnName('porheadID').' = '.$this->getFormattedValue('porheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' >= '.$this->getFormattedValue('sequenceNo')
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
	}
	/**
	* Shuffle up rows after deletion
	* @access public
	* @return bool
	*/
	function shuffleRowsUp()
	{
		$this->setMethodName("shuffleRowsUp");
		if ($this->getValue('porheadID') == ''){
			$this->raiseError('porheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '-1'.
			' WHERE ' . $this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' >= '.$this->getValue('sequenceNo')
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
	}
	/**
	* Move given row down in the sequence
	* Swaps sequence numbers of 2 rows
	* @access public
	* @return bool
	*/
	function moveRow($direction = 'UP')
	{
		$this->setMethodName("moveRow");
		if ($this->getValue('porheadID') == ''){
			$this->raiseError('porheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		// current row into temporary buffer row: sequenceNo = -99
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = -99'.	
			' WHERE ' . $this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' = '.$this->getValue('sequenceNo')
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		// Move row next to this one
		if ($direction == 'UP'){
			$sequenceNo = $this->getValue('sequenceNo') - 1;
		}
		else{			// down
			$sequenceNo = $this->getValue('sequenceNo') + 1;
		}
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = ' . $this->getValue('sequenceNo') .
			' WHERE ' . $this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID') .
			' AND ' . $this->getDBColumnName('sequenceNo').' = '.$sequenceNo
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		// Move current row from temp
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = ' . $sequenceNo.
			' WHERE ' . $this->getDBColumnName('porheadID').' = '.$this->getValue('porheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' = -99'
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
	}
	/**
	* Return count of rows that still have items to be recieved
	*/
	function countOutstandingRows()
	{
		if ($this->getValue('porheadID') == ''){
			$this->raiseError('porheadID not set');
		}
		$this->setQueryString(
			"SELECT COUNT(*)".
			" FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('porheadID')."=".$this->getFormattedValue('porheadID').
			" AND ".$this->getDBColumnName('qtyReceived')." < ".$this->getDBColumnName('qtyOrdered')
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$this->resetQueryString();
				return ($this->getDBColumnValue(0));
			}
		}
	}
}
?>