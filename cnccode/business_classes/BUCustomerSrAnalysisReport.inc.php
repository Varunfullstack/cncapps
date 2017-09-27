<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerSrAnalysisReport extends Business
{
    private $includedItemTypes;

    const ITEMTYPENO_SERVERCARE = 55;
    const ITEMTYPENO_SERVICEDESK = 56;

    function __construct(&$owner)
    {
        parent::__construct($owner);
        /* so that we can extract "other" itemtypes */
        $this->includedItemTypes = self::ITEMTYPENO_SERVERCARE . ',' . self::ITEMTYPENO_SERVICEDESK . ',' . CONFIG_PREPAY_ITEMTYPEID;

    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('customerID', '');
    }

    function search($dsSearchForm)
    {

        $fromDate = $dsSearchForm->getValue('fromDate');
        $toDate = $dsSearchForm->getValue('toDate');
        $customerID = $dsSearchForm->getValue('customerID');

        return $this->getResultsByDateRange($customerID, $fromDate, $toDate);
    }

    function getResultsByPeriodRange($customerID, $startPeriod, $endPeriod)
    {
        /*
        Turn periods into dates
        */
        $fromDate = $startPeriod . '-01';
        $toDate = $endPeriod . '-31';

        return $this->getResultsByDateRange($customerID, $fromDate, $toDate);
    }

    function getResultsByDateRange($customerID, $fromDate, $toDate)
    {
        $resultset = array();

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
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE
                    );
                $resultRow['serverCareHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE
                    );
                $resultRow['serverCareHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE
                    );
                $resultRow['serverCareCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVERCARE
                    );
                $resultRow['serviceDeskCount1And3'] =
                    $this->getCount1to3(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK
                    );
                $resultRow['serviceDeskHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK
                    );
                $resultRow['serviceDeskHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK
                    );
                $resultRow['serviceDeskCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        self::ITEMTYPENO_SERVICEDESK
                    );
                $resultRow['prepayCount1And3'] =
                    $this->getCount1to3(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayCount1And3'] =
                    $this->getCount1to3(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );
                $resultRow['prepayCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        CONFIG_PREPAY_ITEMTYPEID
                    );

                $resultRow['otherCount1And3'] =
                    $this->getCount1to3(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherCount1And3'] =
                    $this->getCount1to3(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherHoursResponded'] =
                    $this->getRespondedHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherHoursFix'] =
                    $this->getFixHours(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultRow['otherCount4'] =
                    $this->getCount4(
                        $customerID,
                        $row['year'],
                        $row['month'],
                        false
                    );
                $resultset[] = $resultRow;
            }
        }
        return $resultset;
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

        if ($result = $this->db->query($query)) {

            while ($tmp = $result->fetch_array(MYSQLI_ASSOC)) {
                $res[] = $tmp;
            }

        } else {
            $res = false;
        }
        return $res;

    }

    function getCount1to3($customerID = false, $year, $month, $itemtypeno)
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

    function getRespondedHours($customerID = false, $year, $month, $itemtypeno)
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

    function getFixHours($customerID = false, $year, $month, $itemtypeno)
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

    function getCount4($customerID = false, $year, $month, $itemtypeno)
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