<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_bu"] . '/BUItem.inc.php');

class BUCustomerAnalysisReport extends Business
{

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('customerID', '');
        $dsData->addColumn('startYearMonth', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('startYearMonth', '');
        $dsData->addColumn('endYearMonth', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('endYearMonth', '');
    }

    /**
     * Get list of contract items in the date/customer range
     * @param $customerID
     * @return bool|mysqli_result
     */
    function getContractItems($customerID)
    {
        $sql =
            "
        SELECT
          itm_itemno AS `ID`,
          itm_desc AS `Contract`
          
        FROM
          custitem
          JOIN item ON itm_itemno = cui_itemno
          
        WHERE
          item.renewalTypeID <> 0
          AND declinedFlag = 'N'";

        if ($customerID) {
            $sql .= " AND cui_custno = $customerID";
        }

        $sql .=
            " GROUP BY
          itm_itemno
        ORDER BY
          itm_desc";

        return $this->db->query($sql);
    }

    function getTandMLabourHours($customerID, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $sql =
            "
      SELECT
        SUM( inl_qty ) as hours
        
      FROM
        invline
        JOIN invhead ON inh_invno = inl_invno
        
      WHERE
        inh_type = 'I'
        AND inl_line_type = 'I'
        AND inh_date_printed_yearmonth BETWEEN '" . $startDate->format('Ym') . "' AND '" . $endDate->format('Ym') . "'
        AND inl_itemno = " . CONFIG_CONSULTANCY_DAY_LABOUR_ITEMID;

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_array();
    }

    /**
     * @param $customerID
     * @param $contractId
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @return mixed
     */
    function getContractLabourHours($customerID, $contractId, DateTimeInterface $startDate, DateTimeInterface $endDate)
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
        AND cui_itemno = $contractId";

        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }
        return $this->db->query($sql)->fetch_array();
    }

    function getContractValues($customerID, $contractId)
    {
        $sql =
            "
        SELECT
          SUM( `salePricePerMonth` ) AS salePricePerMonth,
          SUM( `costPricePerMonth` ) AS costPricePerMonth,
          SUM( `cui_cost_price` / 12 ) AS cui_cost_price,
          SUM( `cui_sale_price` / 12 ) AS cui_sale_price,
          SUM( `salePrice`  * `qty` / 12 ) AS salePrice,
          SUM( `costPrice`  * `qty` / 12 ) AS costPrice,
          SUM( `itm_sstk_price` / 12 ) AS itemSalePrice,
          SUM( `itm_sstk_cost` / 12 ) AS itemCostPrice
          
        FROM
          custitem
          JOIN item ON itm_itemno = cui_itemno
          
        WHERE
          cui_itemno = $contractId
          AND renewalTypeID <> 0          
          AND declinedFlag = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }
        $row = $this->db->query($sql)->fetch_array();

        /* Per month values in different fields despending upon renewal type */
        if ($row['salePricePerMonth'] > 0) {
            $perMonthSale = $row['salePricePerMonth'];
            $perMonthCost = $row['costPricePerMonth'];
        } elseif ($row['cui_sale_price'] > 0) {
            $perMonthSale = $row['cui_sale_price'];
            $perMonthCost = $row['cui_cost_price'];
        } elseif ($row['salePrice'] > 0) {
            $perMonthSale = $row['salePrice'];
            $perMonthCost = $row['costPrice'];
        } else {
            $perMonthSale = $row['itemSalePrice'];
            $perMonthCost = $row['itemCostPrice'];
        }
        return array(
            'perMonthSale' => $perMonthSale,
            'perMonthCost' => $perMonthCost,
        );

    }

    function getOtherSales($customerID, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $sql =
            "
    SELECT
      SUM( inl_qty *  inl_cost_price) AS `cost`,
      SUM( inl_qty * inl_unit_price ) AS `sale`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON inl_itemno = itm_itemno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth BETWEEN '" . $startDate->format('Ym') . "' AND '" . $endDate->format('Ym') . "'
      AND item.renewalTypeID = 0"; // excludes contracts

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_array();
    }


    function getResults(&$searchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $customerID = $searchForm->getValue('customerID');

        $startDate = DateTime::createFromFormat(
            "m/Y",
            $searchForm->getValue('startYearMonth')
        )->modify('first day of this month ');
        $endDate = DateTime::createFromFormat(
            "m/Y",
            $searchForm->getValue('endYearMonth')
        )->modify('last day of this month');

        $diff = $startDate->diff($endDate);

        $numberOfMonths = round($diff->days/30);

        $hourlyRate = $dsHeader->getValue('hourlyLabourCost');

        $test = new BUItem($this);
        /**
         * @var DataSet $data
         */
        $data = null;
        $test->getItemByID(2237, $data);

        $hourlyLabourCharge = $data->getValue('curUnitSale');

        $contractItems = $this->getContractItems($customerID);
        $contractItemsArray = array();

        while ($row = $contractItems->fetch_array()) {
            $contractItemsArray[] = $row;
        }

        foreach ($contractItemsArray as $item) {


            $labourHoursRow =
                $this->getContractLabourHours(
                    $customerID,
                    $item['ID'],
                    $startDate,
                    $endDate
                );

            $contractValues =
                $this->getContractValues(
                    $customerID,
                    $item['ID']
                );

            $cost = round($contractValues['perMonthCost'] * $numberOfMonths, 2);

            $sales = round($contractValues['perMonthSale'] * $numberOfMonths, 2);
            if ($item['Contract'] === 'Pre-Pay Contract') {
                $sales = round($hourlyLabourCharge * $labourHoursRow[0], 2);
            }

            $labourCost = round($labourHoursRow[0] * $hourlyRate, 2);

            $profit = $sales - $cost - $labourCost;

            if ($sales > 0) {
                $profitPercent = number_format(100 - (($cost + $labourCost) / $sales) * 100, 2);
            } else {
                $profitPercent = '';
            }

            $results[$item['Contract']] =
                array(
                    'sales' => $sales,
                    'cost' => $cost,
                    'profit' => $profit,
                    'profitPercent' => $profitPercent,
                    'labourCost' => $labourCost,
                    'labourHours' => $labourHoursRow[0]
                );
        }

        $otherSales =
            $this->getOtherSales(
                $customerID,
                $startDate,
                $endDate
            );

        $otherSalesHoursRow = $this->getTandMLabourHours($customerID, $startDate, $endDate);

        $cost = round($otherSales['cost'], 2);

        $sales = round($otherSales['sale'], 2);

        $labourCost = round($otherSalesHoursRow['hours'] * $hourlyRate, 2);

        $profit = $sales - $cost - $labourCost;

        if ($sales > 0) {
            $profitPercent = number_format(100 - (($cost + $labourCost) / $sales) * 100, 2);
        } else {
            $profitPercent = '';
        }

        $results['Other Sales'] =
            array(
                'sales' => $sales,
                'cost' => $cost,
                'profit' => $profit,
                'profitPercent' => $profitPercent,
                'labourCost' => $labourCost,
                'labourHours' => $otherSalesHoursRow['hours']
            );

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