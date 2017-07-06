<?
/**
* Invoice business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_dbe"]."/DBEPurchaseInv.inc.php");
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_func"]."/common.inc.php");
class BUSageExport extends Business{
	var $salesHandle="";
	var $transHandle="";
	var $purchaseHandle="";
	var $total_gross_amount='';		
	var $lastRecord='';
	var $year='';
	var $month='';
  var $invoiceNumbers=false;
	/**
	* Constructor
	* @access Public
	*/
	function BUSageExport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
	}
	/**
	* Generate Sage export files
	* @parameter Integer $month month for sage data
	* @parameter Integer $year year for sage data
	* @parameter Boolean $includeSales indicates whether to produce sales report
	* @parameter Boolean $includePurchases indicates whether to produce purchase report
	* @access public
	*/
	function generateSageData(
		$year,
		$month,
		$includeSales,
		$includePurchases
	){
		$this->setMethodName('generateSageData');
		$this->year = $year;
		$this->month = $month;

		if ($includeSales) {
			$this->generateSalesFiles();
		}
		if ($includePurchases) {
			$this->generatePurchaseFile();
		}
	}
  function generateSageSalesDataByInvoiceNumbers( $invoiceNumbers ){
    $this->setMethodName('generateSageSalesDataByInvoiceNumbers');
    $this->invoiceNumbers = $invoiceNumbers;  
    $this->generateSalesFiles();
  }
	/**
	* Generate Sage sales data
	* @access public
	*/
	function generateSalesFiles(){
		global $db; //PHPLib DB object
		$this->setMethodName('generateSalesFiles');
    
    $sql =
      "SELECT ".
        "inh_invno,".
        "add_sage_ref,".
        "inh_custno,".
        "cus_name,".
        "add_add1,".
        "add_add2,".
        "add_town,".
        "add_county,".
        "add_postcode,".
        "(
          SELECT
            con_email
          FROM
            contact
          WHERE
            con_custno = inh_custno
          AND
            con_mailflag4 = 'Y'
          LIMIT 1
        ) as email,".
        "stc_sal_nom,".
        "inl_qty,".
        "if((inl_qty * inl_unit_price) < 0, 'SC', 'SI') AS trans_type,".
        "inl_qty * inl_unit_price AS gross_amount,".
        "inh_date_printed,".
        "inh_vat_code,".
        "inh_vat_rate".
        " FROM invhead".
          " INNER  JOIN invline ON inh_invno = inl_invno".
          " INNER  JOIN stockcat ON inl_stockcat = stc_stockcat".
          " INNER  JOIN customer ON inh_custno = cus_custno".
          " INNER  JOIN address ON inh_siteno = add_siteno AND inh_custno = add_custno";
          
    if ( $this->invoiceNumbers ){
      $sql .= "
        WHERE
          inh_invno IN( " . implode( ',', $this->invoiceNumbers ) . ")";
    }          
    else{
      $sql .=
        " WHERE".
        " MONTH( inh_date_printed )  = $this->month AND YEAR( inh_date_printed ) = $this->year";
    }
    
    $sql .=
      " AND inl_line_type <>  'C'";

    $sql .=
        " ORDER BY inh_invno";

		$db->query( $sql );
    
		$lastCustno = -1;
		$lastInvno = -1;
		$this->salesHandle = fopen(SAGE_EXPORT_DIR . '/sales.csv', 'wb');
		if (!$this->salesHandle){
			$this->raiseError("Unable to open file ".SAGE_EXPORT_DIR . '/sales.csv');
		}
		$this->transHandle = fopen(SAGE_EXPORT_DIR . '/trans.csv', 'wb');
		if (!$this->transHandle){
			$this->raiseError("Unable to open file ".SAGE_EXPORT_DIR . '/trans.csv');
		}

		while( $db->next_record() ){
			if ($db->Record['gross_amount'] == 0){
				continue;
			}
			
			if ( $lastInvno == -1 ){
				$this->lastRecord = $db->Record; // make copy ready for VAT posting at end first invoice
			}

			if ( $db->Record['inh_custno'] <> $lastCustno ){
				$this->postSalesRow();
				$lastCustno = $db->Record['inh_custno'];
			}
	
			// post VAT as a separate row after lines for each invoice
			if ( $db->Record['inh_invno'] <> $lastInvno AND $lastInvno <> -1 ){
				$this->postTransVATRow();
				$this->lastRecord = $db->Record; // make copy ready for VAT posting at end of lines
			}
			$lastInvno = $db->Record['inh_invno'];

			$this->postTransRow();
		} // end while

		$this->postTransVATRow();
		
		fclose($this->salesHandle);
		fclose($this->transHandle);
	}
	/**
	* post row to sales file
	*/
	function postSalesRow()
	{
		GLOBAL $db;
    
		fwrite(
			$this->salesHandle,
			"\"" . addslashes($db->Record['add_sage_ref']) . "\",".
			"\"" . addslashes(substr($db->Record['cus_name'],0,30)) . "\",".
			"\"" . addslashes($db->Record['add_add1']) . "\",".
			"\"" . addslashes($db->Record['add_add2']) . "\",".
//			"\"" . addslashes($db->Record['add_add3']) . "\",".
			"\"" . addslashes($db->Record['add_town']) . "\",".
			"\"" . addslashes($db->Record['add_county']) . "\",".
			"\"" . addslashes($db->Record['add_postcode']) . "\",".
      "\"\",".
      "\"\",".
      "\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
			"\"\",".
      "\"\",".
      "\"\",".
      "\"\",".
			"\"" . $db->Record['email'].  "\"".
			"\r\n"
		);
	}
	/**
	* post row to sales transaction file
	*/
	function postTransRow()
	{
		GLOBAL $db;
		if ($db->Record['trans_type'] == 'SC'){
			$gross_amount = $db->Record['gross_amount'] * -1;			// credit so make -ve
		}
		else{
			$gross_amount = $db->Record['gross_amount'];
		}
		$this->total_gross_amount += $db->Record['gross_amount'];
		$reportLine = 
			"\"" . $db->Record['trans_type'] . "\",".														// Trans type SI/SC
			"\"" . $db->Record['add_sage_ref'] . "\",".													// Cust ref
			"\"" . $db->Record['stc_sal_nom'] . "\",".													// nominal code
			"\"01\",".																													// department
			"\"" .																															// date
				substr($db->Record['inh_date_printed'], 8,2) .
				substr($db->Record['inh_date_printed'], 5,2) .
				substr($db->Record['inh_date_printed'], 0,4) .
			"\",".
			"\"" . addslashes($db->Record['inh_invno']) . "\",".								// reference
			"\"\",".																														// details
			"\"" . common_numberFormat($gross_amount) . "\",".																				// gross
			"\"" . addslashes($db->Record['inh_vat_code']) . "\",".							// VAT code
			"\"0.00\",".																														// Line VAT always zero
			"\r\n";
		fwrite($this->transHandle, $reportLine);
	}
	/**
	* post row to sales transaction file
	*
	* $this->lastRecord holds values from last line in invoice
	*/
	function postTransVATRow()
	{
		$vat_amount = $this->total_gross_amount * ( $this->lastRecord['inh_vat_rate'] / 100 );
		if ($this->total_gross_amount >= 0){
			$trans_type = 'SI';			// Invoice
		}
		else{
			$trans_type = 'SC';			// Credit note
			$vat_amount = $vat_amount * -1;
		}
		$reportLine = 
			"\"" . $trans_type . "\",".																							// Trans type SI/SC
			"\"" . addslashes(substr($this->lastRecord['add_sage_ref'],0,30)) . "\",".// Cust ref
			"\"" . $this->lastRecord['stc_sal_nom'] . "\",".												// nominal code
			"\"01\",".																																// department
			"\"" .																															// date
				substr($this->lastRecord['inh_date_printed'], 8,2) .
				substr($this->lastRecord['inh_date_printed'], 5,2) .
				substr($this->lastRecord['inh_date_printed'], 0,4) .
			"\",".
			"\"" . addslashes($this->lastRecord['inh_invno']) . "\",".							// reference
			"\"VAT\",".																															// details
			"\"0\",".																																// gross always zero for VAT line
			"\"" . addslashes($this->lastRecord['inh_vat_code']) . "\",".						// VAT code
			"\"" . common_numberFormat($vat_amount) . "\"".																								
			"\r\n";
		fwrite($this->transHandle, $reportLine);
		$this->total_gross_amount = 0;
	}
	/**
	* Generate Sage purchase data
	* @access public
	*/
	function generatePurchaseFile(){
		$this->setMethodName('generatePurchaseFile');
		$dbePurchaseInv = new DBEPurchaseInv($this);
		$dbePurchaseInvUpdt = new DBEPurchaseInv($this);
		$dbePurchaseInv->getUnprintedRowsByMonth($this->year, $this->month);
		if ($dbePurchaseInv->fetchNext()){
			$this->purchaseHandle = fopen(SAGE_EXPORT_DIR . '/purchase.csv', 'wb');
			if (!$this->purchaseHandle){
				$this->raiseError("Unable to open file ".SAGE_EXPORT_DIR . '/purchase.csv');
			}
			do { 
				$reportLine = 
					"\"" . $dbePurchaseInv->getValue('type') . "\",".								// PI
					"\"" . $dbePurchaseInv->getValue('accRef') . "\",".
					"\"" . $dbePurchaseInv->getValue('nomRef') . "\",".
					"\"" . $dbePurchaseInv->getValue('dept') . "\",".
					"\"" . 
						substr($dbePurchaseInv->getValue('date'), 8,2) .
						substr($dbePurchaseInv->getValue('date'), 5,2) .
						substr($dbePurchaseInv->getValue('date'), 0,4) .
					"\",".
					"\"" . $dbePurchaseInv->getValue('ref') . "\",".
					"\"" . $dbePurchaseInv->getValue('details') . "\",".
					"\"" . $dbePurchaseInv->getValue('netAmnt') . "\",".
					"\"" . $dbePurchaseInv->getValue('taxCode') . "\",".
					"\"" . $dbePurchaseInv->getValue('taxAmnt') . "\"".
					"\r\n";
				fwrite($this->purchaseHandle, $reportLine);
				$dbePurchaseInvUpdt->setPrintedOn($dbePurchaseInv->getPKValue());
			} while ($dbePurchaseInv->fetchNext());
			fclose($this->purchaseHandle);
		}
	}
}// End of class
?>