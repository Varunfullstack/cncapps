<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerCallActivityMonth extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("callactivity");
        $this->addColumn("year", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("month", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("monthName", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("hours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getMonthyActivityByContract(
        $contractType = false,
        $customerID = false,
        $dateFrom,
        $dateTo
    )
    {

        $this->setMethodName('getMonthyActivityByContract');

        $statement =
            "SELECT
				YEAR(date_field),
				MONTH(date_field),
				MONTHNAME(date_field),
				SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60
	
			FROM date_xref
				LEFT JOIN callactivity ON caa_date = date_field
        JOIN problem ON pro_problemno = caa_problemno
        JOIN customer ON problem.pro_custno = customer.cus_custno
				LEFT JOIN consultant ON callactivity.caa_consno = consultant.cns_consno
				LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino
				LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno WHERE 1=1";

        if ($dateFrom) {
            $statement .=
                " AND date_field >= '" . $dateFrom . "'";
        }

        if ($dateTo) {
            $statement .=
                " AND date_field <= '" . $dateTo . "'";
        } else {
            $statement .=
                " AND date_field <= NOW()";
        }

        $statement .=
            " AND
			(";
        if ($contractType == 'Pre-Pay') {
            $statement .=
                "(contractitem.itm_itemno = " . CONFIG_DEF_PREPAY_ITEMID;        // PrePay
        } elseif ($contractType == 'T & M') {
            $statement .=
                "(pro_contract_cuino = 0";                // T & M
        } else {
            $statement .=
                "(contractitem.itm_desc LIKE '%" . $contractType . "%'";
        }

        $statement .=
            "	AND pro_custno=" . $customerID . ")
				OR
				caa_callactivityno IS NULL
			)
	
			GROUP BY YEAR(date_field), MONTH(date_field)";

        $this->setQueryString($statement);
        return (parent::getRows());
    }

    function getMonthyActivityByStaff(
        $customerID = false,
        $dateFrom,
        $dateTo
    )
    {

        $this->setMethodName('getMonthyActivityByStaff');

        $statement =
            "SELECT
				CONCAT( con_first_name, ' ', con_last_name ) AS name,
				'',
				'',
				SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours
	
			FROM callactivity 
        JOIN problem ON pro_problemno = caa_problemno
				LEFT JOIN contact ON contact.con_contno= callactivity.caa_contno
			WHERE pro_custno=" . $customerID;

        if ($dateFrom) {
            $statement .=
                " AND caa_date >= '" . $dateFrom . "'";
        }

        if ($dateTo) {
            $statement .=
                " AND caa_date <= '" . $dateTo . "'";
        } else {
            $statement .=
                " AND caa_date <= NOW()";
        }


        $statement .=
            " GROUP BY name
				ORDER BY hours DESC";

        $this->setQueryString($statement);
        return (parent::getRows());
    }

    function getMonthyActivityBySite(
        $customerID = false,
        $dateFrom,
        $dateTo
    )
    {

        $this->setMethodName('getMonthyActivityBySite');

        $statement =
            "SELECT
				CONCAT( add_town, ' ', add_postcode ),
				'',
				'',
				SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours
	
			FROM date_xref
				LEFT JOIN callactivity ON caa_date = date_field
				LEFT JOIN address ON address.add_custno= callactivity.caa_custno 
				AND address.add_siteno = callactivity.caa_siteno
			WHERE caa_custno=" . $customerID;

        if ($dateFrom) {
            $statement .=
                " AND date_field >= '" . $dateFrom . "'";
        }

        if ($dateTo) {
            $statement .=
                " AND date_field <= '" . $dateTo . "'";
        } else {
            $statement .=
                " AND date_field <= NOW()";
        }

        $statement .=
            " GROUP BY caa_siteno
			ORDER BY hours DESC";

        $this->setQueryString($statement);
        return (parent::getRows());
    }
}

?>