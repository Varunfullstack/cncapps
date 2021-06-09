<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEProject.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");

class BUProject extends Business
{
    var $dbeProject      = "";
    var $dbeCallActivity = "";

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeProject = new DBEProject($this);
    }

    function updateProject(&$dsData)
    {
        $this->setMethodName('updateProject');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeProject
        );
        return TRUE;
    }

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
    public static function getCurrentProjectLink($customerID)
    {
        $thing     = null;
        $buProject = new BUProject($thing);
        $dsProject = new DataSet($thing);
        $buProject->getProjectsByCustomerID(
            $customerID,
            $dsProject,
            date(DATE_MYSQL_DATE)
        );
        $link = '';
        while ($dsProject->fetchNext()) {

            if (!$link) {
                $link = "<table><tr class='makeItColor'><td style='color: black'>SEE CURRENT PROJECTS</td>";
            }
            $url  = Controller::buildLink(
                'Projects.php',
                array(
                    'action'    => 'edit',
                    'projectID' => $dsProject->getValue(DBEProject::projectID),
                )
            );
            $link .= '<td><A HREF="' . $url . ' " target="_blank" >' . $dsProject->getValue(
                    DBEProject::description
                ) . '</A></td>';

        }
        if ($link) {
            $link .= "</tr></table>";
        }
        return $link;

    }

    /**
     * @param $customerID
     * @return array
     * @throws Exception
     */
    public static function getCustomerProjects($customerID)
    {
        if (!isset($customerID)) return [];
        $date  = date(DATE_MYSQL_DATE);
        $query = "SELECT  
                    `projectID`,
                    `description`, 
                    concat('Projects.php?action=edit&projectID=',projectID) editUrl
                FROM project 
                    LEFT JOIN  `projectstages` ps ON ps.id=project.`projectStageID`
                WHERE 
                    (project.`projectStageID` IS  NULL OR ps.displayInSr=1)
                    AND `customerID`=:customerId";
        $query .= " AND (expiryDate >= '$date' or expiryDate is null)";
        return DBConnect::fetchAll($query, ["customerId" => $customerID]);
        /*
        $thing = null;
        $buProject = new BUProject($thing);
        $dsProject = new DataSet($thing);
        $buProject->getProjectsByCustomerID(
            $customerID,
            $dsProject,
            date(DATE_MYSQL_DATE)
        );        
        $projects=array();
        while ($dsProject->fetchNext()) {
            $url = Controller::buildLink(
                'Projects.php',
                array(
                    'action'    => 'edit',
                    'projectID' => $dsProject->getValue(DBEProject::projectID),
                )
            );
            array_push($projects,
            [
                "projectID"=> $dsProject->getValue(DBEProject::projectID),
                "description"=>$dsProject->getValue(DBEProject::description),
                "editUrl"=> $url 
            ]);
        }
        return $projects;*/
    }

    function getProjectByID($ID,
                            &$dsResults
    )
    {
        $this->dbeProject->setPKValue($ID);
        $this->dbeProject->getRow();
        return ($this->getData(
            $this->dbeProject,
            $dsResults
        ));
    }

    function getProjectsByCustomerID($customerID,
                                     &$dsResults,
                                     $activityDate = false
    )
    {
        if (!isset($customerID)) return [];
        $this->dbeProject->getRowsByCustomerID(
            $customerID,
            $activityDate
        );
        return ($this->getData(
            $this->dbeProject,
            $dsResults
        ));
    }

    function getCurrentProjects()
    {
        return $this->dbeProject->getCurrentProjects();
    }

    function deleteProject($ID)
    {
        $this->setMethodName('deleteProject');
        if ($this->canDelete($ID)) {
            return $this->dbeProject->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteProject
     * Only allowed if this project has no callActivity rows at the moment
     * @param $ID
     * @return bool
     */
    function canDelete($ID)
    {
        $dbeProblem = new DBEProblem($this);
        // validate no activities of this type
        $dbeProblem->setValue(
            DBEProblem::projectID,
            $ID
        );
        if ($dbeProblem->countRowsByColumn(DBEProblem::projectID) < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $projectID
     * @param $linkedOrderID
     * @throws Exception
     */
    public function updateLinkedSalesOrder($projectID,
                                           $linkedOrderID,
                                           $orignalOrder = false
    )
    {
        $dbeSalesOrder = new DBEOrdhead($this);
        if (!$dbeSalesOrder->getRow($linkedOrderID)) {
            throw new Exception('Sales order does not exist');
        }
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        if ($dbeProject->getValue(DBEProject::customerID) != $dbeSalesOrder->getValue(DBEOrdhead::customerID)) {
            throw new Exception("Sales Order Not For This Customer");
        }
        $testProject = new DBEProject($this);
        if (!$orignalOrder) {
            $testProject->setValue(
                DBEProject::ordHeadID,
                $linkedOrderID
            );
            $testProject->getRowByColumn(DBEProject::ordHeadID);
            if ($testProject->rowCount()) {
                throw new Exception('The Sales Order given does already have a linked project');
            };
            $dbeProject->setValue(
                DBEProject::ordHeadID,
                $linkedOrderID
            );
        } else {
            $testProject->setValue(
                DBEProject::ordOriginalHeadID,
                $linkedOrderID
            );
            $testProject->getRowByColumn(DBEProject::ordOriginalHeadID);
            if ($testProject->rowCount()) {
                throw new Exception('The Sales Order given does already have a linked project');
            };
            $dbeProject->setValue(
                DBEProject::ordOriginalHeadID,
                $linkedOrderID
            );
        }
        $dbeProject->updateRow();
    }
}// End of class
