<?php
/*
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEActivityProfitReportInvoice extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEActivityProfitReportInvoice(&$owner){
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
		$this->setTableName("callactivity");
		$this->addColumn("InvoiceID", DA_ID, DA_ALLOW_NULL);
		$this->addColumn("Date", DA_DATE, DA_ALLOW_NULL);
 		$this->addColumn("Cost", DA_DECIMAL, DA_NOT_NULL);
 		$this->addColumn("Sale", DA_DECIMAL, DA_NOT_NULL);
 		$this->addColumn("Profit", DA_DECIMAL, DA_ALLOW_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}


	function getRowsBySearchCriteria(
			$customerID,
			$fromDate,
			$toDate
		)
	{

 		$this->setMethodName('getRowsBySearchCriteria');

$query =
		"SELECT
			inh_invno as InvoiceID,
			inh_date_printed as Date,
			SUM( invline.inl_qty * invline.inl_cost_price ) as Cost,
			SUM( invline.inl_qty * invline.inl_unit_price ) as Sale,
			SUM( 
				( invline.inl_qty * invline.inl_unit_price )
				- ( invline.inl_qty * invline.inl_cost_price ) ) as Profit
			
		FROM
				invline
				JOIN invhead 
						ON (invline.inl_invno = invhead.inh_invno)
		WHERE (invhead.inh_custno = '" . $customerID . "')" .
			" and inl_line_type = 'I' " .
			" and inh_date_printed BETWEEN '". $fromDate . "' AND '" . $toDate .
			"' group by inl_invno"; 

			$this->setQueryString($query);

			$ret=(parent::getRows());
			return $ret;
	} 

}
?>