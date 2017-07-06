<?
/*
* Invhead table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEInvhead extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEInvhead(&$owner){
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
		$this->setTableName("invhead");
 		$this->addColumn("invheadID", DA_ID, DA_NOT_NULL, "inh_invno");
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL, "inh_custno");
 		$this->addColumn("siteNo", DA_ID, DA_ALLOW_NULL, "inh_siteno");
 		$this->addColumn("ordheadID", DA_ID, DA_ALLOW_NULL, "inh_ordno");
 		$this->addColumn("type", DA_STRING, DA_NOT_NULL, "inh_type");
 		$this->addColumn("add1", DA_STRING, DA_NOT_NULL, "inh_add1");
 		$this->addColumn("add2", DA_STRING, DA_ALLOW_NULL, "inh_add2");
 		$this->addColumn("add3", DA_STRING, DA_ALLOW_NULL, "inh_add3");
 		$this->addColumn("town", DA_STRING, DA_NOT_NULL, "inh_town");
 		$this->addColumn("county", DA_STRING, DA_ALLOW_NULL, "inh_county");
 		$this->addColumn("postcode", DA_STRING, DA_NOT_NULL, "inh_postcode");
 		$this->addColumn("contactID", DA_ID, DA_NOT_NULL, "inh_contno");
 		$this->addColumn("contactName", DA_STRING, DA_ALLOW_NULL, "inh_contact");
 		$this->addColumn("salutation", DA_STRING, DA_ALLOW_NULL, "inh_salutation");
 		$this->addColumn("payMethod", DA_STRING, DA_NOT_NULL, "inh_pay_method");
 		$this->addColumn("paymentTermsID", DA_ID, DA_NOT_NULL, 'invhead.paymentTermsID');
 		$this->addColumn("vatCode", DA_STRING, DA_ALLOW_NULL, "inh_vat_code");
 		$this->addColumn("vatRate", DA_FLOAT, DA_ALLOW_NULL, "inh_vat_rate");
 		$this->addColumn("intPORef", DA_STRING, DA_ALLOW_NULL, "inh_ref_ecc");
 		$this->addColumn("custPORef", DA_STRING, DA_ALLOW_NULL, "inh_ref_cust");
// 		$this->addColumn("addItem", DA_YN, DA_ALLOW_NULL, "inh_add_item");
 		$this->addColumn("debtorCode", DA_STRING, DA_ALLOW_NULL, "inh_debtor_code");
 		$this->addColumn("source", DA_STRING, DA_ALLOW_NULL, "inh_source");
 		$this->addColumn("vatOnly", DA_YN, DA_ALLOW_NULL, "inh_vat_only");
 		$this->addColumn("datePrinted", DA_DATE, DA_ALLOW_NULL, "inh_date_printed");
    $this->addColumn("pdfFile", DA_BLOB, DA_ALLOW_NULL, "inh_pdf_file");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	/**
	* Set the date printed to passed date for all unprinted invoices in the 
	* database
	* @parameter Date $dateToUse Date to use for printed date
	* @return DataSet &$dsResults results
	* @access public
	*/
	function setUnprintedInvoicesDate($date){
 		if ($date == ''){
 			$this->raiseError('date not passed');
 		}
 		$queryString =
 			"UPDATE ". $this->getTableName() .
 			" SET " . $this->getDBColumnName('datePrinted') . "='" . mysql_escape_string($date). "'".
 			" WHERE " . $this->getDBColumnName('datePrinted') . "='0000-00-00'"; 
		$this->setQueryString($queryString);
		$ret = (parent::runQuery());
		$this->resetQueryString();
		return $ret;
 	}
	/**
	* Count number of unprinted credit notes or invoices
	* @param string $type I=Invoices C=Credit Notes
	* @return Integer Count
	* @access public
	*/
	function countUnprinted($type = 'I'){
		$this->setQueryString(
			"SELECT COUNT(*)".
			" FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('type'). "='" . mysql_escape_string($type) . "'".
			" AND ".$this->getDBColumnName('datePrinted'). "='0000-00-00'"
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$this->resetQueryString();
				return ($this->getDBColumnValue(0));
			}
		}
	}
	/**
	* Value of unprinted credit notes or invoices
	* @param string $type I=Invoices C=Credit Notes
	* @return Integer Count
	* @access public
	*/
	function valueUnprinted($type = 'I'){
		$this->setQueryString(
			"SELECT SUM(inl_unit_price)".
			" FROM ".$this->getTableName().
			" JOIN invline ON ".$this->getDBColumnName('invheadID')."=inl_invno".
			" WHERE ".$this->getDBColumnName('type'). "='" . mysql_escape_string($type) . "'".
			" AND ".$this->getDBColumnName('datePrinted'). "='0000-00-00'".
			" AND inl_unit_price IS NOT NULL"
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$this->resetQueryString();
				return ($this->getDBColumnValue(0));
			}
		}
	}
	function countRowsByCustomerSiteNo($customerID, $siteNo){
		$this->setQueryString(
			"SELECT COUNT(*) FROM ". $this->getTableName().
			" WHERE " . $this->getDBColumnName('customerID'). "=" . $customerID.
			" AND " . $this->getDBColumnName('siteNo'). "=" . $siteNo
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