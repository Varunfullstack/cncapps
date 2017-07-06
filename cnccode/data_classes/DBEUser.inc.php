<?php
/*
* User table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEUser extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEUser(&$owner){
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
		$this->setTableName("consultant");
 		$this->addColumn("userID", DA_ID, DA_NOT_NULL, "cns_consno");
 		$this->addColumn("managerID", DA_ID, DA_ALLOW_NULL, "cns_manager");
 		$this->addColumn("name", DA_STRING, DA_NOT_NULL, "cns_name");
 		$this->addColumn("salutation", DA_STRING, DA_ALLOW_NULL, "cns_salutation");
 		$this->addColumn("add1", DA_STRING, DA_ALLOW_NULL, "cns_add1");
 		$this->addColumn("add2", DA_STRING, DA_ALLOW_NULL, "cns_add2");
 		$this->addColumn("add3", DA_STRING, DA_ALLOW_NULL, "cns_add3");
 		$this->addColumn("town", DA_STRING, DA_ALLOW_NULL, "cns_town");
 		$this->addColumn("county", DA_STRING, DA_ALLOW_NULL, "cns_county");
 		$this->addColumn("postcode", DA_STRING, DA_ALLOW_NULL, "cns_postcode");
 		$this->addColumn("username", DA_STRING, DA_NOT_NULL, "cns_logname");
 		$this->addColumn("employeeNo", DA_STRING, DA_ALLOW_NULL, "cns_employee_no");
 		$this->addColumn("petrolRate", DA_FLOAT, DA_ALLOW_NULL, "cns_petrol_rate");
 		$this->addColumn("perms", DA_STRING, DA_ALLOW_NULL, "cns_perms");
 		$this->addColumn("signatureFilename", DA_STRING, DA_ALLOW_NULL);
 		$this->addColumn("jobTitle", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("firstName", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("lastName", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("activeFlag", DA_YN, DA_NOT_NULL, 'consultant.activeFlag');
 		$this->addColumn("weekdayOvertimeFlag", DA_YN, DA_NOT_NULL); // does user get overtime in weekdays
    $this->addColumn("helpdeskFlag", DA_YN, DA_NOT_NULL, 'cns_helpdesk_flag'); // does user get overtime in weekdays
 		$this->addColumn("customerID", DA_ID, DA_ALLOW_NULL);
    $this->addColumn("hourlyPayRate", DA_FLOAT, DA_ALLOW_NULL, "cns_hourly_pay_rate");
    $this->addColumn("teamID", DA_ID, DA_ALLOW_NULL, 'consultant.teamID');
    $this->addColumn("receiveSdManagerEmailFlag", DA_YN, DA_NOT_NULL);  
    $this->addColumn("changePriorityFlag", DA_YN, DA_NOT_NULL);
    $this->addColumn("appearInQueueFlag", DA_YN, DA_NOT_NULL);
    $this->addColumn("standardDayHours", DA_FLOAT, DA_NOT_NULL);
    $this->addColumn("changeApproverFlag", DA_YN, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	function getRows( $activeOnly = true ){

		$this->setMethodName("getRows");

		$queryString =
			"SELECT ".$this->getDBColumnNamesAsString().
        " FROM ".$this->getTableName() .
        " JOIN team ON team.teamID = consultant.teamID";

		if ( $activeOnly ){
			$queryString .=	' WHERE consultant.activeFlag = "Y"';
		}
		
    $queryString .=  ' ORDER BY firstName, lastName';

		$this->setQueryString( $queryString );
    		
		return(parent::getRows());
	}
	function getRowsInGroup( $group ){
		
		$this->setMethodName("getRowsInGroup");
		
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
					" FROM ".$this->getTableName() .
					" WHERE activeFlag = 'Y'" .
					" AND cns_perms LIKE '%" . $group . "%'"
					
				);
        
		return(parent::getRows());
	}
}
?>