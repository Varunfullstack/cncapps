<?
/**
* Call further action business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEProject.inc.php");
require_once($cfg["path_dbe"]."/DBEProblem.inc.php");
class BUProject extends Business{
	var $dbeProject="";
	var $dbeCallActivity="";
	/**
	* Constructor
	* @access Public
	*/
	function BUProject(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbeProject=new DBEProject($this);
	}
	function updateProject(&$dsData){
		$this->setMethodName('updateProject');
		$this->updateDataaccessObject($dsData, $this->dbeProject);
		return TRUE;
	}
	function getProjectByID($ID, &$dsResults)
	{
		$this->dbeProject->setPKValue($ID);
		$this->dbeProject->getRow();
		return ($this->getData($this->dbeProject, $dsResults));
	}
	function getProjectsByCustomerID($customerID, &$dsResults, $activityDate = false )
	{
		$this->dbeProject->getRowsByCustomerID( $customerID, $activityDate );
		return ($this->getData($this->dbeProject, $dsResults));
	}

  function getCurrentProjects()
  {
    return $this->dbeProject->getCurrentProjects();
  }

	function deleteProject($ID){
		$this->setMethodName('deleteProject');
		if ( $this->canDelete($ID) ){
			return $this->dbeProject->deleteRow($ID);
		}
		else{
			return FALSE;
		}
	}
	/**
	*	canDeleteProject
	* Only allowed if this project has no callActivity rows at the moment
	*/
	function canDelete($ID){
		$dbeProblem = new DBEProblem($this);
		// validate no activities of this type
		$dbeProblem->setValue('projectID', $ID);
		if ( $dbeProblem->countRowsByColumn('projectID') < 1 ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	/**
	*	isCurrent
	* Has it expired?
	*/
	function isCurrent( $ID, $activityDate = false){
		$this->dbeProject->getRow($ID);
		
		if ( $activityDate ){
			$date = $activityDate;
		}
		else{
			$date = date( CONFIG_MYSQL_DATE );
		}
		
		if ( $this->dbeProject->getValue( 'expiryDate' ) < $date ){
			$ret = false;
		}
		else{
			$ret = true;
		}
		return $ret;
	}
}// End of class
?>