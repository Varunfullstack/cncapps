<?
/*
* ExternalItem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEExternalItem.inc.php");
class DBEJExternalItem extends DBEExternalItem{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJExternalItem(&$owner){
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
    $this->addColumn("itemTypeDescription", DA_STRING, DA_ALLOW_NULL, 'itemtype.ity_desc' );
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
      " JOIN itemtype ON itemtype.ity_itemtypeno = externalitem.itemTypeID
			WHERE ".$this->getDBColumnName('customerID')."='".mysql_escape_string($customerID)."'";			

		$this->setQueryString( $queryString );

    return (parent::getRows());
	}

}
?>