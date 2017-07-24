<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActvityHours extends DBEntity
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
        $this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, 'cus_name');
        $this->addColumn("ppOT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("ppNT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("sdOT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("sdNT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("scOT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("scNT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("tAndMOT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("tAndMNT", DA_DECIMAL, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getMonthyActivityHoursByDateRange(
        $fromDate,
        $toDate,
        $ntStart,
        $ntEnd
    )
    {

        $this->setMethodName('getMonthyActivityByContract');
        /*
        Returns a column for normal and overtime activity hours for each contract type

        Diagram of different cases catered for in query:

        CASE	OT	|NT		|OT

        1	--------|		|
        2		|		|--------
        3	    ----|---		|
        4		|---------------|
        5		|		|
        6	--------|---------------|--------
        7		|	    ----|----

        */
        $statement =
            "
		select
			cus_name,
		
			/* Overtime column */
			truncate(
				SUM(
					# 1 & 2: starting and ending ouside NT
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						(caa_starttime < '$ntStart' AND caa_endtime < '$ntStart'
						OR 
						caa_starttime > '$ntEnd' AND caa_endtime > '$ntEnd'),
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3 & 6: up to or after NT start
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_starttime < '$ntStart' AND caa_endtime >= '$ntStart',
						TIME_TO_SEC( '$ntStart' ) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 6 & 7 starting on or before NT end
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_endtime > '$ntEnd' AND caa_starttime <= '$ntEnd',
						TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$ntEnd'),
						0
					)
				) /60 /60,
				2
			)
			AS `PP OT`,
			
			/* Pre pay Normal time column */
			truncate(
				SUM(
					# 4: activity starts and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3: starts before NT and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 7: starts inside NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 6: starts before NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%Pre-pay%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC('$ntEnd') - TIME_TO_SEC('$ntStart'),
						0
					)
		
				)  /60 /60,
				2
			)
			AS `PP NT`,
		
			/* SD Overtime column */
			truncate(
				SUM(
					# 1 & 2: starting and ending ouside NT
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk' AND
						(caa_starttime < '$ntStart' AND caa_endtime < '$ntStart'
						OR 
						caa_starttime > '$ntEnd' AND caa_endtime > '$ntEnd'),
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3 & 6: up to or after NT start
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_starttime < '$ntStart' AND caa_endtime >= '$ntStart',
						TIME_TO_SEC( '$ntStart' ) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 6 & 7 starting on or before NT end
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_endtime > '$ntEnd' AND caa_starttime <= '$ntEnd',
						TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$ntEnd'),
						0
					)
				) /60 /60,
				2
			)
			AS `SD OT`,
			
			/* Normal time column */
			truncate(
				SUM(
					# 4: activity starts and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3: starts before NT and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 7: starts inside NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 6: starts before NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%ServiceDesk%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC('$ntEnd') - TIME_TO_SEC('$ntStart'),
						0
					)
		
				)  /60 /60,
				2
			)
			AS `SD NT`,
		
			/* SC Overtime column */
			truncate(
				SUM(
					# 1 & 2: starting and ending ouside NT
					if ( 
						contractitem.itm_desc LIKE '%ServerCare' AND
						(caa_starttime < '$ntStart' AND caa_endtime < '$ntStart'
						OR 
						caa_starttime > '$ntEnd' AND caa_endtime > '$ntEnd'),
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3 & 6: up to or after NT start
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_starttime < '$ntStart' AND caa_endtime >= '$ntStart',
						TIME_TO_SEC( '$ntStart' ) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 6 & 7 starting on or before NT end
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_endtime > '$ntEnd' AND caa_starttime <= '$ntEnd',
						TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$ntEnd'),
						0
					)
				) /60 /60,
				2
			)
			AS `SC OT`,
			
			/* Normal time column */
			truncate(
				SUM(
					# 4: activity starts and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3: starts before NT and ends inside NT
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 7: starts inside NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 6: starts before NT and ends after NT
					if ( 
						contractitem.itm_desc LIKE '%ServerCare%' AND
						caa_starttime < '$ntStart' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC('$ntEnd') - TIME_TO_SEC('$ntStart'),
						0
					)
		
				)  /60 /60,
				2
			)
			AS `SC NT`,
		
			/* T&M Overtime column */
			truncate(
				SUM(
					# 1 & 2: starting and ending ouside NT
					if ( 
						pro_contract_cuino= 0 AND
						(caa_starttime < '$ntStart' AND caa_endtime < '$ntStart'
						OR 
						caa_starttime > '$ntEnd' AND caa_endtime > '$ntEnd'),
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3 & 6: up to or after NT start
					if ( 
						pro_contract_cuino= 0 AND
						caa_starttime < '$ntStart' AND caa_endtime >= '$ntStart',
						TIME_TO_SEC( '$ntStart' ) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 6 & 7 starting on or before NT end
					if ( 
						pro_contract_cuino= 0 AND
						caa_endtime > '$ntEnd' AND caa_starttime <= '$ntEnd',
						TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$ntEnd'),
						0
					)
				) /60 /60,
				2
			)
			AS `T&M OT`,
			
			/* T&M Normal time column */
			truncate(
				SUM(
					# 4: activity starts and ends inside NT
					if ( 
						pro_contract_cuino= 0 AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
						0
					)
					+
					# 3: starts before NT and ends inside NT
					if ( 
						pro_contract_cuino= 0 AND
						caa_starttime < '$ntStart' AND
						caa_endtime BETWEEN '$ntStart' AND '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 7: starts inside NT and ends after NT
					if ( 
						pro_contract_cuino= 0 AND
						caa_starttime BETWEEN '$ntStart' AND '$ntEnd' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$ntStart'),
						0
					)
					+
					# 6: starts before NT and ends after NT
					if ( 
						pro_contract_cuino= 0 AND
						caa_starttime < '$ntStart' AND
						caa_endtime > '$ntEnd'
						,
						TIME_TO_SEC('$ntEnd') - TIME_TO_SEC('$ntStart'),
						0
					)
		
				)  /60 /60,
				2
			)
			AS `T&M NT`
		
		FROM
			callactivity
      JOIN problem ON pro_problemno = caa_problemno
			LEFT JOIN customer ON callactivity.pro_custno = customer.cus_custno
			LEFT JOIN custitem AS contract ON problem.pro_contract_cuino= contract.cui_cuino
			LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno
		WHERE
			caa_date BETWEEN '$fromDate' AND 'toDate'
		GROUP BY
			caa_custno
		ORDER BY
			cus_name";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    function addParams(
        $statement,
        $contractType,
        $customerID,
        $userID,
        $fromDate,
        $toDate
    )
    {

        if ($contractType == 'T & M') {
            $statement .=
                " AND pro_contract_cuino= 0";                // T & M
        } else {

            if ($contractType != 'All') {

                if ($contractType) {
                    $statement .=
                        " AND contractitem.itm_desc LIKE '%" . $contractType . "%'";
                }

            }

        }
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

        return $statement;
    }

    function getActivityType(
        $contractType = 'All',
        $customerID = false,
        $userID = false,
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getActivityType');
        $statement =
            "SELECT null,  null, null, cat_desc, null, SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours" .
            " FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " INNER JOIN customer ON problem.pro_custno = customer.cus_custno" .
            " INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno" .
            " LEFT JOIN custitem AS contract ON problem.pro_contract_cuino= contract.cui_cuino" .
            " LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno" .
            " LEFT JOIN callacttype ON cat_callacttypeno = caa_callacttypeno";

        $statement .=
            " WHERE caa_endtime > 0";

        $statement = $this->addParams(
            $statement,
            $contractType,
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $statement .=
            " GROUP BY cat_desc ORDER BY hours DESC";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    function getActivityEngineer(
        $contractType = 'All',
        $customerID = false,
        $userID = false,
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getActivityEngineer');
        $statement =
            "SELECT pro_custno, caa_consno, null, cns_name, null, SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60 / 60 AS hours" .
            " FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " INNER JOIN customer ON problem.pro_custno = customer.cus_custno" .
            " LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino" .
            " LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno" .
            " INNER JOIN consultant ON callactivity.caa_consno = consultant.cns_consno";

        $statement .=
            " WHERE caa_endtime > 0";

        $statement = $this->addParams(
            $statement,
            $contractType,
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $statement .=
            " GROUP BY cns_name ORDER BY HOURS DESC";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }


}

?>