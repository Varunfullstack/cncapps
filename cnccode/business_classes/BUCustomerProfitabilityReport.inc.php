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

class BUCustomerProfitabilityReport extends Business
{

    function __construct(&$owner)
    {
        parent::__construct($owner);

        $this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('customerID', '');
    }

    /**
     * total hours
     **/
    function search($dsSearchForm)
    {
        $fromDate = $dsSearchForm->getValue('fromDate');
        $toDate = $dsSearchForm->getValue('toDate');
        $customerID = $dsSearchForm->getValue('customerID');

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $sql =
            "
      SELECT
        custno,
        customerName,
        SUM(cost) AS cost,
        SUM(sale) AS sale,
        SUM(sale) - SUM( cost ) AS profit,
        SUM(hours) AS hours,
        SUM(hours) * " . $dsHeader->getValue('hourlyLabourCost') . " AS cncCost,
        ( SUM( sale ) - SUM( cost ) ) -  ( SUM( hours ) * " . $dsHeader->getValue('hourlyLabourCost') . " ) AS bottomLineProfit,
        SUM( otherTurnover ) AS otherTurnover,
        SUM( maintenanceTurnover ) AS maintenanceTurnover,
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
        0 AS maintenanceTurnover,
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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'";

        if ($customerID) {
            $sql .= " AND inh_custno =  $customerID ";
        }

        $sql .= "
      GROUP BY custno

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
        caa_date BETWEEN '$fromDate' AND '$toDate'";


        if ($customerID) {
            $sql .= " AND pro_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno
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
        LEFT JOIN item ON item.itm_itemno = invline.inl_itemno
        LEFT JOIN itemtype ON item.itm_itemtypeno = itemtype.ity_itemtypeno
      WHERE
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
      AND ( itm_itemtypeno NOT IN(23, 57, 3, 11, 56, 55, 54) OR itm_itemtypeno IS NULL )";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 23";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno

      UNION

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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 57";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno

      UNION

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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 3";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
    GROUP BY custno

      UNION

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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 11";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno

      UNION
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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 56";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
    GROUP BY custno

      UNION

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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 55";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno

      UNION

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
        inh_date_printed BETWEEN '$fromDate' AND '$toDate'
        AND itm_itemtypeno = 54";

        if ($customerID) {
            $sql .= " AND inh_custno = " . $customerID;
        }

        $sql .= "
      GROUP BY custno
      )
      AS temp
      WHERE custno <> 282
      GROUP BY custno";

        return $this->db->query($sql);
    }

}//End of class
?>