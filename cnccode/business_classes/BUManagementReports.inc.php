<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUManagementReports extends Business
{
    /** @var dbSweetcode */
    public $db;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }


    function getSpendByManufacturer($manufacturerName = false, $year = false)
    {
        if (!$year) {
            $year = date('Y');
        }

        $sql =
            "
			SELECT
				manufact.man_name AS manufacturer ";

        for ($month = 1; $month <= 12; $month++) {

            $sql .= $this->buildSpendByManufacturerSegment($month);

        }

        $sql .= "		
			FROM
				porline
				JOIN porhead ON poh_porno = pol_porno
				LEFT JOIN item ON itm_itemno= pol_itemno
				LEFT JOIN manufact ON man_manno = itm_manno
				
				
			WHERE
				YEAR(poh_date) = '$year'
				AND poh_type = 'A'
				AND pol_qty_ord * pol_cost > 0";

        if ($manufacturerName) {
            $sql .= "
				AND man_name LIKE '%$manufacturerName%'";
        }

        $sql .= "
			GROUP BY
				man_manno
			ORDER BY
				man_name;";

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

    /**
     * @param null $customerID
     * @param null $year
     * @param null $sectorId
     * @param null $pcs
     * @return bool|int|mysqli_result|null
     */
    function getSalesByCustomer($customerID = null, $year = null, $sectorId = null, $minPcs = null, $maxPCs = null)
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
				cus_name AS customer,
			    cus_became_customer_date as becameCustomerDate,
        noOfPCs,
        noOfServers,
        (select count(*) from address where add_custno = customer.cus_custno) as noOfSites  ,
        sec_desc AS sector ";

        for ($month = 1; $month <= 12; $month++) {

            $sql .= $this->buildSalesByCustomerSegment($month);

        }

        $sql .= "
			FROM
				invline
				JOIN invhead ON inh_invno = inl_invno
				JOIN customer ON cus_custno = inh_custno
        JOIN sector ON cus_sectorno = sector.sec_sectorno
			WHERE
				YEAR(inh_date_printed) = '$year'
				AND inl_line_type = 'I'";


        if ($customerID) {
            $sql .= "
					AND cus_custno = $customerID ";
        }

        if ($sectorId) {
            $sql .= " and cus_sectorno = $sectorId";
        }

        if ($minPcs !== null) {
            $sql .= " and noOfPCs >= " . $minPcs;
        }

        if ($maxPCs !== null) {
            $sql .= " and noOfPCs <= " . $maxPCs;
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