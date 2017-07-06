<?
/**
* Call further action business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEFurtherAction.inc.php");
require_once($cfg["path_dbe"]."/DBEFutureAction.inc.php");
class BUFurtherAction extends Business{
	var $dbeFurtherAction="";
	var $dbeFutureAction="";
	/**
	* Constructor
	* @access Public
	*/
	function BUFurtherAction(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeFurtherAction=new DBEFurtherAction($this);
	}
	function updateFurtherAction(&$dsData){
		$this->setMethodName('updateFurtherAction');
		$this->updateDataaccessObject($dsData, $this->dbeFurtherAction);
		return TRUE;
	}
	function getFurtherActionByID($ID, &$dsResults)
	{
		$this->dbeFurtherAction->setPKValue($ID);
		$this->dbeFurtherAction->getRow();
		return ($this->getData($this->dbeFurtherAction, $dsResults));
	}
	function getAllTypes(&$dsResults)
	{
		$this->dbeFurtherAction->getRows();
		return ($this->getData($this->dbeFurtherAction, $dsResults));
	}
	function deleteFurtherAction($ID){
		$this->setMethodName('deleteFurtherAction');
		if ( $this->canDelete($ID) ){
			return $this->dbeFurtherAction->deleteRow($ID);
		}
		else{
			return FALSE;
		}
	}
	/**
	*	canDeleteFurtherAction
	* Only allowed if this further actionhas no future action rows at the moment
	*/
	function canDelete($ID){
		$dbeFutureAction = new DBEFutureAction($this);
		// validate no activities of this type
		$dbeFutureAction->setValue('furtherActionID', $ID);
		if ( $dbeFutureAction->countRowsByColumn('furtherActionID') < 1 ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
}// End of class
?>