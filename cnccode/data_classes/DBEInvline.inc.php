<?
/*
* Invline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEInvline extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEInvline(&$owner){
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
		$this->setTableName("invline");
 		$this->addColumn("invheadID", DA_ID, DA_NOT_NULL, "inl_invno");
 		$this->addColumn("sequenceNo", DA_INTEGER, DA_NOT_NULL, "inl_line_no");
 		$this->addColumn("ordSequenceNo", DA_ID, DA_ALLOW_NULL, "inl_ord_line_no");
 		$this->addColumn("lineType", DA_STRING, DA_NOT_NULL, "inl_line_type");
 		$this->addColumn("itemID", DA_ID, DA_ALLOW_NULL, "inl_itemno");
 		$this->addColumn("description", DA_STRING, DA_ALLOW_NULL, "inl_desc");
 		$this->addColumn("qty", DA_FLOAT, DA_ALLOW_NULL, "inl_qty");
 		$this->addColumn("curUnitSale", DA_FLOAT, DA_ALLOW_NULL, "inl_unit_price");
 		$this->addColumn("curUnitCost", DA_FLOAT, DA_ALLOW_NULL, "inl_cost_price");
 		$this->addColumn("stockcat", DA_STRING, DA_ALLOW_NULL, "inl_stockcat");
 		$this->setAddColumnsOff();
 	}
	function deleteRowsByInvheadID(){
		$this->setMethodName('deleteRowsByInvheadID');
		if ($this->getValue('invheadID')==''){
				$this->raiseError('invheadID not set');
		}
		$this->setQueryString(
			"DELETE FROM ". $this->getTableName().
			" WHERE ".$this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID')
		);
		$ret = ($this->runQuery());
		$this->resetQueryString();
		return $ret;
	}
	function getPKWhere(){
		return(
			$this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID').
			" AND " . $this->getDBColumnName('sequenceNo').' = '.$this->getFormattedValue('sequenceNo')
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
		if ($this->getValue('invheadID') == ''){
			$this->raiseError('InvheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		$sequenceNo = $this->getDBColumnName('sequenceNo');
		$InvheadID = $this->getDBColumnName('invheadID');
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '+1'.
			' WHERE ' . $this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID').
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
		if ($this->getValue('invheadID') == ''){
			$this->raiseError('InvheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '-1'.
			' WHERE ' . $this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' >= '.$this->getFormattedValue('sequenceNo')
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
		if ($this->getValue('invheadID') == ''){
			$this->raiseError('InvheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->raiseError('sequenceNo not set');
		}
		// current row into temporary buffer row: sequenceNo = -99
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = -99'.	
			' WHERE ' . $this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' = '.$this->getFormattedValue('sequenceNo')
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
			' SET '. $this->getDBColumnName('sequenceNo') .' = ' . $this->getFormattedValue('sequenceNo') .
			' WHERE ' . $this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID') .
			' AND ' . $this->getDBColumnName('sequenceNo').' = '.$sequenceNo
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		// Move current row from temp
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = ' . $sequenceNo.
			' WHERE ' . $this->getDBColumnName('invheadID').' = '.$this->getFormattedValue('invheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' = -99'
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
	}
	/**
	* get maximum sequence no for this order
	* @access public
	* @return bool Success
	*/
	function getMaxSequenceNo($invheadID){
		$this->setMethodName("getMaxSequenceNo");
		if ($invheadID==''){
			$this->raiseError('invheadID not passed');
			return FALSE;
		}
		$this->setQueryString(
			"SELECT MAX(" . $this->getDBColumnName('sequenceNo') . ")".
			" FROM " . $this->getTableName().
			" WHERE " . $this->getDBColumnName('invheadID') . "=" . $invheadID
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