<?php
/**
 * Contract profit analysis by customer
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUContractAnalysisReport extends Business
{
    const searchFormContracts = "contracts";
    const searchFormStartYearMonth = "startYearMonth";
    const searchFormEndYearMonth = "endYearMonth";


    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormContracts, DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormContracts, null);
        $dsData->addColumn(self::searchFormStartYearMonth, DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormStartYearMonth, null);
        $dsData->addColumn(self::searchFormEndYearMonth, DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormEndYearMonth, null);
    }

    /**
     * Get a comma-separated list of itemIDs that match contract search string
     * @param $contracts
     * @return bool
     */
    function getContractItemIDs($contracts)
    {
        $sql =
            "
        SELECT
          GROUP_CONCAT( itm_itemno ) AS `IDs`
        FROM
          item
        WHERE
          item.renewalTypeID <> 0
          AND MATCH (itm_desc) AGAINST ( '$contracts' IN BOOLEAN MODE )";

        if ($row = $this->db->query($sql)->fetch_array()) {
            return $row['IDs'];
        } else {
            return false;
        }
    }

    /**
     * Get list of customers with given contracts
     * @param $contractItemIDs
     * @return bool|mysqli_result
     */
    function getCustomers($contractItemIDs)
    {
        $sql =
            "
        SELECT
          DISTINCT cus_custno AS `customerID`,
          cus_name AS `Customer`
        FROM
          customer
          JOIN custitem ON cui_custno = cus_custno
        WHERE
          declinedFlag = 'N'
          AND renewalStatus ='R'";

        if ($contractItemIDs) {
            $sql .= " AND cui_itemno IN ( $contractItemIDs )";
        }

        $sql .= " ORDER BY cus_name";

        return $this->db->query($sql);
    }

    function getLabourHours($customerID,
                            DateTimeInterface $startDate,
                            DateTimeInterface $endDate,
                            $contractItemIDs = false
    )
    {
        $sql =
            "SELECT
        SUM( pro_total_activity_duration_hours ) as hours
        
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        
      WHERE
        pro_total_activity_duration_hours IS NOT NULL
        AND problem.pro_date_raised BETWEEN '" . $startDate->format('Y-m-d') . "' AND '" . $endDate->format('Y-m-d') . "'
        AND pro_custno = $customerID";

        if ($contractItemIDs) {
            $sql .=
                " AND cui_itemno IN ( $contractItemIDs )";
        }
        return $this->db->query($sql)->fetch_array();
    }

    function getContractValues($customerID, $contractItemIDs = false)
    {
        $sql =
            "
        SELECT
          salePricePerMonth,
          costPricePerMonth,
          `cui_cost_price` / 12 AS cui_cost_price,
          `cui_sale_price` / 12 AS cui_sale_price,
          `salePrice`  * `qty` / 12 AS salePrice,
          `costPrice`  * `qty` / 12  AS costPrice,
          `itm_sstk_price` / 12 AS itemSalePrice,
          `itm_sstk_cost` / 12  AS itemCostPrice,
          itm_desc as contract
        FROM
          custitem
          JOIN item ON itm_itemno = cui_itemno
          
        WHERE
          custitem.cui_custno = $customerID
          AND renewalTypeID <> 0
          AND declinedFlag = 'N'";

        if ($contractItemIDs) {
            $sql .=
                " AND cui_itemno IN ( $contractItemIDs )";
        }
        $rows = $this->db->query($sql);

        $perMonthSale = 0;
        $perMonthCost = 0;

        while ($row = $rows->fetch_array()) {

            if ($row['salePricePerMonth'] > 0) {
                $perMonthSale += $row['salePricePerMonth'];
                $perMonthCost += $row['costPricePerMonth'];
            } elseif ($row['cui_sale_price'] > 0) {
                $perMonthSale += $row['cui_sale_price'];
                $perMonthCost += $row['cui_cost_price'];
            } elseif ($row['salePrice'] > 0) {
                $perMonthSale += $row['salePrice'];
                $perMonthCost += $row['costPrice'];
            } else {
                $perMonthSale += $row['itemSalePrice'];
                $perMonthCost += $row['itemCostPrice'];
            }
        }

        return array(
            'perMonthSale' => $perMonthSale,
            'perMonthCost' => $perMonthCost,
        );

    }

    /**
     * @param DSForm $searchForm
     * @return array|bool
     */
    function getResults($searchForm)
    {
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $contracts = $searchForm->getValue(self::searchFormContracts);
        $startDate = (DateTime::createFromFormat(
            "m/Y",
            $searchForm->getValue(self::searchFormStartYearMonth)
        ))->modify('first day of this month ');
        $endDate = (DateTime::createFromFormat(
            "m/Y",
            $searchForm->getValue(self::searchFormEndYearMonth)
        ))->modify('last day of this month');

        $numberOfMonths = $startDate->diff($endDate)->m + ($startDate->diff($endDate)->y * 12);

        $hourlyRate = $dsHeader->getValue(DBEHeader::hourlyLabourCost);

        $nothingFoundForSpecifiedContractString = false;

        if ($contracts) {

            $contractItemIDs = $this->getContractItemIDs($contracts);

            if (is_null($contractItemIDs)) {
                $nothingFoundForSpecifiedContractString = true;
            }
        } else {
            $contractItemIDs = false;
        }

        if ($nothingFoundForSpecifiedContractString) {
            return false;

        }
        $customers = $this->getCustomers($contractItemIDs);

        $customersArray = array();

        while ($row = $customers->fetch_array()) {
            $customersArray[] = $row;
        }
        $results = [];

        foreach ($customersArray as $customer) {
            /*
            Sales
            */
            $labourHoursRow =
                $this->getLabourHours(
                    $customer['customerID'],
                    $startDate,
                    $endDate,
                    $contractItemIDs
                );

            $contractValues =
                $this->getContractValues(
                    $customer['customerID'],
                    $contractItemIDs
                );
            $cost = round($contractValues['perMonthCost'] * $numberOfMonths, 2);
            $sales = round($contractValues['perMonthSale'] * $numberOfMonths, 2);
            $labourCost = round($labourHoursRow[0] * $hourlyRate, 2);
            $prepayValues = $this->getPrepayValues($startDate, $endDate, $customer['customerID'], $contractItemIDs);
            $sales = round($sales + $prepayValues['sales'], 2);
            $profit = $sales - $cost - $labourCost;

            //get prepay data
            $profitPercent = null;
            if ($sales > 0) {
                $profitPercent = number_format(100 - (($cost + $labourCost) / $sales) * 100, 2);
            }


            $results[$customer['Customer']] =
                array(
                    'customerID'    => $customer['customerID'],
                    'sales'         => $sales,
                    'cost'          => $cost,
                    'profit'        => $profit,
                    'labourCost'    => $labourCost,
                    'profitPercent' => $profitPercent,
                    'labourHours'   => $labourHoursRow[0]
                );
        }

        return $results;

    }

    function getPrepayValues(DateTimeInterface $startDate,
                             DateTimeInterface $endDate,
                             $customerId,
                             $contractItemIDs = null
    )
    {
        $query = "SELECT
                  hourlyLabourCharge * hours AS sales,
                  hourlyLabourCost * hours   AS labourCost
                FROM
                  (SELECT
                     (SELECT hed_hourly_labour_cost
                      FROM
                        headert
                      LIMIT 1)                                        AS hourlyLabourCost,
                     (SELECT itm_sstk_price
                      FROM
                        item
                      WHERE itm_itemno = 2237)                        AS hourlyLabourCharge,
                     cui_itemno,
                     (SELECT SUM(
                                 pro_total_activity_duration_hours
                             ) AS hours
                      FROM
                        problem
                        JOIN custitem a
                          ON a.cui_cuino = pro_contract_cuino
                      WHERE pro_total_activity_duration_hours IS NOT NULL
                            AND problem.pro_date_raised BETWEEN '" . $startDate->format('Y-m-d') . "'
                            AND '" . $endDate->format('Y-m-d') . "'
                            AND a.cui_itemno = custitem.`cui_itemno`
                            and pro_custno = $customerId) AS hours
                   FROM
                     custitem
                     JOIN item
                       ON itm_itemno = cui_itemno
                   WHERE custitem.cui_custno = $customerId
                         AND renewalTypeID <> 0
                         AND declinedFlag = 'N'
                         AND itm_desc = 'Pre-Pay Contract') AS prepay";

        if ($contractItemIDs) {
            $query .=
                " where cui_itemno IN ( $contractItemIDs )";
        }

        $rows = $this->db->query($query);

        $sales = 0;
        $labourCost = 0;

        while ($row = $rows->fetch_array()) {
            $sales += $row['sales'];
            $labourCost += $row['labourCost'];
        }

        return array(
            "sales"      => $sales,
            "labourCost" => $labourCost
        );
    }
}
