<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerCallActivity extends DBEntity
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
        $this->addColumn("customerID", DA_ID, DA_ALLOW_NULL, 'pro_custno');
        $this->addColumn("userID", DA_ID, DA_ALLOW_NULL, 'caa_consno');
        $this->addColumn("description", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("year", DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn("hours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getMonthyActivityByContract(
        $contract = false,
        $customerID = false,
        $userID = false,
        $fromDate,
        $toDate
    )
    {
        $this->setMethodName('getMonthyActivityByContract');
        $statement =
            "SELECT pro_custno,  cns_consno, MONTHNAME(caa_date), YEAR(caa_date), SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 " .
            " FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " INNER JOIN customer ON problem.pro_custno = customer.cus_custno" .
            " INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno" .
            " LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino" .
            " LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno";

        $statement .=
            " WHERE caa_endtime > 0";

        if ($customerID) {
            $statement .=
                " AND " . $this->getDBColumnName('customerID') . "=" . $customerID;
        }

        if ($userID) {
            $statement .=
                " AND " . $this->getDBColumnName('userID') . "=" . $userID;
        }

        if ($contract != 'All') {
            if ($contract) {
                $statement .=
                    " AND contractitem.itm_desc LIKE '%" . $contract . "%'";
            } else {
                $statement .=
                    " AND pro_contract_cuino = 0";                // T & M
            }
        }

        if ($fromDate != '') {
            $statement .=
                " AND DATE(caa_date) >= '" . $fromDate . "'";
        }

        if ($toDate != '') {
            $statement .=
                " AND DATE(caa_date) <= '" . $toDate . "'";
        }

        $statement .=
            " GROUP BY YEAR(caa_date), MONTH(caa_date)";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    function getActivityType(
        $contract = false,
        $customerID = false,
        $userID = false,
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getActivityType');
        $statement =
            "SELECT null,  null, cat_desc, null, SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours" .
            " FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " INNER JOIN customer ON problem.pro_custno = customer.cus_custno" .
            " INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno" .
            " LEFT JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
			 LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino
			LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno";

        $statement .=
            " WHERE caa_endtime > 0";

        if ($customerID) {
            $statement .=
                " AND " . $this->getDBColumnName('customerID') . "=" . $customerID;
        }

        if ($userID) {
            $statement .=
                " AND " . $this->getDBColumnName('userID') . "=" . $userID;
        }

        if ($fromDate != '') {
            $statement .=
                " AND DATE(caa_date) >= '" . $fromDate . "'";
        }

        if ($toDate != '') {
            $statement .=
                " AND DATE(caa_date) <= '" . $toDate . "'";
        }
        if ($contract != 'All') {
            if ($contract) {
                $statement .=
                    " AND contractitem.itm_desc LIKE '%" . $contract . "%'";
            } else {
                $statement .=
                    " AND pro_contract_cuino = 0";                // T & M
            }
        }
        $statement .=
            " GROUP BY cat_desc ORDER BY hours DESC";


        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    function getActivityEngineer(
        $contract = false,
        $customerID = false,
        $userID = false,
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getActivityEngineer');
        $statement =
            "SELECT pro_custno, caa_consno, cns_name, null, SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours" .
            " FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " INNER JOIN customer ON problem.pro_custno = customer.cus_custno" .
            " INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno
			 LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino
			LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno";

        $statement .=
            " WHERE caa_endtime > 0";

        if ($customerID) {
            $statement .=
                " AND " . $this->getDBColumnName('customerID') . "=" . $customerID;
        }

        if ($userID) {
            $statement .=
                " AND " . $this->getDBColumnName('userID') . "=" . $userID;
        }

        if ($fromDate != '') {
            $statement .=
                " AND DATE(caa_date) >= '" . $fromDate . "'";
        }

        if ($toDate != '') {
            $statement .=
                " AND DATE(caa_date) <= '" . $toDate . "'";
        }

        if ($contract != 'All') {
            if ($contract) {
                $statement .=
                    " AND contractitem.itm_desc LIKE '%" . $contract . "%'";
            } else {
                $statement .=
                    " AND pro_contract_cuino = 0";                // T & M
            }
        }
        $statement .=
            " GROUP BY cns_name ORDER BY HOURS DESC";


        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }


}

?>