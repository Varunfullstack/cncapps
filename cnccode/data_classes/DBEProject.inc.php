<?php /*
* Project table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProject extends DBEntity
{
    const projectID = "projectID";
    const customerID = "customerID";
    const description = "description";
    const notes = "notes";
    const openedDate = "startDate";
    const completedDate = "expiryDate";
    const projectEngineer = "consultantID";
    const commenceDate = "commenceDate";
    const planFileName = "planFileName";
    const planFile = "planFile";
    const planMIMEType = "planMIMEType";
    const ordHeadID = "ordHeadID";
    const outOfHoursBudgetDays = "outOfHoursBudgetDays";
    const inHoursBudgetDays = "inHoursBudgetDays";
    const calculatedBudget = "calculatedBudget";

    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("project");
        $this->addColumn(
            self::projectID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::notes,
            DA_MEMO,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::openedDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::completedDate,
            DA_DATE,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::projectEngineer,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::commenceDate,
            DA_DATE,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::planFileName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::planFile,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::planMIMEType,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::ordHeadID,
            DA_ID,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::outOfHoursBudgetDays,
            DA_FLOAT,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::inHoursBudgetDays,
            DA_FLOAT,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::calculatedBudget,
            DA_YN_FLAG,
            DA_NOT_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();

        $this->db->connect();
    }


    public function replicate(&$data)
    {
        return parent::replicate($data); // TODO: Change the autogenerated stub
    }

    function getRowsByCustomerID($customerID,
                                 $activityDate = false
    )
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $customerID
            ) . "' AND description != ''";

        if ($activityDate) {
            $queryString .=
                " AND (" . $this->getDBColumnName(
                    self::completedDate
                ) . ">= '$activityDate' or " . $this->getDBColumnName(
                    self::completedDate
                ) . " is null or " . $this->getDBColumnName(self::completedDate) . " = '0000-00-00')";
        }

        $this->setQueryString($queryString);

        return ($this->getRows());
    }

    function getProjectList()
    {
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE expiryDate >= NOW() OR expiryDate IS NULL OR expiryDate = '0000-00-00'";

        $this->setQueryString($queryString);

        return ($this->getRows());
    }

    function getCurrentProjects()
    {
        $queryString =
            "SELECT 
  project.projectID AS projectID,
  project.customerID,
  description,
  notes,
  startDate,
  expiryDate,
  commenceDate,
  cus_name AS customerName,
  pu.`comment` ,
  pu.`createdAt`,
  pu.`createdBy`,
  `planFileName`,
  CONCAT_WS(' ', engineer.firstName, engineer.lastName) AS engineerName,
  round(outOfHoursBudgetDays,2) as outOfHoursBudgetDays,
  round(inHoursBudgetDays,2) as inHoursBudgetDays,
  (SELECT GROUP_CONCAT(problem.`pro_problemno`) FROM problem WHERE pro_linked_ordno = project.`ordHeadID` AND project.ordHeadID <> 0) problemno,
  calculatedBudget
FROM
  project 
  LEFT JOIN consultant engineer ON project.consultantID = engineer.cns_consno
  JOIN customer 
    ON cus_custno = project.customerID
  LEFT JOIN (SELECT * FROM (SELECT  * FROM projectUpdates ORDER BY createdAt DESC) reOrderedProjectUpdates GROUP BY projectID) pu ON pu.projectID = project.`projectID`
WHERE expiryDate >= NOW() OR expiryDate IS NULL OR expiryDate = '0000-00-00'
ORDER BY customerName ASC";

        $this->db->query($queryString);

        $results = array();

        while ($row = $this->db->next_record()) {
            $results[] = $this->db->Record;
        }
        return $results;
    }
}

?>