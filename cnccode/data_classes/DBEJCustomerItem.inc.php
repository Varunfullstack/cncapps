<?php /*
* Customer Item join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBECustomerItem.inc.php");
class DBEJCustomerItem extends DBECustomerItem{
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
 		$this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
 		$this->addColumn("siteDescription", DA_STRING, DA_ALLOW_NULL, "CONCAT_WS(', ', add_add1, add_town, add_postcode)");
// 		$this->addColumn("contractDescription", DA_STRING, DA_ALLOW_NULL, "contractitem.itm_desc");
    $this->addColumn("contractItemTypeID", DA_ID, DA_ALLOW_NULL, "citem.itm_itemtypeno");
 		$this->addColumn("itemDescription", DA_STRING, DA_ALLOW_NULL, "citem.itm_desc");
    $this->addColumn("itemNotes", DA_STRING, DA_ALLOW_NULL, "citem.notes");
    $this->addColumn("renewalTypeID", DA_ID, DA_ALLOW_NULL, "citem.renewalTypeID");
 		$this->addColumn("partNo", DA_STRING, DA_ALLOW_NULL, "citem.itm_unit_of_sale");
 		$this->addColumn("servercareFlag", DA_INTEGER, DA_ALLOW_NULL, "citem.itm_servercare_flag");
    $this->addColumn("invoiceFromDate", DA_DATE, DA_NOT_NULL,
      "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%d/%m/%Y')");
    $this->addColumn("invoiceToDate", DA_DATE, DA_NOT_NULL, "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%d/%m/%Y')");

    $this->addColumn("invoiceFromDateYMD", DA_DATE, DA_NOT_NULL,
      "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%Y-%m-%d') as invoiceFromDateYMD");

    $this->addColumn("invoiceToDateYMD", DA_DATE, DA_NOT_NULL,
      "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%Y-%m-%d') as invoiceToDateYMD");

 		$this->setAddColumnsOff();
 	}
 	function getRowsBySearchCriteria(
 		$customerID,
 		$ordheadID,					// sales order no
// 		$contractID,				// customer contract ID @todo many-to-many
		$startDate,
		$endDate,
		$itemText,
    $contractText,
		$serialNo,
		$renewalStatus,
		$row_limit = 1000
	){
    /*
    @todo: update for many-to-many
    */
 		$this->setMethodName('getRowsBySearchCriteria');
		$queryString =
			"SELECT ".$this->getDBColumnNamesAsString().
 			" FROM ".$this->getTableName().
 			" JOIN item AS citem ON cui_itemno = itm_itemno".
 			" JOIN customer ON cui_custno = cus_custno".
 			" JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno".
 			" WHERE 1=1";
		if ($customerID != ''){
			$queryString .= " AND " . $this->getDBColumnName('customerID') . "=" . $customerID;
		}
		if ($ordheadID != ''){
			$queryString .= " AND " . $this->getDBColumnName('ordheadID') . "=" . $ordheadID;
		}
		if ($contractID != ''){
			$queryString .= " AND " . $this->getDBColumnName('contractID') . "=" . $contractID;
		}
		if ($startDate != ''){
			$queryString .= " AND " . $this->getDBColumnName('expiryDate') . ">= '" . mysql_escape_string($startDate) . "'";
		}
		if ($endDate != ''){
			$queryString .= " AND " . $this->getDBColumnName('expiryDate') . "<= '" . mysql_escape_string($endDate). "'";
		}
		if ($serialNo != ''){
			$queryString .=	" AND ".$this->getDBColumnName('serialNo')." LIKE '%".mysql_escape_string($serialNo)."%'";
		}
		if ($renewalStatus != ''){
			$queryString .=	" AND ".$this->getDBColumnName('renewalStatus') . "='" . mysql_escape_string($renewalStatus) . "'";
		}
		if ($itemText != ''){
			$queryString .=	" AND citem.itm_desc LIKE '%".mysql_escape_string($itemText)."%'";
		}
    
    /*
    If searching on contract text, need to sub-query to match item descriptions
    on custitem_contract
    */
    if ($contractText != ''){
      $queryString .=
        " AND (
            SELECT
              COUNT(*)
            FROM
              custitem_contract
              JOIN custitem AS contract ON cic_contractcuino = contract.`cui_cuino`
              JOIN item ON contract.cui_itemno = itm_itemno
            WHERE
              itm_desc LIKE '%" .mysql_escape_string($contractText). "%'
              AND cic_cuino = custitem.`cui_cuino`
            ) > 0";
    }

		if ( $row_limit ){
			$queryString .=	" LIMIT 0," . $row_limit;
		}                

 		$this->setQueryString($queryString);
    
 		return (parent::getRows());
 	}
 	function getRow($ID)
	{
 		$this->setMethodName('getRow');
		$queryString =
			"SELECT ".$this->getDBColumnNamesAsString().
 			" FROM ".$this->getTableName().
      " JOIN item AS citem ON cui_itemno = itm_itemno".
 			" JOIN customer ON cui_custno = cus_custno".
 			" JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno".
//      " LEFT JOIN custitem AS contract ON custitem.cui_contract_cuino = contract.cui_cuino".
//      " LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno".
			" WHERE " . $this->getDBColumnName('customerItemID') . "=" . $ID;

		$this->setQueryString($queryString);
 		return (parent::getRow());
 	}
	function getRowsByColumn($column, $sortColumn=''){
		$this->setMethodName("getRowsByColumn");
		if ($column==''){
			$this->raiseError('Column not passed');
			return FALSE;
		}
		$ixColumn=$this->columnExists($column);
		if ($ixColumn==DA_OUT_OF_RANGE){
			$this->raiseError("Column ". $column. " out of range");
			return DA_OUT_OF_RANGE;
		}
		$queryString = 
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
      " JOIN item AS citem ON cui_itemno = itm_itemno".
 			" JOIN customer ON cui_custno = cus_custno".
 			" JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno".
//      " LEFT JOIN custitem AS contract ON custitem.cui_contract_cuino = contract.cui_cuino".
//      " LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno".
			" WHERE ".$this->getDBColumnName($ixColumn)."=".$this->getFormattedValue($ixColumn);

		if ($sortColumn!=''){
			$ixSortColumn=$this->columnExists($sortColumn);
			if ($ixSortColumn==DA_OUT_OF_RANGE){
				$this->raiseError("Sort Column ". $column. " out of range");
				return DA_OUT_OF_RANGE;
			}
			else{
				$queryString.=" ORDER BY ".$this->getDBColumnName($ixSortColumn);
			}
		}

		$this->setQueryString($queryString);
		return($this->getRows());
	}
  function getItemsByContractID($customerItemId ){
    $queryString = 
      "SELECT ".
          $this->getDBColumnNamesAsString().
      " FROM
          custitem_contract
          JOIN custitem ON cic_cuino = cui_cuino 
          JOIN item AS citem ON cui_itemno = itm_itemno
          JOIN customer ON cui_custno = cus_custno
          JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
       WHERE
          cic_contractcuino = $customerItemId

       ORDER BY 
        itm_desc";

    $this->setQueryString($queryString);
    return($this->getRows());
  }
