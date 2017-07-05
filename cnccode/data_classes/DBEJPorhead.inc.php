<?
/*
* Porhead table join for descriptions: purchase order header
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEPorhead.inc.php");
class DBEJPorhead extends DBEPorhead{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJPorhead(&$owner){
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
 		$this->setAddColumnsOn();
 		$this->addColumn("supplierName", DA_STRING, DA_NOT_NULL, 'sup_name');
 		$this->addColumn("supplierPhone", DA_STRING, DA_NOT_NULL, 'sup_phone');
 		$this->addColumn("customerName", DA_STRING, DA_NOT_NULL, 'cus_name');
		$this->addColumn("contactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(contact.con_first_name,' ',contact.con_last_name)");
		$this->addColumn("contactPhone", DA_STRING, DA_ALLOW_NULL, 'contact.con_phone');
		$this->addColumn("contactEmail", DA_STRING, DA_ALLOW_NULL, 'contact.con_email');
		$this->addColumn("orderedByName", DA_STRING, DA_ALLOW_NULL, 'ob.cns_name');
		$this->addColumn("raisedByName", DA_STRING, DA_ALLOW_NULL, 'rb.cns_name');
 		$this->addColumn("webSiteURL", DA_STRING, DA_ALLOW_NULL, "sup_web_site_url");
 		$this->setAddColumnsOff();
 	}
	/**
	* Get rows by operative and date
	* @access public
	* @return bool Success
	*/
	function getRowsBySearchCriteria(
		$supplierID,
		$ordheadID,
		$orderType,
		$supplierRef,
		$lineText,
		$partNo = '',
		$fromDate = '',
		$toDate = '',
		$context = 'PO'					// PI = Purchase Invoice 'PO' = Purchase Order GI = Goods In
	){
		$this->setMethodName("getRowsBySearchCriteria");
		if ( $lineText != '' OR $partNo != ''){
			$statement=
				"SELECT DISTINCT ".$this->getDBColumnNamesAsString().
				" FROM ".$this->getTableName().
				" JOIN porline ON ".$this->getTableName().".".$this->getDBColumnName('porheadID').
					"= porline.pol_porno".
				" JOIN item ON porline.pol_itemno".
					"= item.itm_itemno".
				" JOIN supplier ON ".$this->getTableName().".".$this->getDBColumnName('supplierID').
					"= supplier.sup_suppno".
				" LEFT JOIN ordhead ON ".$this->getTableName().".".$this->getDBColumnName('ordheadID').
					"= ordhead.odh_ordno".
   			" LEFT JOIN contact".
				" ON ".$this->getDBColumnName('contactID'). " = contact.con_contno".
   			" LEFT JOIN consultant AS rb".
				" ON ".$this->getDBColumnName('userID'). " = rb.cns_consno".
   			" LEFT JOIN consultant AS ob".
				" ON ".$this->getDBColumnName('orderUserID'). " = ob.cns_consno".
				" LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno";
		}
		else{
			$statement=
				"SELECT ".$this->getDBColumnNamesAsString().
				" FROM ".$this->getTableName().
				" JOIN supplier ON ".$this->getTableName().".".$this->getDBColumnName('supplierID').
					"= supplier.sup_suppno".
   			" LEFT JOIN contact".
				" ON ".$this->getDBColumnName('contactID'). " = contact.con_contno".
   			" LEFT JOIN consultant AS rb".
				" ON ".$this->getDBColumnName('userID'). " = rb.cns_consno".
   			" LEFT JOIN consultant AS ob".
				" ON ".$this->getDBColumnName('orderUserID'). " = ob.cns_consno".
				" LEFT JOIN ordhead ON ".$this->getTableName().".".$this->getDBColumnName('ordheadID').
					"= ordhead.odh_ordno".
				" LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno";
		}
		$statement=$statement." WHERE 1=1";
		if ($supplierID!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('supplierID')."=".$supplierID;
		}
		if ($ordheadID!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('ordheadID')."=".$ordheadID;
		}
		if ($fromDate != ''){
			$statement = $statement.
				" AND ".$this->getDBColumnName('date').">= '".$fromDate."'";
		}
		if ($toDate != ''){
			$statement = $statement.
				" AND ".$this->getDBColumnName('date')."<= '".$toDate."'";
		}


		if ($orderType!=''){
			if ($orderType=='B'){
				$statement=$statement.
					" AND ".$this->getDBColumnName('type')." IN('I','P')";
			}
			else{
				$statement=$statement.
					" AND ".$this->getDBColumnName('type')."='".mysql_escape_string($orderType)."'";
			}
		}

		// context of search
		if ($context == 'PI'){				// For purchase invoices exclude authorised POs and
			$statement .=								// stock suppliers
				" AND " . $this->getDBColumnName('supplierID') .
					" NOT IN(" . CONFIG_SALES_STOCK_SUPPLIERID . "," . CONFIG_MAINT_STOCK_SUPPLIERID.")".
				" AND (".
					" (" . $this->getDBColumnName('type') . " IN ('P', 'C') AND " . $this->getDBColumnName('directDeliveryFlag') . "= 'N' )" .
					" OR".
					" (" . $this->getDBColumnName('type') . " IN ('P', 'C', 'I') AND " . $this->getDBColumnName('directDeliveryFlag') . "= 'Y' )" .
				")";
		}
		
		elseif ($context == 'GI'){		// for goods in exclude direct delivery
			$statement .= " AND " . $this->getDBColumnName('directDeliveryFlag') . "<> 'Y'";		
		}
		
		if ( $lineText != '' ){
			$statement .=
//				" AND item.itm_desc LIKE '%".mysql_escape_string($lineText)."%'";
				" AND MATCH (item.itm_desc, item.notes, item.itm_unit_of_sale)
					AGAINST ('" . mysql_escape_string($lineText) .	"' IN BOOLEAN MODE)";
		}

		if ( $partNo != '' ){
			$statement .=
				" AND item.itm_unit_of_sale LIKE '%".mysql_escape_string($partNo)."%'";
		}
		if ($supplierRef!=''){
			$statement .=
				" AND ".$this->getDBColumnName('supplierRef')." LIKE '%".mysql_escape_string($supplierRef)."%'";
		}
		$statement=$statement.
			" ORDER BY ".$this->getDBColumnName('porheadID')." DESC".
			" LIMIT 0,200";
			
		$this->setQueryString($statement);
		$ret=(parent::getRows());
		return $ret;
	}
	function getRow(){
		$this->setMethodName("getRow");
		$ret=FALSE;
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN supplier ON ".$this->getTableName().".".$this->getDBColumnName('supplierID')."= supplier.sup_suppno".
			" LEFT JOIN ordhead ON ".$this->getTableName().".".$this->getDBColumnName('ordheadID').
			"= ordhead.odh_ordno".
 			" LEFT JOIN consultant AS rb".
			" ON ".$this->getDBColumnName('userID'). " = rb.cns_consno".
 			" LEFT JOIN consultant AS ob".
			" ON ".$this->getDBColumnName('orderUserID'). " = ob.cns_consno".
 			" LEFT JOIN contact".
			" ON ".$this->getDBColumnName('contactID'). " = contact.con_contno".
			" LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno".
			" WHERE ".$this->getPKWhere()
		);
		return (parent::getRow());
	}
	function getPurchaseInvoiceRow(){
		$this->setMethodName("getPurchaseInvoiceRow");
		$ret=FALSE;
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN supplier ON ".$this->getTableName().".".$this->getDBColumnName('supplierID')."= supplier.sup_suppno".
			" LEFT JOIN ordhead ON ".$this->getTableName().".".$this->getDBColumnName('ordheadID').
			"= ordhead.odh_ordno".
 			" LEFT JOIN consultant AS rb".
			" ON ".$this->getDBColumnName('userID'). " = rb.cns_consno".
 			" LEFT JOIN consultant AS ob".
			" ON ".$this->getDBColumnName('orderUserID'). " = ob.cns_consno".
 			" LEFT JOIN contact".
			" ON ".$this->getDBColumnName('contactID'). " = contact.con_contno".
			" LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno".
			" WHERE ".$this->getPKWhere().
			" AND " . $this->getDBColumnName('supplierID') .
				" NOT IN(" . CONFIG_SALES_STOCK_SUPPLIERID . "," . CONFIG_MAINT_STOCK_SUPPLIERID.")".
			" AND (".
				" (" . $this->getDBColumnName('type') . " IN ('P', 'C') AND " . $this->getDBColumnName('directDeliveryFlag') . "= 'N' )" .
				" OR".
				" (" . $this->getDBColumnName('type') . " IN ('P', 'C', 'I') AND " . $this->getDBColumnName('directDeliveryFlag') . "= 'Y' )" .
			")"
		);
		return (parent::getRow());
	}
}
?>