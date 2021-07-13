<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerSrAnalysisReport extends Business
{
    const ITEMTYPENO_SERVERCARE = 55;
    const ITEMTYPENO_SERVICEDESK = 56;
    const searchFormCustomerID = 'customerID';
    const searchFormFromDate = 'fromDate';
    const searchFormToDate = 'toDate';
    private $includedItemTypes;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        /* so that we can extract "other" itemTypes */
        $this->includedItemTypes = self::ITEMTYPENO_SERVERCARE . ',' . self::ITEMTYPENO_SERVICEDESK . ',' . CONFIG_PREPAY_ITEMTYPEID;

    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormCustomerID, '');
    }

    /**
     * @param DataSet $dsSearchForm
     * @return array
     */
    function search($dsSearchForm)
    {

        $fromDate = $dsSearchForm->getValue(self::searchFormFromDate);
        $toDate = $dsSearchForm->getValue(self::searchFormToDate);
        $customerID = $dsSearchForm->getValue(self::searchFormCustomerID);

        return $this->getResultsByDateRangeBrokenByPriority($customerID, $fromDate, $toDate);
    }

    function getResultsByDateRangeBrokenByPriority($customerID, $fromDate, $toDate)
    {
        $resultSet = [];

        $months = $this->getMonths(
            $fromDate,
            $toDate
        );

        if ($months) {

            foreach ($months as $key => $row) {

                $resultRow = array();

                $key = $row['year'] . ' ' . $row['month'];

                $resultRow['period'] = $key;

                $resultRow['year'] = $row['year'];

                $resultRow['monthName'] = $row['monthName'];

                $priorities = [1, 2, 3, 4];
                $itemTypes = [
                    "serverCare"  => self::ITEMTYPENO_SERVERCARE,
                    "serviceDesk" => self::ITEMTYPENO_SERVICEDESK,
                    "prepay"      => CONFIG_PREPAY_ITEMTYPEID,
                    "other"       => null
                ];

                $resultRow['types'] = [
                ];


                foreach ($itemTypes as $itemTypeKey => $itemType) {

                    if (!isset($resultRow['types'][$itemTypeKey])) {
                        $resultRow[$itemTypeKey] = [];
                    }

                    foreach ($priorities as $priority) {

                        if (!isset($resultRow['types'][$itemTypeKey][$priority])) {
                            $resultRow['types'][$itemTypeKey][$priority] = [];
                        }

                        $resultRow['types'][$itemTypeKey][$priority]["count"] = $this->getCountForPriority(
                            $priority,
                            $customerID,
                            $row['year'],
                            $row['month'],
                            $itemType
                        );

                        $resultRow['types'][$itemTypeKey][$priority]["hoursResponded"] = $this->getRespondedHoursByPriority(
                            $priority,
                            $customerID,
                            $row['year'],
                            $row['month'],
                            $itemType
                        );

                        $resultRow['types'][$itemTypeKey][$priority]["hoursFix"] =
                            $this->getFixHoursByPriority(
                                $priority,
                                $customerID,
                                $row['year'],
                                $row['month'],
                                $itemType
                            );
                    }
                }

                $resultSet[] = $resultRow;
            }
        }
        return $resultSet;
    }

    function getMonths($fromDate, $toDate)
    {

        $query = "
      SELECT
        YEAR( pro_date_raised ) AS year,
        MONTH( pro_date_raised ) AS month,
        MONTHNAME( pro_date_raised ) AS monthName
      FROM
        problem
      WHERE 1=1";

        if ($fromDate) {
            $query .= " AND  pro_date_raised >= '$fromDate'";
        }
        if ($toDate) {
            $query .= " AND  pro_date_raised <= '$toDate'";
        }

        $query .= " GROUP BY
        YEAR( pro_date_raised ), MONTH( pro_date_raised );";

        if (!($result = $this->db->query($query))) {
            return false;
        }
        $res = [];
        while ($tmp = $result->fetch_array(MYSQLI_ASSOC)) {
            $res[] = $tmp;
        }

        return $res;
    }

    function getCountForPriority($priority, $customerID, $year, $month, $itemtypeno = false)
    {
        $query = "
      SELECT
        COUNT(*) as count
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority = $priority
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }

        return $this->db->query($query)->fetch_object()->count;
    }

    function getRespondedHoursByPriority($priority, $customerID, $year, $month, $itemtypeno = false)
    {
        $query = "
      SELECT
        AVG( pro_responded_hours ) as hours
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority = $priority
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }
        return $this->db->query($query)->fetch_object()->hours;
    }

    function getFixHoursByPriority($priority, $customerID, $year, $month, $itemtypeno = false)
    {
        $query = "
      SELECT
        AVG( pro_working_hours ) as hours
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority = $priority
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }
        return $this->db->query($query)->fetch_object()->hours;
    }

    function getResultsByPeriodRange($customerID, DateTimeInterface $startPeriod, DateTimeInterface $endPeriod)
    {
        /*
        Turn periods into dates
        */
        $fromDate = $startPeriod->format('Y-m-d');
        $toDate = $endPeriod->format('Y-m-d');

        return $this->getResultsByDateRange($customerID, $fromDate, $toDate);
    }

    function getResultsByDateRange($customerID, $fromDate, $toDate)
    {
        $resultSet = array();

        $months = $this->getMonths(
            $fromDate,
            $toDate
        );

        if ($months) {

            foreach ($months as $key => $row) {

                $resultRow = array();

                $key = $row['year'] . ' ' . $row['month'];

                $resultRow['period'] = $key;

                $resultRow['year'] = $row['year'];

                $resultRow['monthName'] = $row['monthName'];

                $resultRow['serverCareCount1And3'] =
                    $this->getCount1to3(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE,
                        $customerID
                    );
                $resultRow['serverCareHoursResponded'] =
                    $this->getRespondedHours(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE,
                        $customerID
                    );
                $resultRow['serverCareHoursFix'] =
                    $this->getFixHours(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE,
                        $customerID
                    );
                $resultRow['serverCareCount4'] =
                    $this->getCount4(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE,
                        $customerID
                    );
                $resultRow['serviceDeskCount1And3'] =
                    $this->getCount1to3(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK,
                        $customerID
                    );
                $resultRow['serviceDeskHoursResponded'] =
                    $this->getRespondedHours(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK,
                        $customerID
                    );
                $resultRow['serviceDeskHoursFix'] =
                    $this->getFixHours(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK,
                        $customerID
                    );
                $resultRow['serviceDeskCount4'] =
                    $this->getCount4(
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK,
                        $customerID
                    );
                $resultRow['prepayCount1And3'] =
                    $this->getCount1to3(
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID,
                        $customerID
                    );
                $resultRow['prepayHoursResponded'] =
                    $this->getRespondedHours(
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID,
                        $customerID
                    );
                $resultRow['prepayHoursFix'] =
                    $this->getFixHours(
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID,
                        $customerID
                    );
                $resultRow['prepayCount4'] =
                    $this->getCount4(
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID,
                        $customerID
                    );
                $resultRow['otherCount1And3'] =
                    $this->getCount1to3(
                        $row['year'],
                        $row['month'],
                        false,
                        $customerID
                    );
                $resultRow['otherHoursResponded'] =
                    $this->getRespondedHours(
                        $row['year'],
                        $row['month'],
                        false,
                        $customerID
                    );
                $resultRow['otherHoursFix'] =
                    $this->getFixHours(
                        $row['year'],
                        $row['month'],
                        false,
                        $customerID
                    );
                $resultRow['otherCount4'] =
                    $this->getCount4(
                        $row['year'],
                        $row['month'],
                        false,
                        $customerID
                    );
                $resultSet[] = $resultRow;
            }
        }
        return $resultSet;
    }

    function getCount1to3($year, $month, $itemtypeno, $customerID = false)
    {
        $query = "
      SELECT
        COUNT(*) as count
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority BETWEEN 1 AND 3
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }

        return $this->db->query($query)->fetch_object()->count;
    }

    function getRespondedHours($year, $month, $itemtypeno, $customerID = false)
    {
        $query = "
      SELECT
        AVG( pro_responded_hours ) as hours
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority BETWEEN 1 AND 3
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }
        return $this->db->query($query)->fetch_object()->hours;
    }

    function getFixHours($year, $month, $itemtypeno, $customerID = false)
    {
        $query = "
      SELECT
        AVG( pro_working_hours ) as hours
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority BETWEEN 1 AND 3
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }
        return $this->db->query($query)->fetch_object()->hours;
    }

    function getCount4($year, $month, $itemtypeno, $customerID = false)
    {
        $query = "
      SELECT
        COUNT(*) as count
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN item ON itm_itemno = cui_itemno
      WHERE
        pro_priority = 4
        AND MONTH(pro_date_raised) = $month
        AND YEAR( pro_date_raised) = $year";

        if ($itemtypeno) {
            $query .= " AND itm_itemtypeno = $itemtypeno";
        } else {
            $query .= " AND itm_itemtypeno NOT IN ( " . $this->includedItemTypes . ")";
        }

        if ($customerID) {
            $query .= " AND pro_custno = $customerID";
        }
        return $this->db->query($query)->fetch_object()->count;
    }
}