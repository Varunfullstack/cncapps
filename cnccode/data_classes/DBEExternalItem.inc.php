<?
/*
* ExternalItem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEExternalItem extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEExternalItem(&$owner){
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
		$this->setTableName("externalitem");
 		$this->addColumn("externalItemID", DA_ID, DA_NOT_NULL);
    $this->addColumn("itemTypeID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
    $this->addColumn("notes", DA_MEMO, DA_ALLOW_NULL);
    $this->addColumn("licenceRenewalDate", DA_DATE, DA_ALLOW_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
	function getRowsByCustomerID( $customerID )
	{
		$this->setMethodName("getRowsByCustomerID");
		if ($customerID==''){
			$this->raiseError('customerID not passed');
		}

	$queryString = 
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('customerID')."='".mysql_escape_string($customerID);			

		$this->setQueryString( $queryString );

		return($this->getRows());
	}

}
?>