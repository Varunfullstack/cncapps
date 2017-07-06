<?
/*
* Site table
* NOTE: There are all sorts of workarounds for the fact that there is not a single
* primary key column. The primary key is composite customerID and siteNo
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBESite extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBESite(&$owner){
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
		$this->setTableName("Address");
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL, "add_custno");
 		$this->addColumn("siteNo", DA_ID, DA_ALLOW_NULL, "add_siteno");
 		$this->addColumn("add1", DA_STRING, DA_NOT_NULL, "add_add1");
 		$this->addColumn("add2", DA_STRING, DA_ALLOW_NULL, "add_add2");
 		$this->addColumn("add3", DA_STRING, DA_ALLOW_NULL, "add_add3");
 		$this->addColumn("town", DA_STRING, DA_NOT_NULL, "add_town");
 		$this->addColumn("county", DA_STRING, DA_ALLOW_NULL, "add_county");
 		$this->addColumn("postcode", DA_STRING, DA_NOT_NULL, "add_postcode");
 		$this->addColumn("invContactID", DA_ID, DA_ALLOW_NULL, "add_inv_contno");
 		$this->addColumn("delContactID", DA_ID, DA_ALLOW_NULL, "add_del_contno");
 		$this->addColumn("debtorCode", DA_STRING, DA_ALLOW_NULL, "add_debtor_code");
 		$this->addColumn("sageRef", DA_STRING, DA_ALLOW_NULL, "add_sage_ref");
 		$this->addColumn("phone", DA_STRING, DA_ALLOW_NULL, "add_phone");
 		$this->addColumn("maxTravelHours", DA_INTEGER, DA_ALLOW_NULL, "add_max_travel_hours");
    $this->addColumn("activeFlag", DA_YN, DA_ALLOW_NULL, "add_active_flag");
 		//$this->setPK(1);		// NOTE: This is not really the PK, just the second element
 		$this->setAddColumnsOff();
 		$this->setNewRowValue(-9);		// This allows for fact that first siteNo is zero. Used in DataAccess->replicate()
 	}
	/**
	* Get string to be used as WHERE statement for update/get/delete statements.
	* @access public
	* @return string Where clause for update statements
	*/
	function getPKWhere(){
		return(
			$this->getDBColumnName('customerID').'='.$this->getFormattedValue('customerID').
				' AND '.$this->getDBColumnName('siteNo').'='.$this->getFormattedValue('siteNo')
		);
	}
	/**
	* Allocates the next site number for this customer
	* @access private
	* @param  void
	* @return integer Next Siteno
	*/
	function getNextPKValue(){
		$this->setQueryString(
			'SELECT MAX('.$this->getDBColumnName('siteNo').') + 1 FROM '. $this->getTableName().
			' WHERE '. $this->getDBColumnName('customerID').'='.$this->getFormattedValue('customerID')
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$siteNo=$this->getDBColumnValue(0);
			}
		}
		if ($siteNo==null){
			$siteNo = '0';
		}
		$this->resetQueryString();
		return $siteNo;
	}
	/**
	* Build and return string that can be used by update() function
	* @access private
	* @return string Update SQL statement
	*/
	function getUpdateString(){
		$colString="";
		for($ixCol=0;$ixCol<$this->colCount();$ixCol++){
			// exclude primary key columns
			if (($this->getName($ixCol)!='customerID')&($this->getName($ixCol)!='siteNo')){
				if ($colString!="") $colString=$colString . ",";
				$colString=$colString.$this->getDBColumnName($ixCol)."='".
					$this->prepareForSQL($this->getValue($ixCol))."'";
			}
		}
		return $colString;
	}
	/**
	* Return all rows from DB
	* @access public
	* @return bool Success
	*/
	function getRowsByCustomerID( $activeFlag = 'Y' ){
		$this->setMethodName("getRowsByCustomerID");
		if ($this->getValue('customerID') == ""){
			$this->raiseError('customerID not set');
		}
    $queryString = 
			'SELECT '.$this->getDBColumnNamesAsString().
					' FROM '.$this->getTableName().
					' WHERE '.$this->getDBColumnName('customerID').'='.$this->getFormattedValue('customerID');
          
    if ( $activeFlag == 'Y'){
      $queryString .= ' AND add_active_flag = "Y"';
    }

    $queryString .= ' ORDER BY '.$this->getDBColumnName('add1');
    
    $this->setQueryString( $queryString );
    
		return(parent::getRows());
	}
	/**
	* Return row by customerid and site no
	* @access public
	* @return bool Success
	*/
	function getRowByCustomerIDSiteNo(){
		$this->setMethodName("getRowByCustomerIDSiteNo");
		if ($this->getValue('customerID') == ""){
			$this->raiseError('customerID not set');
		}
		$this->setQueryString(
			'SELECT '.$this->getDBColumnNamesAsString().
					' FROM '.$this->getTableName().
					' WHERE '.$this->getDBColumnName('customerID').'='.$this->getFormattedValue('customerID').
					' AND '.$this->getDBColumnName('siteNo').'='.$this->getFormattedValue('siteNo')
		);
		return(parent::getRow());
	}
  function getRowByPostcode( $customerID, $postcode ){
    $this->setMethodName("getRowByPostcode");
    $this->setQueryString(
      'SELECT '.$this->getDBColumnNamesAsString().
          ' FROM '.$this->getTableName().
          ' WHERE '.$this->getDBColumnName('customerID') . '="' . $customerID . '"' .
          ' AND '.$this->getDBColumnName('postcode').'="'.$postcode . '"'
    );
    return(parent::getRow());
  }
	
	/**
	* Test for unique Sage Ref
	* @access public
	* @return bool Success
	*/
	function uniqueSageRef($sageRef)
	{
		$this->setMethodName("uniqueSageRef");
		$this->setQueryString(
		  "SELECT COUNT(*)".
	    " FROM ".$this->getTableName().
    	" WHERE ".$this->getDBColumnName('sageRef')." = '". $sageRef."'"
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$count=$this->getDBColumnValue(0);
			}
		}
		if ($count==0){
			$ret = TRUE;
		}
		else{
			$ret = FALSE;
		}
		$this->resetQueryString();
		return $ret;
	}
}
/**
* table join to contact for name
*/
class DBEJSite extends DBESite {
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJSite(&$owner){
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
		$this->addColumn("invContactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(icontact.con_first_name,' ',icontact.con_last_name)");
		$this->addColumn("delContactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(dcontact.con_first_name,' ',dcontact.con_last_name)");
 		$this->setAddColumnsOff();
	}
	/**
	* Get rows by description match
	* @access public
	* @return bool Success
	*/
	function getRowsByDescMatch($desc, $activeFlag = 'Y' ){
		$this->setMethodName("getRowsByDescMatch");
		$ret=FALSE;

    $queryString =
				"SELECT ".$this->getDBColumnNamesAsString().
	     			" FROM ".$this->getTableName().
	     			" LEFT JOIN contact AS icontact".
						" ON ".$this->getDBColumnName('invContactID'). " = icontact.con_contno".
	     			" LEFT JOIN contact AS dcontact".
						" ON ".$this->getDBColumnName('delContactID'). " = dcontact.con_contno".
						" WHERE (".$this->getDBColumnName('add1')." LIKE '%".$desc."%'".
						" OR ".$this->getDBColumnName('town')." LIKE '%".$desc."%'".
						" OR ".$this->getDBColumnName('postcode')." LIKE '%".$desc."%')".
						" AND ".$this->getDBColumnName('customerID')."=".$this->getFormattedValue('customerID');

    if ( $activeFlag == 'Y'){
      $queryString .= ' AND add_active_flag = "Y"';
    }

    $queryString .= ' ORDER BY ' . $this->getDBColumnName('add1');
    
    $this->setQueryString( $queryString );

		$ret=(parent::getRows());

		return $ret;
	}
	function getRow(){
		$this->setMethodName("getRow");
		$this->setQueryString(
				"SELECT ".$this->getDBColumnNamesAsString().
	     			" FROM ".$this->getTableName().
	     			" LEFT JOIN contact AS icontact".
						" ON ".$this->getDBColumnName('invContactID'). " = icontact.con_contno".
	     			" LEFT JOIN contact AS dcontact".
						" ON ".$this->getDBColumnName('delContactID'). " = dcontact.con_contno".
						" WHERE ".$this->getDBColumnName('customerID')."=".$this->getFormattedValue('customerID').
						" AND ".$this->getDBColumnName('siteNo')."=".$this->getFormattedValue('siteNo')
		);
		$ret=(parent::getRow());
		return $ret;
	}
	function getRowsByColumn($column, $activeFlag = 'Y' ){
		$this->setMethodName("getRowsByColumn");

    $queryString = 
				"SELECT ".$this->getDBColumnNamesAsString().
	     			" FROM ".$this->getTableName().
	     			" LEFT JOIN contact AS icontact".
						" ON ".$this->getDBColumnName('invContactID'). " = icontact.con_contno".
	     			" LEFT JOIN contact AS dcontact".
						" ON ".$this->getDBColumnName('delContactID'). " = dcontact.con_contno".
						" WHERE ".$this->getDBColumnName($column)."=".$this->getFormattedValue('customerID');
            
    if ( $activeFlag == 'Y'){
      $queryString .= ' AND add_active_flag = "Y"';
    }

    $this->setQueryString( $queryString	);

		$ret=(parent::getRows());
		return $ret;
	}
  
}
?>