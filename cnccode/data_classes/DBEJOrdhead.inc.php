<?php /*
* Ordhead table join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEOrdhead.inc.php");
class DBEJOrdhead extends DBEOrdhead{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function __construct(&$owner){
		$this->constructor($owner);
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::__construct($owner);
 		$this->setAddColumnsOn();
 		$this->addColumn("customerName", DA_STRING, DA_NOT_NULL, 'cus_name');
 		$this->addColumn("contract", DA_STRING, DA_ALLOW_NULL, 'itm_desc');
 		$this->addColumn("customerItemID", DA_INTEGER, DA_ALLOW_NULL, 'cui_cuino');
 		$this->setAddColumnsOff();
 	}
	/**
	* Get rows by operative and date
	* @access public
	* @return bool Success
	*/
	function getRowsBySearchCriteria(
		$customerID,
		$orderType,
		$custPORef,
		$lineText,
		$fromDate,
		$toDate,
    $userID
	){
		$this->setMethodName("getRowsBySearchCriteria");
		if ($lineText!=''){
			$statement=
				"SELECT DISTINCT ".$this->getDBColumnNamesAsString().
				" FROM ".$this->getTableName().
				" JOIN ordline ON ".$this->getTableName().".".$this->getDBColumnName('ordheadID').
					"= ordline.odl_ordno".
				" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID').
					"= customer.cus_custno";
		}
		else{
			$statement=
				"SELECT ".$this->getDBColumnNamesAsString().
				" FROM ".$this->getTableName().
				" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID').
					"= customer.cus_custno";
		}
		$statement .=
			" LEFT JOIN custitem ON renewalOrdheadID = odh_ordno".
			" LEFT JOIN item ON cui_itemno = itm_itemno";

		$statement=$statement." WHERE 1=1";
		if ($customerID!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('customerID')."=".$customerID;
		}
    if ($userID!=''){
      $statement=$statement.
        " AND (
            SELECT
              COUNT(*)
            FROM
              quotation
            WHERE
              ordheadID = ordhead.odh_ordno
              AND userID = ".$userID .
            " ) > 0";
    }
		switch ($orderType){
			case 'B':
				$statement = $statement.
					" AND ".$this->getDBColumnName('type')." IN('I','P')";
				break;
			case '':											// all types
				break;
			default:											
				$statement = $statement.
				" AND ".$this->getDBColumnName('type')."='".mysql_escape_string($orderType)."'";
				break;
		}
		if ($lineText!=''){
			$statement=$statement.
		
					" AND ( MATCH (ordline.odl_desc)
					AGAINST ('" . mysql_escape_string($lineText) .	"' IN BOOLEAN MODE)";

			$statement=$statement.
					" OR MATCH (item.notes, item.itm_unit_of_sale)
					AGAINST ('" . mysql_escape_string($lineText) .	"' IN BOOLEAN MODE) )";
		}
		if ($custPORef!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('custPORef')." LIKE '%".mysql_escape_string($custPORef)."%'";
		}
		if ($fromDate!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('date').">='".mysql_escape_string($fromDate)."'";
		}
		if ($toDate!=''){
			$statement=$statement.
				" AND ".$this->getDBColumnName('date')."<='".mysql_escape_string($toDate)."'";
		}
		$statement=$statement." ORDER BY ".$this->getDBColumnName('date')." DESC";
		$statement=$statement." LIMIT 0,200";

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
			" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID')."= customer.cus_custno".
			" LEFT JOIN custitem ON renewalOrdheadID = odh_ordno".
			" LEFT JOIN item ON cui_itemno = itm_itemno".
			" WHERE ".$this->getPKWhere()
		);
		return (parent::getRow());
	}
	function getDespatchRowByOrdheadID($ordheadID){
		$this->setMethodName("getDespatchRowByOrdheadID");
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID').
				"= customer.cus_custno".
			" LEFT JOIN custitem ON renewalOrdheadID = odh_ordno".
			" LEFT JOIN item ON cui_itemno = itm_itemno".
			" WHERE ".$this->getDBColumnName('ordheadID')."=".$ordheadID.
			" AND ".$this->getDBColumnName('type')." IN('I','P')".
			" AND ".$this->getDBColumnName('customerID')." NOT IN(".
			CONFIG_ASSET_STOCK_CUSTOMERID . ",".
			CONFIG_MAINT_STOCK_CUSTOMERID . ",".
			CONFIG_SALES_STOCK_CUSTOMERID . ",".
			CONFIG_OPERATING_STOCK_CUSTOMERID . ")"
		);			
		return (parent::getRow());
	}
	function getDespatchRows($customerID = ''){
		$this->setMethodName("getDespatchRows");
		$ret=FALSE;
		$queryString =
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN customer ON ".$this->getTableName().".".$this->getDBColumnName('customerID').
				"= customer.cus_custno".
			" LEFT JOIN custitem ON renewalOrdheadID = odh_ordno".
			" LEFT JOIN item ON cui_itemno = itm_itemno".
			" WHERE 1=1";
		if ($customerID != ''){
			$queryString .=
				" AND ".$this->getDBColumnName('customerID')."=".$customerID;
		}
		$queryString .=
			" AND ".$this->getDBColumnName('type')." IN('I','P')".
			" AND ".$this->getDBColumnName('customerID')." NOT IN(".
			CONFIG_ASSET_STOCK_CUSTOMERID . ",".
			CONFIG_MAINT_STOCK_CUSTOMERID . ",".
			CONFIG_SALES_STOCK_CUSTOMERID . ",".
			CONFIG_OPERATING_STOCK_CUSTOMERID . ")";
		$this->setQueryString($queryString);			
		return (parent::getRows());
	}
}
?>