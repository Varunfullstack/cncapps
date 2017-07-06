<?
/**
* System Header business class
* NOTE: Uses new lower case naming convention
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEHeaderNew.inc.php");
class BUHeader extends Business{
	var $dbeHeader="";
	var $dbeJHeader="";
	/**
	* Constructor
	* @access Public
	*/
	function BUHeader(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeHeader=new DBEHeader($this);
		$this->dbeJHeader=new DBEJHeader($this);
	}
	/**
	* Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
	* @parameter String $nameSearchString String to match against or numeric customerID
	* @parameter DataSet &$dsResults results
	* @return bool : One or more rows
	* @access public
	*/
	function getHeader(&$dsResults){
		$this->setMethodName('getHeader');
		$this->dbeJHeader->getRow();
		return($this->getData($this->dbeJHeader, $dsResults));
	}
	function updateHeader(&$dsData){
		$this->setMethodName('updateHeader');
		$this->updateDataaccessObject($dsData, $this->dbeHeader);
		return TRUE;
	}
	function updateHelpDesk( $data ){

		$this->setMethodName('updateHelpDesk');
		$this->dbeHeader->getRow(1);
		$this->dbeHeader->setValue('helpDeskProblems', $data['helpDeskProblems'] );
		$this->dbeHeader->updateRow();
		return TRUE;
	}
	function clearActivityProblemField(){

		$this->setMethodName('clearActivityProblemField');
		$this->dbeHeader->getRow(1);
		$this->dbeHeader->setValue('helpDeskProblems', '' );
		$this->dbeHeader->updateRow();
		return TRUE;
	}
}// End of class
?>