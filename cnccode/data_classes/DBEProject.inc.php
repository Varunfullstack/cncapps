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


        $this->setPK(0);
        $this->setAddColumnsOff();

        $this->db->connect();
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
                " AND " . $this->getDBColumnName(self::completedDate) . ">= '$activityDate'";
        }

        $this->setQueryString($queryString);

        return ($this->getRows());
    }

    function getCurrentProjects()
    {
        $queryString =
            "SELECT 
          projectID,
          customerID,
          description,
          notes,
          startDate,
          expiryDate,
          cus_name AS customerName
          
        FROM 
          project
          JOIN customer ON cus_custno = project.customerID

        WHERE 
          expiryDate >= NOW()";

        $this->db->query($queryString);

        $results = array();

        while ($row = $this->db->next_record()) {
            $results[] = $this->db->Record;
        }
        return $results;
    }
}

?>