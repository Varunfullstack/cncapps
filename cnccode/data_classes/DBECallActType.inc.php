<?
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBECallActType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBECallActType(&$owner){
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
		$this->setTableName("callacttype");
 		$this->addColumn("callActTypeID", DA_ID, DA_NOT_NULL, "cat_callacttypeno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "cat_desc");
 		$this->addColumn("oohMultiplier", DA_FLOAT, DA_ALLOW_NULL, "cat_ooh_multiplier");
 		$this->addColumn("itemID", DA_INTEGER, DA_ALLOW_NULL, "cat_itemno");
 		$this->addColumn("maxHours", DA_FLOAT, DA_ALLOW_NULL, "cat_max_hours");		
 		$this->addColumn("minHours", DA_FLOAT, DA_ALLOW_NULL, "cat_min_hours");		
 		$this->addColumn("customerEmailFlag", DA_YN, DA_NOT_NULL);// send emails to customers		
 		$this->addColumn("requireCheckFlag", DA_YN, DA_NOT_NULL, "cat_req_check_flag");		// rquires checking before sales order
 		$this->addColumn("allowExpensesFlag", DA_YN, DA_NOT_NULL, "cat_allow_exp_flag");	// allow expenses against this type		
 		$this->addColumn("allowReasonFlag", DA_YN, DA_NOT_NULL, "cat_problem_flag");				// allow problem notes		
 		$this->addColumn("allowActionFlag", DA_YN, DA_NOT_NULL, "cat_action_flag");		
 		$this->addColumn("allowFinalStatusFlag", DA_YN, DA_NOT_NULL, "cat_resolve_flag");		
 		$this->addColumn("reqReasonFlag", DA_YN, DA_NOT_NULL, "cat_r_problem_flag");	// whether these notepads are required
 		$this->addColumn("reqActionFlag", DA_YN, DA_NOT_NULL, "cat_r_action_flag");		
 		$this->addColumn("reqFinalStatusFlag", DA_YN, DA_NOT_NULL, "cat_r_resolve_flag");		
 		$this->addColumn("allowSCRFlag", DA_YN, DA_NOT_NULL);
 		$this->addColumn("curValueFlag", DA_YN, DA_NOT_NULL);														// is this activity type a currency value
 		$this->addColumn("travelFlag", DA_YN, DA_NOT_NULL);			// is this a travel activity?
 		$this->addColumn("activeFlag", DA_YN, DA_NOT_NULL);			// is	this an active activity?
		$this->addColumn("showNotChargeableFlag", DA_YN, DA_NOT_NULL);			// show charagable text on activity emails?
		$this->addColumn("engineerOvertimeFlag", DA_YN, DA_NOT_NULL);			// Allow engineer overtime
    $this->addColumn("onSiteFlag", DA_YN, DA_NOT_NULL, "cat_on_site_flag");    
    $this->addColumn("portalDisplayFlag", DA_YN, DA_NOT_NULL, "cat_portal_display_flag");    
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
class DBEJCallActType extends DBECallActType{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJCallActType(&$owner){
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
 		$this->addColumn("itemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");		// linked item
    $this->addColumn("itemSalePrice", DA_STRING, DA_NOT_NULL, "itm_sstk_price");
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
	function getRow( $pkValue = false ){
    
    if ( $pkValue ){
      $this->setPKValue( $pkValue );
    }
    
		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" LEFT JOIN item ON itm_itemno = cat_itemno".
			" WHERE ". $this->getPKWhere();
		$this->setQueryString($statement);
		$ret=(parent::getRow());
	}
	function getActiveRows(){
		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" LEFT JOIN item ON itm_itemno = cat_itemno".
			" WHERE activeFlag = 'Y'".
			" ORDER BY cat_desc";
		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
}
?>