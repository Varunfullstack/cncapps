<?php
/**
* Helpdesk report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BULeadStatus extends Business{

	/**
	* Constructor
	* @access Public
	*/
	function BULeadStatus(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		
		$this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}
	function getBecameCustomerCounts()
	{

    $valueArray = array();
    
    $stmt = $this->db->prepare("
      select
        count(*)
        from customer
        where YEAR(cus_became_customer_date) = ?"
    );
    
    $stmt->bind_param( 's', $year );    

    $yearNow = date( 'Y' );
    
    $yearsToInclude = 20;
    
    for ( $year = $yearNow ; $year >= ( $yearNow - $yearsToInclude ) ; $year--){
      
      $stmt->execute();
      
      $stmt->bind_result( $count );
      
      $stmt->fetch();
    
      $valueArray[$year] = $count;
        
    }
			
		return $valueArray;
    

	}

  function getDroppedCustomerCounts()
  {

    $valueArray = array();
    
    $stmt = $this->db->prepare("
      select
        count(*)
        from customer
        where YEAR(cus_dropped_customer_date) = ?"
    );
    
    $stmt->bind_param( 's', $year );    

    $yearNow = date( 'Y' );
    
    $yearsToInclude = 20;
    
    for ( $year = $yearNow ; $year >= ( $yearNow - $yearsToInclude ) ; $year--){
      
      $stmt->execute();
      
      $stmt->bind_result( $count );
      
      $stmt->fetch();
    
      $valueArray[$year] = $count;
        
    }
      
    return $valueArray;
    

  }
  function getLeadsByStatus( $leadStatusID, $orderAlpha = false )
  {
    if ( !$orderAlpha ){
      $orderByColumn = 'cus_became_customer_date';
    }
    else{
      $orderByColumn = 'cus_name';
    }
    
    $sql = "
      select
        cus_custno as customerID,
        cus_name  as customerName
        from customer
        where cus_leadstatusno = $leadStatusID
      ORDER BY $orderByColumn";

    return $this->db->query( $sql );
      
  }
	
}// End of class
?>