/**
 * Get a list of server rows for given customer
 *
 * @param integer $customerID
 * @return void
*/
 	function getServersByCustomerID()
 	{
	 	$this->setMethodName('getServersByCustomerID');
 		if ($this->getValue('customerID') == ''){
 			$this->raiseError('customerID not set');
 		}
 		$this->setQueryString(
 			"SELECT ". $this->getDBColumnNamesAsString().

 			" FROM ". $this->getTableName() .
          " JOIN item AS citem ON cui_itemno = itm_itemno
 			    JOIN customer ON cui_custno = cus_custno
 			    JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
          JOIN custitem_contract cic ON cic.cic_cuino = custitem.cui_cuino
          JOIN custitem con ON con.cui_cuino = cic.cic_contractcuino
          JOIN item con_item ON con.cui_itemno = con_item.itm_itemno
      
 		   WHERE ". $this->getDBColumnName('customerID') . "=" . $this->getValue('customerID') .
 		  " AND citem.itm_itemtypeno = " . CONFIG_SERVER_ITEMTYPEID .
      " AND con_item.itm_itemtypeno = " . CONFIG_SERVERCARE_ITEMTYPEID .
      " AND con.renewalStatus = 'R'" . 
      " AND " . $this->getDBColumnName('serverName') . " > ''
      ORDER BY citem.itm_desc, custitem.installationDate"
    
 		);
 		
 		return (parent::getRows());
	}
  function getContractDescriptionsByCustomerItemID( $customerItemID )
  {
    $db = new dbSweetcode();
    $select =
      "SELECT
        GROUP_CONCAT( i.itm_desc ) as contracts
      FROM
        custitem_contract cic
        JOIN custitem c ON cic_contractcuino = c.cui_cuino
        JOIN item i ON i.itm_itemno = c.cui_itemno
      WHERE
       cic_cuino = $customerItemID";
    $db->query( $select );
    $db->next_record();
    return $db->Record[ 'contracts' ];
  }
}
?>