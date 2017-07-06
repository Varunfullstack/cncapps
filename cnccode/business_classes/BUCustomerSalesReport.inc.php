<?php
/**
* Customer Sales report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BUCustomerSalesReport extends Business{

	/**
	* Constructor
	* @access Public
	*/
	function BUCustomerSalesReport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		
		$this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}
	
	function getSalesByCustomer( $endDate )
	{

		$startDate = $this->dateSub( $endDate, '12 MONTHS' ); 
		
		$sql = "SELECT	cus_name AS Customer";
	
		for ($month = 1; $month <= 12; $month++ ){
			
			$sql .= $this->buildSalesByCustomerSegment( $month );
		
		}

		$sql .=
			"
			FROM
				ordline
				JOIN ordhead ON odh_ordno = odl_ordno
				JOIN customer ON cus_custno = odh_custno
			WHERE
				odh_date BETWEEN '$startDate' AND '$endDate'
			GROUP BY
				odh_custno";
			
		return $this->db->query( $sql )->fetch_object()->salesReport;
		
	}
	function dateSub( $date, $interval )
	{
		$sql .=
			"SELECT DATE_SUB( $months, INTERVAL $interval ) AS startDate";
			
		return $this->db->query( $sql )->fetch_object()->startDate;
		
	}
	function buildSalesByCustomerSegment( $month )
	{
		$return =
				"
				,SUM(
					if (
						YEAR(odh_date) = YEAR(CURDATE())
						AND MONTH( odh_date ) = $month,
						odl_qty_ord * odl_e_unit,
						0
					)
				)as `month_$month_sales`,
			
				SUM(
					if (
						YEAR(odh_date) = YEAR(CURDATE())
						AND MONTH( odh_date ) = $month,
						odl_qty_ord * odl_d_unit - odl_qty_ord * odl_e_unit,
						0
					)
				)as `month_$month_profit`
				";
		return $return;
		
	}
}// End of class
?>