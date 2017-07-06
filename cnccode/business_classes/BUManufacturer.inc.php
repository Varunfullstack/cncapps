<?
/**
* Call manufacturer business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEManufacturer.inc.php");
require_once($cfg["path_dbe"]."/DBEItem.inc.php");
define('BUMANUFACTURER_NAME_STR_NT_PASD', 'No name string passed');
class BUManufacturer extends Business{
	var $dbeManufacturer="";
	/**
	* Constructor
	* @access Public
	*/
	function BUManufacturer(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeManufacturer=new DBEManufacturer($this);
	}
	function updateManufacturer(&$dsData){
		$this->setMethodName('updateManufacturer');
		$this->updateDataaccessObject($dsData, $this->dbeManufacturer);
		return TRUE;
	}
	function getManufacturerByID($ID, &$dsResults)
	{
		$this->dbeManufacturer->setPKValue($ID);
		$this->dbeManufacturer->getRow();
		return ($this->getData($this->dbeManufacturer, $dsResults));
	}
	/**
	* Get Item rows whose names match the search string or, if the string is numeric, try to select by customerID
	* Don't include discontinued items
	* @parameter String $nameSearchString String to match against or numeric itemID
	* @parameter DataSet &$dsResults results
	* @return bool : One or more rows
	* @access public
	*/
	function getManufacturersByNameMatch($matchString, &$dsResults){
		$this->setMethodName('getManufacturersByNameMatch');
		if ($matchString==''){
			$this->raiseError(BUMANUFACTURER_NAME_STR_NT_PASD);
		}
		$matchString = trim($matchString);
		$ret = FALSE;
		if (is_numeric($matchString)){
			$ret=($this->getManufacturerByID($matchString,$dsResults));
		}
		if (!$ret){
			$this->dbeManufacturer->setValue("name", $matchString);
			$this->dbeManufacturer->getRowsByNameMatch();
			$ret=($this->getData($this->dbeManufacturer, $dsResults));
		}
		return $ret;
	}
	function getAll(&$dsResults)
	{
		$this->dbeManufacturer->getRows( 'name' );
		return ($this->getData($this->dbeManufacturer, $dsResults));
	}
	function deleteManufacturer($ID){
		$this->setMethodName('deleteManufacturer');
		if ( $this->canDeleteManufacturer($ID) ){
			return $this->dbeManufacturer->deleteRow($ID);
		}
		else{
			return FALSE;
		}
	}
	/**
	*	canDeleteManufacturer
	* Only allowed if type has no activities
	*/
	function canDeleteManufacturer($ID){
		$dbeItem = new DBEItem($this);
		// validate no items of this manufacturer
		$dbeItem->setValue('manufacturerID', $ID);
		if ( $dbeItem->countRowsByColumn('manufacturerID') < 1 ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
}// End of class
?>