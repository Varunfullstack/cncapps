<?
/*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEProject extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEProject(&$owner){
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
		$this->setTableName("project");
 		$this->addColumn("projectID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
    $this->addColumn("notes", DA_MEMO, DA_ALLOW_NULL);
    $this->addColumn("startDate", DA_DATE, DA_NOT_NULL);
 		$this->addColumn("expiryDate", DA_DATE, DA_NOT_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
	function getRowsByCustomerID( $customerID, $activityDate = false )
	{
		$this->setMethodName("getRowsByCustomerID");
		if ($customerID==''){
			$this->raiseError('customerID not passed');
		}

	$queryString = 
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName().
			" WHERE ".$this->getDBColumnName('customerID')."='".mysql_escape_string($customerID)."' AND description != ''";
						
	if ( $activityDate ){
		$queryString .= 
			" AND ".$this->getDBColumnName('expiryDate') . ">= '$activityDate'";
	}		

		$this->setQueryString( $queryString );

		return($this->getRows());
	}

  function getCurrentProjects()
  {
    $queryString = 
        "SELECT 
          projectID,
          customerID,
          description,
          notes,
          startDate,
          expiryDate,
          cus_name as customerName
          
        FROM 
          project
          JOIN customer on cus_custno = project.customerID

        WHERE 
          expiryDate >= NOW()";

    $this->db->query( $queryString );
    
    $results = array();
    
    while( $row = $this->db->next_record() ){
      $results[] = $this->db->Record;
    }
    return $results;
  }
}
?>