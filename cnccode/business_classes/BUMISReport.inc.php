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

class BUMISReport extends Business
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
        $dsData->addColumn('months', DA_INTEGER, DA_ALLOW_NULL);
        $dsData->setValue('months', 12);
    }

    /**
     * Average monthly contract billing
     **/
    function getAverageMonthlyContractValues($customerID)
    {
        $sql =
            "
        SELECT
          itm_itemno AS `ID`,
          itm_desc AS `Contract`,
          SUM( custitem.`cui_sale_price` - custitem.`cui_cost_price`) / 12  AS `Value`
        FROM
          custitem_contract
          JOIN custitem ON cui_cuino = cic_contractcuino
          JOIN item ON itm_itemno = cui_itemno
        WHERE
          item.renewalTypeID = 2
          AND `declinedFlag` = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }

        $sql .=
            " GROUP BY
          itm_itemno
        ORDER BY
          itm_desc";

        return $this->db->query($sql);
    }

    /**
     * Actual year-to-date contract billing by contract
     **/
    function getActualBillingByContractAndMonth($customerID, $itemID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND inl_itemno = $itemID";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getActualBroadbandByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON itm_itemno = inl_itemno
      JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND ity_stockcat = 'B'
      AND (
        inl_desc LIKE '%cnc broadband%'
        OR
        inl_desc LIKE '%leased%'
        )";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getActualDomainByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON itm_itemno = inl_itemno
      JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND ity_stockcat = 'B'
      AND inl_desc LIKE '%domain%'";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getActualHostingByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON itm_itemno = inl_itemno
      JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND renewalTypeID = 5";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getOtherSalesProfitByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON inl_itemno = itm_itemno
      JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND ity_stockcat IN( 'U','D','C','E','H','S','T', 'F','O','P','Q','A','V','Z')";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getCreditByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
    WHERE
      inh_type = 'C'
      AND inh_date_printed_yearmonth = '$yearMonth'";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getPrePayProfitByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND ( inl_itemno = " . CONFIG_DEF_PREPAY_TOPUP_ITEMID .
            " OR inl_itemno = " . CONFIG_DEF_PREPAY_ITEMID . ")";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getTAndMProfitByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN item ON inl_itemno = itm_itemno
      JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND itm_itemtypeno = 11";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getSSLProfitByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND inl_desc LIKE '%SSL Certificate%'";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }
        return $this->db->query($sql)->fetch_row();
    }

    function getManualInvoiceProfitByMonth($customerID, $yearMonth)
    {
        $sql =
            "
      SELECT
      SUM(inl_qty * (inl_unit_price - inl_cost_price)) AS `Value`
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
    WHERE
      inh_type = 'I'
      AND inl_line_type = 'I'
      AND inh_date_printed_yearmonth = '$yearMonth'
      AND inl_itemno = 0";

        if ($customerID) {
            $sql .= " AND invhead.inh_custno = $customerID";
        }
        return $this->db->query($sql)->fetch_row();
    }

    function getCncBroadbandValue($customerID)
    {
        $sql =
            "
        SELECT
          SUM( `salePricePerMonth` - `costPricePerMonth`) AS `Value`
        FROM
          custitem_contract
          JOIN custitem ON cui_cuino = cic_contractcuino
          JOIN item ON itm_itemno = cui_itemno
        WHERE
          item.renewalTypeID = 1
          AND `declinedFlag` = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getQuotationValue($customerID)
    {
        $sql =
            "
        SELECT
          SUM( qty * (`salePrice` - `costPrice`)) / 12 AS `Value`
        FROM
          custitem_contract
          JOIN custitem ON cui_cuino = cic_contractcuino
          JOIN item ON itm_itemno = cui_itemno
        WHERE
          item.renewalTypeID = 3
          AND `declinedFlag` = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getCncDomianValue($customerID)
    {
        $sql =
            "
        SELECT
          SUM( custitem.`cui_sale_price` - custitem.`cui_cost_price`) / 12  AS `Value`
        FROM
          custitem_contract
          JOIN custitem ON cui_cuino = cic_contractcuino
          JOIN item ON itm_itemno = cui_itemno
        WHERE
          item.renewalTypeID = 4
          AND `declinedFlag` = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getCncHostingValue($customerID)
    {
        $sql =
            "
        SELECT
          SUM( custitem.`cui_sale_price` - custitem.`cui_cost_price`) / 12  AS `Value`
        FROM
          custitem_contract
          JOIN custitem ON cui_cuino = cic_contractcuino
          JOIN item ON itm_itemno = cui_itemno
        WHERE
          item.renewalTypeID = 5
          AND `declinedFlag` = 'N'";

        if ($customerID) {
            $sql .= " AND custitem.cui_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getTAndMHours($customerID, $yearMonth)
    {
        $sql =
            "
        SELECT
          SUM( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 as Hours
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
        WHERE
          caa_date_yearmonth = '$yearMonth'
          AND pro_contract_cuino = 0";

        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getServerCareHours($customerID, $yearMonth)
    {
        $sql =
            "
        SELECT
          SUM( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 as Hours
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN custitem ON pro_contract_cuino = cui_cuino
          JOIN item ON cui_itemno = itm_itemno
          
        WHERE
          caa_date_yearmonth = '$yearMonth'
          AND itm_itemtypeno = " . CONFIG_SERVERCARE_ITEMTYPEID;

        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getServiceDeskHours($customerID, $yearMonth)
    {
        $sql =
            "
        SELECT
          SUM( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 as Hours
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN custitem ON pro_contract_cuino = cui_cuino
          JOIN item ON cui_itemno = itm_itemno
          
        WHERE
          caa_date_yearmonth = '$yearMonth'
          AND itm_itemtypeno = " . CONFIG_SERVICEDESK_ITEMTYPEID;

        if ($customerID) {
            $sql .= " AND pro_custno = $customerID";
        }

        return $this->db->query($sql)->fetch_row();
    }

    function getPrePayHours($customerID, $yearMonth)
    {
        $sql =
            "
        SELECT
          SUM( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 as Hours
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN custitem ON pro_contract_cuino = cui_cuino
          JOIN item ON cui_itemno = itm_itemno
          
        WHERE
          caa_date_yearmonth = '$yearMonth'
          AND itm_itemtypeno = " . CONFIG_PREPAY_ITEMTYPEID;

        if ($customerID) {
            $sql .= " AND cui_custno = $customerID";
        }
        return $this->db->query($sql)->fetch_row();
    }

    function getContractCsv(&$searchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $customerID = $searchForm->getValue('customerID');

        $months = $searchForm->getValue('months');
        $dateYMD = date('Y') . '-' . date('m') . '-01';
        $baseDate = strtotime($dateYMD);

        if (!$months) {
            $months = 12;
        }

        $hourlyRate = $dsHeader->getValue('hourlyLabourCost');

        $returnFile = '';

        $averageMonthlyContractValues = $this->getAverageMonthlyContractValues($customerID);

        /*
        Build an array
        */
        while ($row = $averageMonthlyContractValues->fetch_array()) {
            $averageMonthlyContractArray[] = $row;
        }

        foreach ($averageMonthlyContractArray as $key => $row) {

            $returnFile .= $row['Contract'] . ',' . number_format($row['Value'], 2, '.', '');


            for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

                $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

                $actual = $this->getActualBillingByContractAndMonth($customerID, $row['ID'], $yearMonth);

                $returnFile .= ',' . number_format($actual[0], 2, '.', '');

            }

            $returnFile .= "\n";

        }
        $returnFile .= "\n";
        /*
        Other Sales Profit
        */
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getOtherSalesProfitByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $otherSalesProfit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'Other Sales' . ',' . number_format($total / $months, 2, '.', '') . $otherSalesProfit;

        /*
        Pre-pay Profit
        */
        $returnFile .= "\n";

        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getPrePayProfitByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $prePayProfit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'Pre-Pay' . ',' . number_format($total / $months, 2, '.', '') . $prePayProfit;

        /*
        T&M Profit
        */
        $returnFile .= "\n";

        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getTAndMProfitByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $profit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'T&M' . ',' . number_format($total / $months, 2, '.', '') . $profit;

        $returnFile .= "\n";

        $broadBandValueResult = $this->getCncBroadbandValue($customerID);

        $returnFile .= 'Broadband' . ',' . number_format($broadBandValueResult[0], 2, '.', '');

        $monthIncrement = 1;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getActualBroadbandByMonth($customerID, $yearMonth);

            $returnFile .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= "\n";

        $hostingValueResult = $this->getCncHostingValue($customerID);

        $returnFile .= 'Hosting' . ',' . number_format($hostingValueResult[0], 2, '.', '');

        $monthIncrement = 1;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getActualHostingByMonth($customerID, $yearMonth);

            $returnFile .= ',' . number_format($actual[0], 2, '.', '');

        }

        /*
        domains
        */
        $returnFile .= "\n";

        $domainValueResult = $this->getCncDomianValue($customerID);

        $returnFile .= 'Domains' . ',' . number_format($domainValueResult[0], 2, '.', '');

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getActualDomainByMonth($customerID, $yearMonth);

            $returnFile .= ',' . number_format($actual[0], 2, '.', '');

        }
        /*
        SSL
        */

        $returnFile .= "\n";

        $total = 0;
        $profit = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getSSLProfitByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $profit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'SSL' . ',' . number_format($total / $months, 2, '.', '') . $profit;
        /*
        Manual invoices (no item)
        */
        $returnFile .= "\n";

        $profit = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getManualInvoiceProfitByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $profit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'Manual Invoices' . ',' . number_format($total / $months, 2, '.', '') . $profit;
        /*
        credit notes
        */
        $returnFile .= "\n";

        $profit = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getCreditByMonth($customerID, $yearMonth);

            $total += $actual[0];

            $profit .= ',' . number_format($actual[0], 2, '.', '');

        }

        $returnFile .= 'Credits' . ',' . number_format($total / $months, 2, '.', '') . $profit;

        $returnFile .= "\n";
        $returnFile .= "\n";
        /*
        T&M Cost
        */
        $returnFile .= "\n";

        $monthlyCosts = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getTAndMHours($customerID, $yearMonth);

            $value = $actual[0] * $hourlyRate;

            $total += $value;

            $monthlyCosts .= ',' . number_format($value, 2, '.', '');

        }

        $returnFile .= 'T&M Cost' . ',' . number_format($total / $months, 2, '.', '') . $monthlyCosts;
        /*
        ServerCare Cost
        */
        $returnFile .= "\n";

        $monthlyCosts = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getServerCareHours($customerID, $yearMonth);

            $value = $actual[0] * $hourlyRate;

            $total += $value;

            $monthlyCosts .= ',' . number_format($value, 2, '.', '');

        }

        $returnFile .= 'ServerCare Cost' . ',' . number_format($total / $months, 2, '.', '') . $monthlyCosts;

        /*
        ServiceDesk Cost
        */
        $returnFile .= "\n";

        $monthlyCosts = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getServiceDeskHours($customerID, $yearMonth);

            $value = $actual[0] * $hourlyRate;

            $total += $value;

            $monthlyCosts .= ',' . number_format($value, 2, '.', '');

        }

        $returnFile .= 'ServiceDesk Cost' . ',' . number_format($total / $months, 2, '.', '') . $monthlyCosts;

        /*
        PrePay Cost
        */
        $returnFile .= "\n";

        $monthlyCosts = '';
        $total = 0;

        for ($monthIncrement = 1; $monthIncrement <= $months; $monthIncrement++) {

            $yearMonth = date('Ym', strtotime('-' . $monthIncrement . ' month', $baseDate));

            $actual = $this->getPrePayHours($customerID, $yearMonth);

            $value = $actual[0] * $hourlyRate;

            $total += $value;

            $monthlyCosts .= ',' . number_format($value, 2, '.', '');

        }

        $returnFile .= 'PrePay Cost' . ',' . number_format($total / $months, 2, '.', '') . $monthlyCosts;


        $returnFile .= "\n";
        $returnFile .= "\n";

        $result = $this->getQuotationValue($customerID);

        $returnFile .= 'Renewal Quotes' . ',' . number_format($result[0], 2, '.', '');

        return $returnFile;

    }
}//End of class
?>