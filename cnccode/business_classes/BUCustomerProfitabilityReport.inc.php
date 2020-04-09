<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerProfitabilityReport extends Business
{

    const searchFormCustomerID = "customerID";
    const searchFormFromDate = "fromDate";
    const searchFormToDate = "toDate";

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormCustomerID, null);
    }

    /**
     * total hours
     * @param DSForm $dsSearchForm
     * @return bool|mysqli_result
     */
    function search($dsSearchForm)
    {
        $fromDate = $dsSearchForm->getValue(self::searchFormFromDate);
        $toDate = $dsSearchForm->getValue(self::searchFormToDate);
        $customerID = $dsSearchForm->getValue(self::searchFormCustomerID);

        $sql = "SELECT
  cus_custno,
  customer.`cus_name` as customerName,
  cus_create_date AS since,
  cost,
  sale,
  profit,
  hours,
  hours * hed_hourly_labour_cost AS cncCost,
  (sale - cost) - (hours * hed_hourly_labour_cost) AS bottomLineProfit,
  otherTurnover,
  maintenanceTurnover,
  prePayTurnover,
  internetTurnover,
  tAndMTurnover,
  serviceDeskTurnover,
  serverCareTurnover,
  managedTurnover,
  expiryDate,
  initialContractLength as term   
FROM
  customer
      left join 
  (SELECT
  cui_custno,
  MIN(
    DATE_ADD(
      `installationDate`,
      INTERVAL `totalInvoiceMonths` MONTH
    )
  ) AS expiryDate,
  initialContractLength
FROM
  custitem
  LEFT JOIN item
    ON custitem.`cui_itemno` = item.`itm_itemno`
WHERE item.`itm_itemtypeno` IN (55, 56, 58)
  AND declinedFlag <> 'Y'
GROUP BY cui_custno) contractData on contractData.cui_custno = cus_custno
  LEFT JOIN headert
    ON 1
  LEFT JOIN
    (SELECT
      SUM(inl_qty * inl_cost_price) AS cost,
      SUM(inl_qty * inl_unit_price) AS sale,
      SUM(inl_qty * inl_unit_price) - SUM(inl_qty * inl_cost_price) AS profit,
      SUM(
        IF(
          itm_itemtypeno NOT IN (23, 57, 3, 11, 56, 55, 54)
          OR itm_itemtypeno IS NULL,
          inl_qty * inl_unit_price,
          0
        )
      ) AS otherTurnover,
      SUM(
        IF(
          itm_itemtypeno = 23,
          inl_qty * inl_unit_price,
          0
        )
      ) AS maintenanceTurnover,
      SUM(
        IF(
          itm_itemtypeno = 57,
          inl_qty * inl_unit_price,
          0
        )
      ) AS prePayTurnover,
      SUM(
        IF(
          itm_itemtypeno = 3,
          inl_qty * inl_unit_price,
          0
        )
      ) AS internetTurnover,
      SUM(
        IF(
          itm_itemtypeno = 11,
          inl_qty * inl_unit_price,
          0
        )
      ) AS tAndMTurnover,
      SUM(
        IF(
          itm_itemtypeno = 56,
          inl_qty * inl_unit_price,
          0
        )
      ) AS serviceDeskTurnover,
      SUM(
        IF(
          itm_itemtypeno = 55,
          inl_qty * inl_unit_price,
          0
        )
      ) AS serverCareTurnover,
      SUM(
        IF(
          itm_itemtypeno = 54,
          inl_qty * inl_unit_price,
          0
        )
      ) AS managedTurnover,
      inh_custno
    FROM
      invline
      JOIN invhead
        ON inh_invno = inl_invno
      LEFT JOIN item
        ON item.itm_itemno = invline.inl_itemno
      LEFT JOIN itemtype
        ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE inh_date_printed BETWEEN ?
      AND ?
    GROUP BY inh_custno) turnOver
    ON turnOver.inh_custno = cus_custno
  LEFT JOIN
    (SELECT
      SUM(
        TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime)
      ) / 3600 AS hours,
      problem.`pro_custno`
    FROM
      callactivity
      JOIN problem
        ON pro_problemno = caa_problemno
    WHERE caa_date BETWEEN ?
      AND ?
    GROUP BY problem.`pro_custno`) hoursData
    ON hoursData.pro_custno = cus_custno
WHERE cus_custno <> 282
  AND cost IS NOT NULL";

        $paramTypes = "ssss";
        $params = [
            $fromDate,
            $toDate,
            $fromDate,
            $toDate
        ];

        if ($customerID) {
            $sql .= " and cus_custno = ?";
            $paramTypes .= "i";
            $params[] = +$customerID;
        }

        $params = array_merge(
            [$paramTypes],
            $params
        );
        $refArray = [];
        foreach ($params as $key => $value) $refArray[$key] = &$params[$key];

        $statement = $this->db->prepare($sql);
        call_user_func_array(
            [$statement, 'bind_param'],
            $refArray
        );
        return $statement->execute() ? $statement->get_result() : false;
    }
}