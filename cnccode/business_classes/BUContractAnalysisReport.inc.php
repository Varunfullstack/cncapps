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

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('contracts', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('contracts', '');
        $dsData->addColumn('startYearMonth', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('startYearMonth', '');
        $dsData->addColumn('endYearMonth', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('endYearMonth', '');
    }

    /**
     * Get a comma-separated list of itemIDs that match contract search string
     **/
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
     **/
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
            $sql .=
                " AND cui_itemno IN ( $contractItemIDs )";
        }

        $sql .=
            " ORDER BY
          cus_name";

        return $this->db->query($sql);
    }

    function getLabourHours($customerID, $startYearMonth, $endYearMonth, $contractItemIDs = false)
    {
        $sql =
            "SELECT
        SUM( pro_total_activity_duration_hours ) as hours
        
      FROM
        problem
        JOIN custitem ON cui_cuino = pro_contract_cuino
        
      WHERE
        pro_total_activity_duration_hours IS NOT NULL
        AND problem.pro_date_raised BETWEEN '$startYearMonth-01' AND '$endYearMonth-31'
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
          `itm_sstk_cost` / 12  AS itemCostPrice
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

    function getResults($searchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $contracts = $searchForm->getValue('contracts');

        $startYearMonth = $searchForm->getValue('startYearMonth');
        $endYearMonth = $searchForm->getValue('endYearMonth');

        $startYearMonthCompact = str_replace('-', '', $startYearMonth);
        $endYearMonthCompact = str_replace('-', '', $endYearMonth);

        $numberOfMonths = $this->getMonthsBetweenYearMonths($startYearMonth, $endYearMonth);

        $hourlyRate = $dsHeader->getValue('hourlyLabourCost');

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

        foreach ($customersArray as $customer) {
            /*
            Sales
            */
            $labourHoursRow =
                $this->getLabourHours(
                    $customer['customerID'],
                    $startYearMonth,
                    $endYearMonth,
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

            $profit = $sales - $cost - $labourCost;

            if ($sales > 0) {
                $profitPercent = number_format(100 - (($cost + $labourCost) / $sales) * 100, 2);
            } else {
                $profitPercent = '';
            }


            $results[$customer['Customer']] =
                array(
                    'customerID' => $customer['customerID'],
                    'sales' => $sales,
                    'cost' => $cost,
                    'profit' => $profit,
                    'labourCost' => $labourCost,
                    'profitPercent' => $profitPercent,
                    'labourHours' => $labourHoursRow[0]
                );
        }

        return $results;

    }

    function getMonthsBetweenYearMonths($startYearMonth, $endYearMonth)
    {
        $d1 = new DateTime($startYearMonth . "-01");
        $d2 = new DateTime($endYearMonth . "-28");

        $months = 0;

        $d1->add(new \DateInterval('P1M'));

        while ($d1 <= $d2) {
            $months++;
            $d1->add(new \DateInterval('P1M'));
        }

        return $months + 1;
    }
}//End of class
?>