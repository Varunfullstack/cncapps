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
require_once($cfg["path_func"] . "/activity.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUStaffProductivityReport extends Business
{
    const searchFormStartDate = "startDate";
    const searchFormEndDate = "endDate";


    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormStartDate, DA_DATE, DA_NOT_NULL);
        $dsData->addColumn(self::searchFormEndDate, DA_DATE, DA_NOT_NULL);
    }

    /**
     * @param DSForm $dsSearchForm
     * @return array
     */
    function search(&$dsSearchForm)
    {
        $query =
            "
      SELECT 
        cns_consno AS userID,
        cns_name AS name
      FROM
        consultant
      WHERE
        activeFlag = 'Y'";

        $results = $this->db->query($query);

        $ret = array();

        while ($row = $results->fetch_object()) {

            $ret[$row->name]['name'] = $row->name;

            $ret[$row->name]['tAndmHours'] =
                $this->getHours(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    0
                );
            $ret[$row->name]['tAndmCost'] =
                $this->getCost(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    0
                );
            $ret[$row->name]['tAndmBilled'] =
                $this->getTandMBilled(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate)
                );
            $ret[$row->name]['prePayHours'] =
                $this->getHours(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    57

                );
            $ret[$row->name]['prePayCost'] =
                $this->getCost(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    57
                );
            $ret[$row->name]['prePayBilled'] =
                $this->getPrepayBilled(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate)
                );
            $ret[$row->name]['serviceDeskHours'] =
                $this->getHours(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    56
                );
            $ret[$row->name]['serviceDeskCost'] =
                $this->getCost(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    56
                );
            $ret[$row->name]['serverCareHours'] =
                $this->getHours(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    55
                );
            $ret[$row->name]['serverCareCost'] =
                $this->getCost(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    55
                );
            $ret[$row->name]['inHouseHours'] =
                $this->getInHouseHours(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate)
                );
            $ret[$row->name]['inHouseCost'] =
                $this->getCost(
                    $row->userID,
                    $dsSearchForm->getValue(self::searchFormStartDate),
                    $dsSearchForm->getValue(self::searchFormEndDate),
                    0,
                    true
                );
            $ret[$row->name]['totalHours'] =
                $ret[$row->name]['tAndmHours'] +
                $ret[$row->name]['inHouseHours'] +
                $ret[$row->name]['serverCareHours'] +
                $ret[$row->name]['serviceDeskHours'] +
                $ret[$row->name]['prePayHours'];
            $ret[$row->name]['totalCost'] =
                $ret[$row->name]['tAndmCost'] +
                $ret[$row->name]['inHouseCost'] +
                $ret[$row->name]['serverCareCost'] +
                $ret[$row->name]['serviceDeskCost'] +
                $ret[$row->name]['prePayCost'];
            $ret[$row->name]['totalBilled'] =
                $ret[$row->name]['tAndmBilled'] +
                $ret[$row->name]['prePayBilled'];
        }
        return $ret;
    }

    function getHours($userID, $startDate, $endDate, $contractItemID = 0)
    {
        $query = "
      SELECT
        SUM( ( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) )/ 60/60 ) AS hours
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno";

        if ($contractItemID > 0) {

            $query .=
                " JOIN custitem ON cui_cuino = pro_contract_cuino 
           JOIN item ON cui_itemno = itm_itemno
        WHERE itm_itemtypeno = $contractItemID";
        } else {
            $query .=
                " WHERE pro_contract_cuino = 0";          // T and M
        }

        $query .=
            " AND
        caa_date BETWEEN '$startDate' AND '$endDate'
        AND caa_starttime is not null
        AND caa_endtime is not null
      AND
        caa_consno = $userID";

        //echo $query . '<BR/>';

        $results = $this->db->query($query);
        $row = $results->fetch_object();

        if ($row->hours == null) {
            $ret = 0;
        } else {
            $ret = $row->hours;
        }
        return number_format($ret, 1, '.', '');
    }

    /**
     * @param $userID
     * @param $startDate
     * @param $endDate
     * @param int $contractItemID
     * @param bool $inHouse
     * @return string
     */
    function getCost($userID, $startDate, $endDate, $contractItemID = 0, $inHouse = false)
    {
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $query = "
      SELECT
        cns_helpdesk_flag as helpdeskFlag,
        cns_hourly_pay_rate as hourlyPayRate
      FROM
        consultant
      WHERE
        cns_consno = $userID";
        $result = $this->db->query($query);
        $helpDeskRow = $result->fetch_object();
        $hourlyPayRate = $helpDeskRow->hourlyPayRate;
        /* loop through activities */
        $query = "
      SELECT
        caa_callactivityno as activityId,
        caa_starttime AS startTime,
        caa_endtime AS endTime,
             getOvertime(caa_callactivityno) as overtime
      FROM
        callactivity";

        if ($inHouse) {
            $query .= "
        JOIN problem ON pro_problemno = caa_problemno
          WHERE
            pro_custno = 282";
        } else {
            if ($contractItemID > 0) {
                $query .=
                    " JOIN problem ON pro_problemno = caa_problemno
           JOIN custitem ON cui_cuino = pro_contract_cuino 
           JOIN item ON cui_itemno = itm_itemno
          WHERE itm_itemtypeno = $contractItemID";
            } else {
                $query .=
                    " JOIN problem ON pro_problemno = caa_problemno
          WHERE pro_contract_cuino = 0";          // T and M
            }
        } // end if $inHouse

        $query .=
            " AND caa_consno = $userID
        AND caa_date BETWEEN '$startDate' AND '$endDate'
        AND( caa_endtime <> caa_starttime )
        AND caa_starttime is not null
        AND caa_endtime is not null and caa_endtime <> ''
      ORDER BY
        caa_date";
        $results = $this->db->query($query);
        $overtimeCost = 0;
        $normalTimeCost = 0;
        while ($row = $results->fetch_object()) {
            $startTime = common_convertHHMMToDecimal($row->startTime);
            $endTime = common_convertHHMMToDecimal($row->endTime);
            $overtime = $row->overtime;
            $totalTime = $endTime - $startTime;
            $normalTime = $totalTime - $overtime;
            $thisOvertimeCost = $overtime * ($hourlyPayRate * 1.5);
            $overtimeCost += $thisOvertimeCost;
            $thisNormalTimeCost = $normalTime * $hourlyPayRate;
            $normalTimeCost += $thisNormalTimeCost;
        } // end while activity

        $totalCost = $overtimeCost + $normalTimeCost;
        if ($totalCost == null) {
            $totalCost = 0;
        }
        return number_format($totalCost, 2, '.', '');
    } // end in-house hours

    function getTandMBilled($userID, $startDate, $endDate)
    {
        $query = "
      SELECT
        cns_name as username
      FROM
        consultant
      WHERE
        cns_consno = $userID";

        //echo $query . '<BR/>';

        $results = $this->db->query($query);
        $row = $results->fetch_object();

        $username = $row->username;

        $query = "
      SELECT
        SUM(ordline.`odl_e_total`) as value
      FROM
        ordline
        JOIN ordhead ON odl_ordno = odh_ordno
      WHERE
        odl_desc = '" . $username . " - Consultancy'
        AND odh_date BETWEEN '$startDate' AND '$endDate'";

//    echo $query . '<BR/>';

        $results = $this->db->query($query);
        $row = $results->fetch_object();

        if ($row->value == null) {
            $ret = 0;
        } else {
            $ret = $row->value;
        }
        return number_format($ret, 2, '.', '');
    }

    function getPrepayBilled($userID, $startDate, $endDate)
    {


        /* loop through activities */
        $query = "
      SELECT
        caa_date as date, 
        caa_starttime as startTime,
        caa_endtime as endTime,
        cat_min_hours as minHours,
        cat_max_hours as maxHours,
        cat_ooh_multiplier as oohMultiplier,
        cat_itemno as itemID
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
        JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
        JOIN item cai ON cai.itm_itemno = cat_itemno
        JOIN custitem ON cui_cuino = pro_contract_cuino 
        JOIN item cit ON cui_itemno = cit.itm_itemno
      WHERE
        cit.itm_itemtypeno = 57
        AND cai.itm_sstk_price > 0
        AND caa_consno = $userID
        AND caa_date BETWEEN '$startDate' AND '$endDate'
        AND( caa_endtime <> caa_starttime )
      ORDER BY
        caa_date";

        $results = $this->db->query($query);

        $totalCost = 0;

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        while ($row = $results->fetch_object()) {

            getRatesAndHours(
                $row->date,
                $row->startTime,
                $row->endTime,
                $row->minHours,
                $row->maxHours,
                $row->oohMultiplier,
                $row->itemID,
                $dsHeader,
                $normalHours,
                $beforeHours,
                $afterHours,
                $overtimeRate,
                $normalRate
            );

            $totalCost += ($normalRate * $normalHours) + ($overtimeRate * ($beforeHours + $afterHours));
        }

        if ($totalCost == null) {
            $totalCost = 0;
        }
        return number_format($totalCost, 2, '.', '');
    }

    function getInHouseHours($userID, $startDate, $endDate)
    {
        $query = "
      SELECT
        SUM(caa_endtime - caa_starttime) as hours
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
      WHERE
        pro_custno = 282
        AND caa_date BETWEEN '$startDate' AND '$endDate'
        AND caa_starttime is not null
        AND caa_endtime is not null and caa_endtime <> ''
        AND caa_consno = $userID";

        //echo $query . '<BR/>';

        $results = $this->db->query($query);
        $row = $results->fetch_object();

        if ($row->hours == null) {
            $ret = 0;
        } else {
            $ret = $row->hours;
        }
        return number_format($ret, 1, '.', '');
    }

}
