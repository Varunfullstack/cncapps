<?
/*
* Ordhead table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEOrdhead extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEOrdhead(&$owner){
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
		$this->setTableName("Ordhead");
 		$this->addColumn("ordheadID", DA_ID, DA_NOT_NULL, "odh_ordno");
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL, "odh_custno");
 		$this->addColumn("type", DA_STRING, DA_NOT_NULL, "odh_type");
 		$this->addColumn("partInvoice", DA_YN, DA_NOT_NULL, "odh_part_invoice");
 		$this->addColumn("date", DA_DATE, DA_NOT_NULL, "odh_date");
 		$this->addColumn("requestedDate", DA_DATE, DA_ALLOW_NULL, "odh_req_date");
 		$this->addColumn("promisedDate", DA_DATE, DA_ALLOW_NULL, "odh_prom_date");
 		$this->addColumn("expectedDate", DA_DATE, DA_ALLOW_NULL, "odh_expect_date");
 		$this->addColumn("quotationOrdheadID", DA_STRING, DA_ALLOW_NULL, "odh_quotation_ordno");
 		$this->addColumn("custPORef", DA_STRING, DA_ALLOW_NULL, "odh_ref_cust");
 		$this->addColumn("vatCode", DA_STRING, DA_ALLOW_NULL, "odh_vat_code");
 		$this->addColumn("vatRate", DA_STRING, DA_ALLOW_NULL, "odh_vat_rate");
 		$this->addColumn("invSiteNo", DA_ID, DA_NOT_NULL, "odh_inv_siteno");
 		$this->addColumn("invAdd1", DA_STRING, DA_NOT_NULL, "odh_inv_add1");
 		$this->addColumn("invAdd2", DA_STRING, DA_ALLOW_NULL, "odh_inv_add2");
 		$this->addColumn("invAdd3", DA_STRING, DA_ALLOW_NULL, "odh_inv_add3");
 		$this->addColumn("invTown", DA_STRING, DA_ALLOW_NULL, "odh_inv_town");
 		$this->addColumn("invCounty", DA_STRING, DA_ALLOW_NULL, "odh_inv_county");
 		$this->addColumn("invPostcode", DA_STRING, DA_ALLOW_NULL, "odh_inv_postcode");
 		$this->addColumn("invContactID", DA_ID, DA_NOT_NULL, "odh_inv_contno");
 		$this->addColumn("invContactName", DA_STRING, DA_ALLOW_NULL, "odh_inv_contact");
 		$this->addColumn("invContactSalutation", DA_STRING, DA_ALLOW_NULL, "odh_inv_salutation");
 		$this->addColumn("invContactPhone", DA_STRING, DA_ALLOW_NULL, "odh_inv_phone");
 		$this->addColumn("invSitePhone", DA_STRING, DA_ALLOW_NULL, "odh_inv_sphone");
 		$this->addColumn("invContactFax", DA_STRING, DA_ALLOW_NULL, "odh_inv_fax");
 		$this->addColumn("invContactEmail", DA_STRING, DA_ALLOW_NULL, "odh_inv_email");
 		$this->addColumn("delSiteNo", DA_ID, DA_NOT_NULL, "odh_del_siteno");
 		$this->addColumn("delAdd1", DA_STRING, DA_NOT_NULL, "odh_del_add1");
 		$this->addColumn("delAdd2", DA_STRING, DA_ALLOW_NULL, "odh_del_add2");
 		$this->addColumn("delAdd3", DA_STRING, DA_ALLOW_NULL, "odh_del_add3");
 		$this->addColumn("delTown", DA_STRING, DA_ALLOW_NULL, "odh_del_town");
 		$this->addColumn("delCounty", DA_STRING, DA_ALLOW_NULL, "odh_del_county");
 		$this->addColumn("delPostcode", DA_STRING, DA_ALLOW_NULL, "odh_del_postcode");
 		$this->addColumn("delContactID", DA_ID, DA_NOT_NULL, "odh_del_contno");
 		$this->addColumn("delContactName", DA_STRING, DA_ALLOW_NULL, "odh_del_contact");
 		$this->addColumn("delContactSalutation", DA_STRING, DA_ALLOW_NULL, "odh_del_salutation");
 		$this->addColumn("delContactPhone", DA_STRING, DA_ALLOW_NULL, "odh_del_phone");
 		$this->addColumn("delSitePhone", DA_STRING, DA_ALLOW_NULL, "odh_del_sphone");
 		$this->addColumn("delContactFax", DA_STRING, DA_ALLOW_NULL, "odh_del_fax");
 		$this->addColumn("delContactEmail", DA_STRING, DA_ALLOW_NULL, "odh_del_email");
 		$this->addColumn("debtorCode", DA_STRING, DA_ALLOW_NULL, "odh_debtor_code");
 		$this->addColumn("wip", DA_YN, DA_ALLOW_NULL, "odh_wip");
 		$this->addColumn("consultantID", DA_ID, DA_ALLOW_NULL, "odh_consno");
 		$this->addColumn("payMethod", DA_STRING, DA_ALLOW_NULL, "odh_pay_method");
 		$this->addColumn("paymentTermsID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("addItem", DA_YN, DA_ALLOW_NULL, "odh_add_item");
 		$this->addColumn("callID", DA_ID, DA_ALLOW_NULL, "odh_callno");
 		$this->addColumn("quotationSubject", DA_STRING, DA_ALLOW_NULL, "odh_quotation_subject");
 		$this->addColumn("quotationIntroduction", DA_STRING, DA_ALLOW_NULL, "odh_quotation_introduction");
 	 $this->addColumn("updatedTime", DA_DATETIME, DA_ALLOW_NULL);
   $this->addColumn("serviceRequestCustomerItemID", DA_ID, DA_ALLOW_NULL, 'odh_service_request_custitemno');
   $this->addColumn("serviceRequestPriority", DA_ID, DA_ALLOW_NULL, 'odh_service_request_priority');
   $this->addColumn("serviceRequestText", DA_MEMO, DA_ALLOW_NULL, 'odh_service_request_text');
   $this->addColumn("quotationCreateDate", DA_DATE, DA_ALLOW_NULL, "odh_quotation_create_date");
 	 $this->setPK(0);
 	 $this->setAddColumnsOff();
	}
	function insertRow()
	{
		$this->setValue('updatedTime', date('Y-m-d H:i:s'));
		parent::insertRow();
	}
	function updateRow()
	{
		$this->setValue('updatedTime', date('Y-m-d H:i:s'));
		parent::updateRow();
	}
	function setUpdatedTime()
	{
		if ($this->getPKValue() == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setValue('updatedTime', date('Y-m-d H:i:s'));
		$this->setQueryString(
			"UPDATE ". $this->getTableName().
			" SET ". $this->getDBColumnName('updatedTime'). "='". date('Y-m-d H:i:s')."'".
			" WHERE ". $this->getPKDBName(). "=" . $this->getPKValue()
		);
		$this->runQuery();
		$this->resetQueryString();
		return TRUE;
	}
	function setStatusCompleted()
	{
		if ($this->getPKValue() == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			"UPDATE ". $this->getTableName().
			" SET ". $this->getDBColumnName('type'). "='C'". 
			" WHERE ". $this->getPKDBName(). "=" . $this->getPKValue()
		);
		$this->runQuery();
		$this->resetQueryString();
		return TRUE;
	}
	function countRowsByCustomerSiteNo($customerID, $siteNo){
		$this->setQueryString(
			"SELECT COUNT(*) FROM ". $this->getTableName().
			" WHERE " . $this->getDBColumnName('customerID'). "=" . $customerID.
			" AND (" . $this->getDBColumnName('delSiteNo'). "=" . $siteNo .
			" OR "  . $this->getDBColumnName('invSiteNo'). "=" . $siteNo . ")"
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$this->resetQueryString();
				return ($this->getDBColumnValue(0));
			}
		}
	}
	function countRowsByContactID($contactID){
		$this->setQueryString(
			"SELECT COUNT(*) FROM ". $this->getTableName().
			" WHERE " . $this->getDBColumnName('delContactID'). "=" . $contactID .
			" OR "  . $this->getDBColumnName('invContactID'). "=" . $contactID
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