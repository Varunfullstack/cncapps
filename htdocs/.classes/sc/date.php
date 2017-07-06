<?php
/**
* Date class
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES .		'object.php');
class SC_Date extends SC_Object {
	/*
	* function to mirror mysql date_add function
	*
	* @param	date		$date 		public static
	* @param	string	$interval	public static
	* @param	string	$unit			public static
	* @return	string	new date in MySql format
	* @access	public static
	*/
	function dateAdd($date, $interval, $unit='DAY') {
		return SC_Date::__getDbResult("SELECT DATE_ADD('$date', INTERVAL $interval $unit) AS result");
	}
	/*
	* function to mirror mysql date_sub function
	*
	* @param	date		$date 		public static
	* @param	string	$interval	public static
	* @param	string	$unit			public static
	* @return	string	new date in MySql format
	* @access	public static
	*/
	function dateSub($date,$interval, $unit='DAY') {
		return SC_Date::__getDbResult("SELECT DATE_SUB('$date', INTERVAL $interval $unit) AS result");
	}
	function __getDbResult($SQL){
		global $connection;
		if ($stmt = $connection->execute($SQL)) {
			$result = $stmt->fetchAssoc();
			return $result['result'];
		}
		else {
			die ("could not execute query");
		}
	}
	/**
	*	isMysqlDate
	*
	* >>> NOT PROPERLY
	*
	* is it YYYY-MM-DD (ISO 8601 based)
	*	@param	string $date Date string
	*	@return boolean success
	*/
	function isMysqlDate($isodate, $min=false, $max=false) {
		return SC_Date::isMySQLDatetime($isodate, $min=false, $max=false);
	}
	/**
	*	isMysqlDatetime
	*
	* >>> NOT IMPLEMETED YET!!!!! <<<<<<
	*
	* is it YYYY-MM-DD (ISO 8601 based)
	*	@param	string $date Date string
	*	@return boolean success
	*/
	function isMysqlDatetime($isodate, $min=false, $max=false) {
		if (
			preg_match(
				"/^([123456789][[:digit:]]{3})-(0[1-9]|1[012])-(0[1-9]|[12][[:digit:]]|3[01])$/",
				$isodate,
				 $date_part
			) &&
			checkdate($date_part[2], $date_part[3], $date_part[1])
		){
			return true;
		}
		else
		{	
			return false;
		}
	}
	/**
	*	isUKDate
	*
	* is it DD/MM/YYYY or D/M/YYYY (or - instead of /)
	*	@param	string $date Date string
	*	@Íreturn boolean valid date
	*/
	function isUKDate($date, $min=false, $max=false) {
		return(
			ereg ('([0-9]{1,2})[-/]([0-9]{1,2})[-/]([0-9]{4})', $date, $parts) &&
			checkdate( (int)$parts[2],  (int)$parts[1], (int)$parts[3] )
		);
	}

	function isDate( $string )
	{
		if ( SC_Date::isUKDate( $string ) || SC_Date::isMySQLDate( $string ) ){
			return true;
		}
		else{
			return false;
		}
	}	
	function strToTime($date_string) {
		$unix_string = strtotime($date_string);
		return $unix_string == -1 ? FALSE : $unix_string;				// PHP 5 compatible
	}
	/*
	* reformats date from datetime format to mysql date
	*/
  function dateFormatMysql($datetime) {
		return SC_Date::dateFormat($datetime, SC_DB_MYSQL_DATE);
	}
	/*
	* format value in given date format
	*/
  function dateFormat($date, $format = SC_DB_UK_DATE) {

    $date = trim( $date );
		$ret = $date;				// default return value is what was passed in

		// only do something if a valid date string is passed in
		if (	$date > 0 && SC_Date::isDate( $date ) ) {
			/*
			Convert UK date to MySQL
			*/
			if ( SC_Date::isUKDate( $date ) ) {
				$parts = explode( '/', $date );
				
				$mysql_datetime =
					date(
						"Y-m-d H:i:s",
						strtotime( (int) $parts[2] . "-" . (int) $parts[1] . "-" . (int) $parts[0] )
					);
			}

			switch ($format) {
				case SC_DB_UK_DATE:
					$ret = date("d/m/Y", strtotime( $mysql_datetime ) );
					break;
				case SC_DB_MYSQL_DATETIME:
					$ret = $mysql_datetime;
					break;
				case SC_DB_MYSQL_DATE:
					$ret = substr( $mysql_datetime, 0, 10 );		// no time part
					break;
			}
		}
	 	return $ret;
  }
	function getDateNowMysql(){
		return date('Y-m-d');
	}
}
?>