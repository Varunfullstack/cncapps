<?
/**
* Notepad business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBENotepad.inc.php");
require_once($cfg["path_func"]."/Common.inc.php");
class BUNotepad extends Business{
	var $dbeNotepad="";
	/**
	* Constructor
	* @access Public
	*/
	function BUNotepad(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeNotepad=new DBENotepad($this);
	}
	/**
	* Get notepad rows whose 
	* @parameter String $noteType Indicates note entity type (e.g. Item)
	* @parameter Integer $noteKey Indicates note key (e.g. itemID)
	* @parameter DataSet &$dsResults results
	* @return bool : One or more rows
	* @access public
	*/
	function getNotes($noteType, $noteKey, &$dsResults){
		$this->setMethodName('getNotes');
		$ret= FALSE;
		if ($noteType==''){
			$this->raiseError('noteType not passed');
		}
		if ($noteKey==''){
			$this->raiseError('noteKey not passed');
		}
		$this->dbeNotepad->getRowsByTypeAndKey($noteType, $noteKey);
		return ($this->getData($this->dbeNotepad, $dsResults));
	}
	/**
	* Returns notes as one string
	*/
		function getNotesAsString($noteType, $noteKey){
		$this->setMethodName('getNotesAsString');
		$notes = '';
		if ($this->getNotes($noteType, $noteKey, $dsNotes)){
			while ($dsNotes->fetchNext()){
				$notes .= $dsNotes->getValue('noteText');
			}
		}
		return $notes;
	}
	/**
	* Update notepad from passed string
	* @parameter String $noteType Indicates note entity type (e.g. Item)
	* @parameter Integer $noteKey Indicates note key (e.g. itemID)
	* @parameter String $data inputdata
	* @access public
	*/
	function updateNotepad($noteType, $noteKey, $noteData){
		$this->setMethodName('updateNotepad');
		$noteArray = str_split($noteData, 76); // 76 is the length of the field
		// remove existing rows
		$this->dbeNotepad->deleteRowsByTypeAndKey($noteType, $noteKey);
		// remove existing rows
		$this->dbeNotepad->setValue('noteType', $noteType);
		$this->dbeNotepad->setValue('noteKey', $noteKey);
		$lineNo = 0;
		foreach ($noteArray as $noteText){
			$lineNo ++;
			$this->dbeNotepad->setValue('lineNo', $lineNo);
			$this->dbeNotepad->setValue('noteText', $noteText);
			$this->dbeNotepad->insertRow();
		}
	}
}// End of class
?>