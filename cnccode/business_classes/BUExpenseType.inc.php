<?
/**
* Call expense type business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEExpenseType.inc.php");
require_once($cfg["path_dbe"]."/DBEExpense.inc.php");
class BUExpenseType extends Business{
	var $dbeExpenseType="";
	/**
	* Constructor
	* @access Public
	*/
	function BUExpenseType(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeExpenseType=new DBEExpenseType($this);
		$this->dbeExpense=new DBEExpense($this);				// join to item table
	}
	function updateExpenseType(&$dsData){
		$this->setMethodName('updateExpenseType');
		$this->updateDataaccessObject($dsData, $this->dbeExpenseType);
		return TRUE;
	}
	function getExpenseTypeByID($ID, &$dsResults)
	{
		$this->dbeExpenseType->setPKValue($ID);
		$this->dbeExpenseType->getRow();
		return ($this->getData($this->dbeExpenseType, $dsResults));
	}
	function getAllTypes(&$dsResults)
	{
		$this->dbeExpenseType->getRows();
		return ($this->getData($this->dbeExpenseType, $dsResults));
	}
	function deleteExpenseType($ID){
		$this->setMethodName('deleteExpenseType');
		if ( $this->canDeleteExpenseType($ID) ){
			return $this->dbeExpenseType->deleteRow($ID);
		}
		else{
			return FALSE;
		}
	}
	/**
	*	canDeleteExpenseType
	* Only allowed if type has no activities
	*/
	function canDeleteExpenseType($ID){
		$dbeExpense = new DBEExpense($this);
		// validate no activities of this type
		$dbeExpense->setValue('expenseTypeID', $ID);
		if ( $dbeExpense->countRowsByColumn('expenseTypeID') < 1 ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
}// End of class
?>