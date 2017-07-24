<?php /*
* Renewal quotation table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBECustomerItem.inc.php");
class DBEJRenQuotation extends DBECustomerItem{
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
 		$this->addColumn("customerName", DA_STRING, DA_NOT_NULL, "cus_name");
    $this->addColumn("siteName", DA_STRING, DA_NOT_NULL, "CONCAT(add_add1, ' ', add_town, ' ' , add_postcode)");
 		$this->addColumn("itemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");
    $this->addColumn("itemTypeDescription", DA_STRING, DA_NOT_NULL, "ity_desc");
    $this->addColumn("itemID", DA_ID, DA_NOT_NULL, "itm_itemno");
 		$this->addColumn("type", DA_STRING, DA_NOT_NULL, "renQuotationType.description");
 		$this->addColumn("addInstallationCharge", DA_YN, DA_NOT_NULL);
     $this->addColumn("nextPeriodStartDateYMD", DA_DATE, DA_NOT_NULL,
       "DATE_FORMAT( DATE_ADD(`startDate`, INTERVAL 1 YEAR ), '%Y-%m-%d') as nextPeriodStartDateYMD");
 		$this->addColumn("nextPeriodStartDate", DA_DATE, DA_NOT_NULL,
 			"DATE_FORMAT( DATE_ADD(`startDate`, INTERVAL 1 YEAR ), '%d/%m/%Y')");
     $this->addColumn("nextPeriodEndDateYMD", DA_DATE, DA_NOT_NULL,
       "DATE_FORMAT(         
           DATE_ADD(`startDate`, INTERVAL 2 YEAR )
           , '%Y-%m-%d') as nextPeriodEndDateYMD");
 		$this->addColumn("nextPeriodEndDate", DA_DATE, DA_NOT_NULL,
 			"DATE_FORMAT( 				
 					DATE_ADD(`startDate`, INTERVAL 2 YEAR )
 					, '%d/%m/%Y')");
 		
 		$this->setAddColumnsOff();
	}
	
  function addYearToStartDate( $customerItemID )
  {
    $statement=
      "
      UPDATE ".$this->getTableName().
      " SET startDate = DATE_ADD( `startDate`, INTERVAL 1 YEAR ),
      dateGenerated = '0000-00-00'
        WHERE cui_cuino = $customerItemID;";

    $this->setQueryString($statement);
    return  $this->runQuery();
    
  }

	function getRow(){
		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" 	JOIN item ON  itm_itemno = cui_itemno
          JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
				  JOIN customer ON  cus_custno = cui_custno
          JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
				  LEFT JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
				WHERE ". $this->getPKWhere();

			
		$this->setQueryString($statement);
		$ret=(parent::getRow());
	}
	function getRows( $orderBy = false ){

		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN item ON  itm_itemno = cui_itemno
        JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			  JOIN customer ON  cus_custno = cui_custno
        JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			LEFT JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE
        declinedFlag = 'N'
        AND renewalTypeID = 3";
      
    if ( $orderBy ){
      $statement .= " ORDER BY $orderBy";
    }
    else{
      $statement .= " ORDER BY cus_name";
      
    }
		
		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
	function getRowsByCustomerID( $customerID ){

		$statement=
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE declinedFlag = 'N'
				AND cui_custno = $customerID		
        AND renewalTypeID = 3
			ORDER BY cus_name";
		
		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
	/**
	 * Get all renewals due in 1 months time
	 * 
	 * i.e. Start date plus 11 months
	 * 
	 */
	function getRenewalsDueRows(){

		$statement=
			"
			SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE
        CURDATE() >= ( DATE_ADD(`startDate`, INTERVAL 11 MONTH) )
			  AND dateGenerated = '0000-00-00' AND dateGenerated IS NOT NULL
		    AND declinedFlag = 'N'
        AND renewalTypeID = 3
		 ORDER BY cui_custno";

		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
  /**
   * Get all renewals in the passed array of IDs
   * 
   */
  function getRenewalsByIDList( $customerItemIDs ){

    $commaListOfIDs = implode( ',', $customerItemIDs );
    
    $statement=
      "
      SELECT ".$this->getDBColumnNamesAsString().
      " FROM ".$this->getTableName().
      " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
      JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
      JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
      WHERE cui_cuino IN ( ". $commaListOfIDs ." )
        AND renewalTypeID = 3
      ORDER BY cui_custno
     ";

    $this->setQueryString($statement);
    $ret=(parent::getRows());
  }
	/**
	 * Get all renewals where quote has been generated last 2 weeks
	 * 
	 * i.e. Start date plus 11 months
	 * 
	 */
	function getRecentQuotesRows(){

		$statement=
			"
			SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE dateGenerated > DATE_SUB( CURDATE(), INTERVAL 2 WEEK )
		 AND declinedFlag = 'N'
     AND renewalTypeID = 3
		 ORDER BY cui_custno
		 ";

		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
	
}
?>