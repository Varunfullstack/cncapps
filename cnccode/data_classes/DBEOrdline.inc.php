<?
/*
* Ordline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEOrdline extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEOrdline(&$owner){
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
		$this->setTableName("ordline");
 		$this->addColumn("lineType", DA_STRING, DA_NOT_NULL, "odl_type");
 		$this->addColumn("ordheadID", DA_ID, DA_NOT_NULL, "odl_ordno");
 		$this->addColumn("sequenceNo", DA_INTEGER, DA_NOT_NULL, "odl_item_no");
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL, "odl_custno");
 		$this->addColumn("itemID", DA_ID, DA_ALLOW_NULL, "odl_itemno");
 		$this->addColumn("stockcat", DA_STRING, DA_ALLOW_NULL, "odl_stockcat");
 		$this->addColumn("description", DA_STRING, DA_ALLOW_NULL, "odl_desc");
 		$this->addColumn("qtyOrdered", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_ord");
 		$this->addColumn("qtyDespatched", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_desp");
 		$this->addColumn("qtyLastDespatched", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_last_desp");
 		$this->addColumn("supplierID", DA_ID, DA_ALLOW_NULL, "odl_suppno");
 		$this->addColumn("curUnitCost", DA_FLOAT, DA_ALLOW_NULL, "odl_d_unit");
 		$this->addColumn("curTotalCost", DA_FLOAT, DA_ALLOW_NULL, "odl_d_total");
 		$this->addColumn("curUnitSale", DA_FLOAT, DA_ALLOW_NULL, "odl_e_unit");
 		$this->addColumn("curTotalSale", DA_FLOAT, DA_ALLOW_NULL, "odl_e_total");
 		$this->addColumn("renewalCustomerItemID", DA_ID, DA_ALLOW_NULL, "odl_renewal_cuino");
 		$this->setAddColumnsOff();
 	}
	function deleteRowsByOrderID(){
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			'DELETE from '.$this->getTableName().' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID')
		);
		return (parent::runQuery());
	}
	function getPKWhere(){
		return(
			$this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID').
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
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
		if ($this->getValue('sequenceNo') == ''){
			$this->setValue('sequenceNo', 0);
		}
		$sequenceNo = $this->getDBColumnName('sequenceNo');
		$ordheadID = $this->getDBColumnName('ordheadID');
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '+1'.
			' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getFormattedValue('ordheadID').
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
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
    if ($this->getValue('sequenceNo') == ''){
      $this->setValue('sequenceNo', 0);
    }
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .'='. $this->getDBColumnName('sequenceNo'). '-1'.
			' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID').
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
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
    if ($this->getValue('sequenceNo') == ''){
      $this->setValue('sequenceNo', 0);
    }
		// current row into temporary buffer row: sequenceNo = -99
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = -99'.	
			' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID').
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
			' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID') .
			' AND ' . $this->getDBColumnName('sequenceNo').' = '.$sequenceNo
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		// Move current row from temp
		$this->setQueryString(
			'UPDATE '.$this->getTableName().
			' SET '. $this->getDBColumnName('sequenceNo') .' = ' . $sequenceNo.
			' WHERE ' . $this->getDBColumnName('ordheadID').' = '.$this->getValue('ordheadID').
			' AND ' . $this->getDBColumnName('sequenceNo').' = -99'
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
	}
	/**
	* get all rows ready for generation of purchase orders
	* @access public
	* @return bool
	*/
	function getRowsForPO()
	{
		$this->setMethodName("getRowsForPO");
		if ($this->getValue('ordheadID') == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			"SELECT DISTINCT  odl_suppno, odl_stockcat, ".
			"odl_itemno, odl_d_unit, SUM(odl_qty_ord) ".
      "FROM ordline ".
      "WHERE odl_ordno = ".$this->getValue('ordheadID').
      " AND odl_type = 'I' " .
			"GROUP BY odl_suppno, odl_stockcat, ".
			"odl_itemno, odl_d_unit ".
      "ORDER BY odl_suppno"
    );
    return(parent::getRows());
	}

	function getRowBySequence( $ordheadID, $sequenceNo )
	{
		$this->setMethodName("getRow");

		if ( $ordheadID == ''){
			$this->raiseError('ordheadID not set');
		}

		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
 			" FROM ".$this->getTableName().
      " WHERE odl_ordno = " . $ordheadID . 
			" AND " . $this->getDBColumnName('sequenceNo') . " = " .$sequenceNo
    );

    return( parent::getRow() );
	}
}
?>