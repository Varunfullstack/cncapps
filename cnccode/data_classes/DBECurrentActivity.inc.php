<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBECurrentActivity extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBECurrentActivity(&$owner){
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
		$this->setTableName("callactivity");
 		$this->addColumn("callActivityID", DA_ID, DA_ALLOW_NULL, 'caa_callactivityno');
 		$this->addColumn("startTime", DA_TIME, DA_ALLOW_NULL, 'caa_starttime');
 		$this->addColumn("date", DA_DATE, DA_ALLOW_NULL, 'caa_date');
 		$this->addColumn("engineerName", DA_STRING, DA_ALLOW_NULL, 'cns_name');
 		$this->addColumn("activityType", DA_STRING, DA_ALLOW_NULL, 'cat_desc');
 		$this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, 'cus_name');
 		$this->addColumn("allowSCRFlag", DA_YN, DA_ALLOW_NULL);		
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}

	function getRows()
	{

 		$this->setMethodName('getRows');
		$statement=
			"SELECT caa_callactivityno, caa_starttime, caa_date, cns_name, cat_desc, cus_name, allowSCRFlag".
			" FROM ".$this->getTableName().
      " JOIN problem ON pro_problemno = caa_problemno " .
			" INNER JOIN customer ON problem.pro_custno = customer.cus_custno
			INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno
			LEFT JOIN callacttype ON callactivity.caa_callacttypeno = callacttype.cat_callacttypeno
			WHERE caa_endtime = ''
			AND caa_date <= NOW()
			ORDER BY cns_name";
		$this->setQueryString($statement);
		$ret=(parent::getRows());
		return $ret;
 	} 


}
?>