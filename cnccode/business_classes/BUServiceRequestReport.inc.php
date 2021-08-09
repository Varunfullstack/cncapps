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

class BUServiceRequestReport extends Business
{
    const searchFormCustomerID = "customerID";
    const searchFormFromDate = "fromDate";
    const searchFormToDate = "toDate";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    /**
     * @param $dsData
     */
    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormCustomerID, null);
    }

    /**
     * @param DSForm $dsSearchForm
     * @param bool $hasInialActivity
     * @return bool|mysqli_result
     */
    function search(&$dsSearchForm, $hasInialActivity = false)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $inialActivity = "";
        if ($hasInialActivity)
            $inialActivity = ",(select min(caa_callactivityno) from callactivity where caa_problemno=pro_problemno and caa_callacttypeno=51) inialActivity";
        $query =
            "
        SELECT 
          DATE_FORMAT(pro_date_raised, '%Y-%m-%d') AS `RaisedDate`,
          DATE_FORMAT(pro_date_raised, '%H:%i') AS `RaisedTime`,
          DATE_FORMAT(pro_fixed_date, '%Y-%m-%d') AS `FixedDate`,
          DATE_FORMAT(pro_fixed_date, '%H:%i') AS `FixedTime`,
          (
          SELECT
            MAX( created )
          FROM
            callactivity
          WHERE
            caa_problemno = pro_problemno
          ) AS `lastUpdated`,
          (
          SELECT
            MIN( created )
          FROM
            callactivity
          WHERE
            caa_problemno = pro_problemno
          ) AS `created`,
          cus_name AS `Customer`,
          concat(con_first_name, ' ', con_last_name) AS `Contact`,
          pro_priority AS `Priority`,
          rootcause.rtc_desc AS `RootCause`,
          pro_problemno AS `CallReference`,
          pro_total_activity_duration_hours AS `TotalHours`,
          pro_responded_hours AS `ResponseHours`,
          pro_sla_response_hours AS `MinContractResponseHours`,
          pro_sla_response_hours - pro_responded_hours AS `DiffResponseContract`,
          pro_working_hours AS `FixHours`,
          consultant.cns_name AS `FixEngineer`,
          CONCAT(
            IFNULL(item.itm_desc, ''),
            ' ',
            IFNULL(add_postcode, ''),
            ' ',
            IFNULL(adslPhone, '')
          ) AS `Contract`,
          prt.description AS raiseType,
          pro_status AS status,
          pro_contract_cuino,
          emailSubjectSummary As emailSummary    
          $inialActivity
        FROM
          problem 
          LEFT JOIN customer 
            ON cus_custno = pro_custno 
          LEFT JOIN contact
            ON con_contno = pro_contno
          LEFT JOIN rootcause 
            ON problem.pro_rootcauseno = rootcause.rtc_rootcauseno 
          LEFT JOIN consultant 
            ON cns_consno = pro_fixed_consno 
          LEFT JOIN custitem 
            ON cui_cuino = pro_contract_cuino 
          LEFT JOIN address
            ON add_custno = cui_custno AND add_siteno = cui_siteno
          LEFT JOIN item 
            ON itm_itemno = cui_itemno
          LEFT JOIN ProblemRaiseType prt
            ON problem.raiseTypeId=prt.id
          WHERE 1=1";

        if ($dsSearchForm->getValue(self::searchFormFromDate)) {
            $query .= " AND date(pro_date_raised) >= '" . $dsSearchForm->getValue(self::searchFormFromDate) . "'";
        }

        if ($dsSearchForm->getValue(self::searchFormToDate)) {
            $query .= " AND date(pro_date_raised) <= '" . $dsSearchForm->getValue(self::searchFormToDate) . "'";
        }

        if ($dsSearchForm->getValue(self::searchFormCustomerID)) {
            $query .=
                " AND pro_custno = " . $dsSearchForm->getValue(self::searchFormCustomerID);
        }

        $query .= " ORDER BY pro_date_raised";

        $result = $this->db->query($query);


        return $result;
    }
}
