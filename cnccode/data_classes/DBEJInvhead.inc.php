<?
/*
* Invhead join to customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEInvhead.inc.php");
class DBEJInvhead extends DBEInvhead{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJInvhead(&$owner){
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
 		$this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
 		$this->addColumn("firstName", DA_STRING, DA_ALLOW_NULL, "con_first_name");
 		$this->addColumn("lastName", DA_STRING, DA_ALLOW_NULL, "con_last_name");
 		$this->addColumn("title", DA_STRING, DA_ALLOW_NULL, "con_title");
    $this->addColumn("paymentTerms", DA_STRING, DA_ALLOW_NULL, "description");
    
    
 		$this->setAddColumnsOff();
 	}
 	function getPrintedRowsByRange( $customerID, $startDate, $endDate, $startID, $endID ){
 		$this->setMethodName('getPrintedRowsByRange');

 		$queryString =
 			'SELECT '.$this->getDBColumnNamesAsString(). ' FROM '. $this->getTableName().
			' LEFT JOIN customer ON inh_custno = cus_custno'.
			' LEFT JOIN contact ON inh_contno = con_contno'.
      ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID '.
 			' WHERE 1=1';

    if ($startDate != ''){
      $queryString .=
         ' AND '.$this->getDBColumnName('datePrinted') . ' >= \'' . mysql_escape_string($startDate) . '\'';
    }
      
		if ($endDate != ''){
			$queryString .=
 				' AND '.$this->getDBColumnName('datePrinted') . ' <= \'' . mysql_escape_string($endDate) . '\'';
		}

    if ($customerID != ''){
      $queryString .=
         ' AND '.$this->getDBColumnName('customerID') . ' = \'' . mysql_escape_string($customerID) . '\'';
    }

    if ($startID != ''){
      $queryString .=
         ' AND '.$this->getDBColumnName('invheadID') . ' >= \'' . $startID . '\'';
    }

    if ($endID != ''){
      $queryString .=
         ' AND '.$this->getDBColumnName('invheadID') . ' <= \'' . $endID . '\'';
    }

 		$queryString .= ' AND '.$this->getDBColumnName('datePrinted') . ' <> \'0000-00-00\'';

		$queryString .= ' ORDER BY ' . $this->getDBColumnName('invheadID');
    
		$this->setQueryString($queryString);
		return ($this->getRows());
 	}

 	function getUnprintedRows(){
 		$this->setMethodName('getUnprintedRows');
 		$queryString =
 			'SELECT '.$this->getDBColumnNamesAsString(). ' FROM '. $this->getTableName().
			' LEFT JOIN customer ON inh_custno = cus_custno'.
			' LEFT JOIN contact ON inh_contno = con_contno'.
      ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID '.
			' WHERE '.$this->getDBColumnName('datePrinted') . ' = \'0000-00-00\'';
		$queryString .= ' ORDER BY ' . $this->getDBColumnName('customerID');
		$this->setQueryString($queryString);
		return ($this->getRows());
 	}
 	function getRowsBySearchCriteria(
		$customerID,
		$ordheadID,
		$printedFlag,
		$fromDate,
		$toDate,
		$invoiceType
 	){
 		$this->setMethodName('getRowsBySearchCriteria');
		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID').
			"= customer.cus_custno".
			" LEFT JOIN contact ON inh_contno = con_contno" .
      ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID ';
		$statement=$statement." WHERE 1=1";
		if ($ordheadID!=''){				// if passed an ordheadID then only use this
			$statement=$statement.
				" AND ".$this->getDBColumnName('ordheadID')."=".$ordheadID;
		}
		else{
			if ($customerID!=''){
				$statement=$statement.
					" AND ".$this->getDBColumnName('customerID')."=".$customerID;
			}
			if ($invoiceType!=''){
				$statement=$statement.
					" AND ".$this->getDBColumnName('type')."='".$invoiceType."'";
			}
			if ($printedFlag == 'Y'){
				if ($fromDate!=''){
					$statement=$statement.
						" AND ".$this->getDBColumnName('datePrinted').">='".mysql_escape_string($fromDate)."'";
				}
				if ($toDate!=''){
					$statement=$statement.
						" AND ".$this->getDBColumnName('datePrinted')."<='".mysql_escape_string($toDate)."'";
				}
			}
			else{
				$statement=$statement.
						" AND ".$this->getDBColumnName('datePrinted')."='0000-00-00'";
			}
		}
		$statement .= " ORDER BY ".$this->getDBColumnName('ordheadID')." DESC";
		$statement .= " LIMIT 0, 200";
		$this->setQueryString($statement);
		$ret=(parent::getRows());
		return $ret;
 	} // no ordheadID
 	function getRow(){
 		$this->setMethodName('getRow');
 		$queryString =
 			'SELECT '.$this->getDBColumnNamesAsString(). ' FROM '. $this->getTableName().
			' LEFT JOIN customer ON inh_custno = cus_custno'.
			' LEFT JOIN contact ON inh_contno = con_contno'.
      ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID '.
 			' WHERE '.$this->getDBColumnName('invheadID') . ' = ' . $this->getFormattedValue('invheadID');
		$this->setQueryString($queryString);
		return (parent::getRow());
 	}
}
?>