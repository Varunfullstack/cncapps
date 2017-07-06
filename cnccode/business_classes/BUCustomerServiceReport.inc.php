<?php
/**
* Customer service business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_gc"]."/Controller.inc.php");
require_once($cfg["path_dbe"]."/DBECustomerServiceReport.inc.php");

class BUCustomerServiceReport extends Business{
	/**
	* Constructor
	* @access Public
	*/
	function BUCustomerServiceReport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeCustomerServiceReport=new DBECustomerServiceReport($this);

	}
	function initialiseSearchForm(&$dsData)
	{
		$dsData = new DSForm($this);
		$dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
		$dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
	}
	function search( &$dsSearchForm )
	{

		$this->dbeCustomerServiceReport->getRowsBySearchCriteria(
			trim($dsSearchForm->getValue('fromDate')),
			trim($dsSearchForm->getValue('toDate'))
		);

		return $this->dbeCustomerServiceReport;	
	}

}// End of class
?>