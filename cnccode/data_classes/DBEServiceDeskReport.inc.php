<?php /*
* Location table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEServiceDeskReport extends DBEntity{
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
		$this->setTableName("servicedeskreport");
 		$this->addColumn("serviceDeskReportID", DA_ID, DA_NOT_NULL, "sdr_servicedeskreportno");
 		$this->addColumn("yearMonth", DA_STRING, DA_NOT_NULL, "sdr_year_month");
    $this->addColumn("callsReceived", DA_INTEGER, DA_ALLOW_NULL, "sdr_calls_Received");
    $this->addColumn("callsOverflowed", DA_INTEGER, DA_ALLOW_NULL, "sdr_calls_overflowed");
    $this->addColumn("callsHelpdesk", DA_INTEGER, DA_ALLOW_NULL, "sdr_calls_helpdesk");
    $this->addColumn("callsAnswerSeconds", DA_INTEGER, DA_ALLOW_NULL, "sdr_calls_answer_seconds");
    $this->addColumn("callsAbandoned", DA_INTEGER, DA_ALLOW_NULL, "sdr_calls_abandoned");
    $this->addColumn("meetingResults", DA_MEMO, DA_ALLOW_NULL, "sdr_meeting_results");
    $this->addColumn("staffIssues", DA_MEMO, DA_ALLOW_NULL, "sdr_staff_issues");
    $this->addColumn("staffHolidayDays", DA_INTEGER, DA_NOT_NULL, "sdr_staff_holiday_days");
    $this->addColumn("staffSickDays", DA_INTEGER, DA_NOT_NULL, "sdr_staff_sick_days");
    $this->addColumn("training", DA_MEMO, DA_ALLOW_NULL, "sdr_training");
    $this->addColumn("anyOtherBusiness", DA_MEMO, DA_ALLOW_NULL, "sdr_any_other_business");
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>