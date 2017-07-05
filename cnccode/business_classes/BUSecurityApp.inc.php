<?
/**
* Call security applcation business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBESecurityApp.inc.php");
class BUSecurityApp extends Business{
	var $dbeSecurityApp="";
	/**
	* Constructor
	* @access Public
	*/
	function BUSecurityApp(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeSecurityApp=new DBESecurityApp($this);
	}
	function updateSecurityApp(&$dsData){
		$this->setMethodName('updateSecurityApp');
		$this->updateDataaccessObject($dsData, $this->dbeSecurityApp);
		return TRUE;
	}
	function getSecurityAppByID($ID, &$dsResults)
	{
		$this->dbeSecurityApp->setPKValue($ID);
		$this->dbeSecurityApp->getRow();
		return ($this->getData($this->dbeSecurityApp, $dsResults));
	}
	function getAllRows(&$dsResults)
	{
		$this->dbeSecurityApp->getRows();
		return ($this->getData($this->dbeSecurityApp, $dsResults));
	}
	function deleteSecurityApp($ID){
		$this->setMethodName('deleteSecurityApp');
		return $this->dbeSecurityApp->deleteRow($ID);
	}
	function getBackupApplications( &$dsResults ){
		$this->dbeSecurityApp->setValue( 'backupFlag', 'Y' );
		$this->dbeSecurityApp->getRowsByColumn( 'backupFlag');
		return ($this->getData($this->dbeSecurityApp, $dsResults));
	}
	function getEmailAVApplications( &$dsResults ){
		$this->dbeSecurityApp->setValue( 'emailAVFlag', 'Y' );
		$this->dbeSecurityApp->getRowsByColumn( 'emailAVFlag');
		return ($this->getData($this->dbeSecurityApp, $dsResults));
	}
	function getSeverAVApplications( &$dsResults ){
		$this->dbeSecurityApp->setValue( 'serverAVFlag', 'Y' );
		$this->dbeSecurityApp->getRowsByColumn( 'serverAVFlag');
		return ($this->getData($this->dbeSecurityApp, $dsResults));
	}
}// End of class
?>