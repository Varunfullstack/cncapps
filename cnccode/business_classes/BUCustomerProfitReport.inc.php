<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerProfitReport extends Business
{

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    /**
     * total hours
     **/
    function getReport($startDate, $endDate, $customerID = false)
    {
        $sql =
            "
      SELECT
      custno,
      customerName,
      SUM(cost) AS cost,
      SUM(sale) AS sale,
      SUM(sale) - SUM( cost ) AS profit,
      SUM(hours) AS hours,
      SUM(hours) * 25 AS cncCost,
      ( SUM( sale ) - SUM( cost ) ) -  ( SUM( hours ) * 25 ) AS bottomLineProfit,
      SUM( otherTurnover ) AS otherTurnover,
      SUM( maintananceTurnover ) AS maintananceTurnover,
      SUM( prePayTurnover ) AS prePayTurnover,
      SUM( internetTurnover ) AS internetTurnover,
      SUM( tAndMTurnover ) AS tAndMTurnover,
      SUM( serviceDeskTurnover ) AS serviceDeskTurnover,
      SUM( serverCareTurnover ) AS serverCareTurnover,
      SUM( managedTurnover ) AS managedTurnover
      
      FROM
      (
      SELECT
        inh_custno AS custno,
        cus_name AS customerName,
        SUM(inl_qty * inl_cost_price) AS cost,
        SUM(inl_qty * inl_unit_price) AS sale,
        0 AS hours,
        0 AS otherTurnover,
        0 AS maintananceTurnover,
        0 AS prePayTurnover,
        0 AS internetTurnover,
        0 AS tAndMTurnover,
        0 AS serviceDeskTurnover,
        0 AS serverCareTurnover,
        0 AS managedTurnover
      FROM
        invline
        JOIN invhead ON inh_invno = inl_invno
        JOIN customer ON cus_custno = inh_custno
      WHERE
        inh_date_printed BETWEEN '$startDate' AND '$endDate'";

        if ($customerID) {

            $sql .= " AND custno = $customerID";

        }

        $sql .= " GROUP BY custno";

        $sql .= "
    
      UNION

      SELECT
        pro_custno AS custno,
        cus_name AS customerName,
        0,
        0,
        SUM( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC(caa_starttime ) ) / 3600 AS hours,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
        JOIN customer ON cus_custno = pro_custno
      WHERE
        caa_date BETWEEN '$startDate' AND '$endDate'";

        if ($customerID) {

            $sql .= " AND custno = $customerID";

        }

        $sql .= " GROUP BY custno";

        $sql .= "

      UNION

      SELECT
        inh_custno AS custno,
        cus_name AS customerName,
        0,
        0,
        0,
        SUM(inl_qty * inl_unit_price) AS otherTurnover,
        0,
        0,
        0,
        0,
        0,
        0,
        0
      FROM
        invline
        JOIN invhead ON inh_invno = inl_invno
        JOIN customer ON cus_custno = inh_custno
        JOIN item ON item.itm_itemno = invline.inl_itemno
        JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
      WHERE
        inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
        AND ity_stockcat IN ( 'S', 'H', 'U', 'F', 'D', 'C', 'E' )

    GROUP BY custno

    UNION

    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS maintenanceTurnover,
      0,
      0,
      0,
      0,
      0,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 23

    GROUP BY custno

    UNION
    #pre-pay
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS prePayTurnover,
      0,
      0,
      0,
      0,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 57
      #AND caa_custno = 4763

    GROUP BY custno

    UNION
    #internet
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS internetTurnover,
      0,
      0,
      0,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 3
      #AND caa_custno = 4763

    GROUP BY custno

    UNION
    #T & M
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS tAndMTurnover,
      0,
      0,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 11
      #AND caa_custno = 4763

    UNION
    #Service Desk
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS serviceDeskTurnover,
      0,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 56
      #AND caa_custno = 4763
    GROUP BY custno

    UNION
    #Server Care
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS serverCareTurnover,
      0
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 55
      #AND caa_custno = 4763
    GROUP BY custno

    UNION
    #managed
    SELECT
      inh_custno AS custno,
      cus_name AS customerName,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      0,
      SUM(inl_qty * inl_unit_price) AS managedTurnover
      
    FROM
      invline
      JOIN invhead ON inh_invno = inl_invno
      JOIN customer ON cus_custno = inh_custno
      JOIN item ON item.itm_itemno = invline.inl_itemno
      JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
      
    WHERE
      inh_date_printed BETWEEN '2009-01-01' AND '2009-12-31'
      AND itm_itemtypeno = 54
      #AND caa_custno = 4763
    GROUP BY custno
    )
    AS temp
    WHERE custno <> 282
    GROUP BY custno";

        if ($customerID) {
            $sql .= "AND cus_custno = $customerID";
        }

        return $this->db->query($sql);

        /**
         * total sales
         **/
        function getTotalCostAndSales($startDate, $endDate, $customerID = false)
        {
            $sql = "
      SELECT
        SUM(inl_qty * inl_cost_price) as Cost,
        SUM(inl_qty * inl_unit_price) as Sale
      FROM
        invline
        JOIN invhead ON inh_invno = inl_invno
      WHERE
        caa_date BETWEEN '$startDate' AND '$endDate'";

            if ($customerID) {
                $sql .= " AND caa_custno = $customerID";
            }

            return $this->db->query($sql);

        }

        function getSpendByCategory($year = false)
        {
            if (!$year) {
                $year = date('Y');
            }

            $sql =
                "
			SELECT
				stc_desc AS category,
				stc_stockcat As `code` ";

            for ($month = 1; $month <= 12; $month++) {

                $sql .= $this->buildSpendByManufacturerSegment($month);

            }

            $sql .= "		
			FROM
				porline
				JOIN porhead ON poh_porno = pol_porno
				JOIN supplier ON poh_suppno = sup_suppno
				JOIN item ON pol_itemno = itm_itemno
				JOIN stockcat on stc_stockcat = itm_stockcat
			WHERE
				YEAR(poh_date) = '$year'
				AND poh_type = 'A'
			GROUP BY
				stc_desc
			;";

            return $this->db->query($sql);

        }

        function getSpendBySupplier($supplierID = false, $year = false)
        {
            if (!$year) {
                $year = date('Y');
            }

            $sql =
                "
			SELECT
				supplier.sup_name AS supplier ";

            for ($month = 1; $month <= 12; $month++) {

                $sql .= $this->buildSpendByManufacturerSegment($month);

            }

            $sql .= "		
			FROM
				porline
				JOIN porhead ON poh_porno = pol_porno
				JOIN supplier ON sup_suppno = poh_suppno
				
				
			WHERE
				YEAR(poh_date) = '$year'
				AND poh_type = 'A'
				AND pol_qty_ord * pol_cost > 0";

            if ($supplierID) {
                $sql .= "
				AND sup_suppno = $supplierID";
            }

            $sql .= "
			GROUP BY
				sup_suppno
			ORDER BY
				sup_name;";

            return $this->db->query($sql);

        }

        function buildSpendByManufacturerSegment($month)
        {
            $return = "
			,SUM(
				if (
					MONTH( poh_date ) = $month,
					pol_qty_ord * pol_cost,
					0
				)
			)as `month$month`";

            return $return;
        }

        function getSalesByCustomer($customerID = false, $year = false)
        {
            if (!$year) {
                $year = date('Y');
            }

            $sql =
                "
		SELECT
			*,
			salesMonth1 + salesMonth2 + salesMonth3 + salesMonth4 + salesMonth5 +salesMonth6 +salesMonth7 +salesMonth8 +salesMonth9 +salesMonth10 + salesMonth11 + salesMonth12  AS salesTotal,
			profitMonth1 + profitMonth2 +profitMonth3 +profitMonth4 +profitMonth5 +profitMonth6 +profitMonth7 +profitMonth8 +profitMonth9 +profitMonth10+profitMonth11 +profitMonth12  AS profitTotal
		FROM
		(			
			SELECT
				cus_name AS customer";

            for ($month = 1; $month <= 12; $month++) {

                $sql .= $this->buildSalesByCustomerSegment($month);

            }

            $sql .= "
			FROM
				invline
				JOIN invhead ON inh_invno = inl_invno
				JOIN customer ON cus_custno = inh_custno
			WHERE
				YEAR(inh_date_printed) = '$year'
				AND inl_qty * inl_unit_price > 0
				AND inl_line_type = 'I'";


            if ($customerID) {
                $sql .= "
					AND cus_custno = $customerID ";
            }

            $sql .= "
				GROUP BY
					inh_custno
				ORDER BY
					cus_name
			)  AS temp
			ORDER BY profitTotal DESC;";

            return $this->db->query($sql);

        }

        function buildSalesByCustomerSegment($month)
        {
            $return = "
			,SUM(
				if (
					MONTH( inh_date_printed ) = $month,
					inl_qty * inl_unit_price,
					0
				)
			)as `salesMonth$month`,
			SUM(
				if (
					MONTH( inh_date_printed ) = $month,
					inl_qty * inl_unit_price - inl_qty * inl_cost_price,
					0
				)
			)as `profitMonth$month`";

            return $return;
        }

    }// End of class
?>