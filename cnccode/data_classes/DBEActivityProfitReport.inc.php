<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");

class DBEActivityProfitReport extends DBEntity
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
        $this->addColumn("ActivityID", DA_ID, DA_ALLOW_NULL);
        $this->addColumn("Date", DA_DATE, DA_ALLOW_NULL);
        $this->addColumn("CustomerID", DA_ID, DA_NOT_NULL);
        $this->addColumn("CustomerName", DA_STRING, DA_NOT_NULL);
        $this->addColumn("PPHours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("PPCharge", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("TMHours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("TMCharge", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SDHours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SDCharge", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SCHours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SCCharge", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SDPaid", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SCPaid", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("TotalHours", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SDProRata", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SCProRata", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("SalesProfit", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("CncCost", DA_DECIMAL, DA_ALLOW_NULL);
        $this->addColumn("ServiceProfit", DA_DECIMAL, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria(
        $customerID,
        $fromDate,
        $toDate,
        $officeStartTime,
        $officeEndTime,
        $drill = false
    )
    {

        $this->setMethodName('getRowsBySearchCriteria');

        $buHeader = new BUHeader ($this);
        $buHeader->getHeader($dsHeader);
        /*
        Returns a column for normal and overtime activity hours for each contract type

        Diagram of different cases catered cncp1for in query:

        CASE	OT	|NT		|OT

        1	--------|		|
        2		|		|--------
        3	    ----|---		|
        4		|---------------|
        5		|		|
        6	--------|---------------|--------
        7		|	    ----|----

        */

        /* use a derived table to do calculations on result */
        $query =
            "select
	*,
	truncate( " . $dsHeader->getValue('hourlyLabourCost') . " * `TotalHours`, 2 ) AS `CnCCost`,
	truncate( `PPCharge` + `TMCharge` + `SDProRata` + `SCProRata` -  (" . $dsHeader->getValue('hourlyLabourCost') . " * `TotalHours`), 2 )  AS `ServiceProfit`
from(
select
	*,
	`PPHours` + `TMHours` + `SDHours` + `SCHours` AS `TotalHours`,
	truncate( ( `SDPaid` / 365 ) * DATEDIFF('$toDate', '$fromDate') , 2 ) AS `SDProRata`,
	truncate( ( `SCPaid` / 365 ) * DATEDIFF('$toDate', '$fromDate') , 2 ) AS `SCProRata`,
	(SELECT SUM( 
		( invline.inl_qty * invline.inl_unit_price )
		- ( invline.inl_qty * invline.inl_cost_price ) )
		FROM invhead JOIN invline ON inl_invno = inh_invno
		JOIN item ON itm_itemno = inl_itemno
		JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
		WHERE inh_custno = CustomerID
	  AND inh_date_printed BETWEEN '$fromDate' AND '$toDate'
		AND inl_line_type = 'I' 

	) AS `SalesProfit`
from(
select
	`ActivityID`,
	`Date`,
	`CustomerID`,
	`CustomerName`,
	`PP OT` + `PP NT` AS `PPHours`,
	( 70 * 1.5 * `PP OT C` ) + ( 70 * `PP NT C` ) as `PPCharge`,
	`TM OT` + `TM NT` AS `TMHours`,
	( 70 * 1.5 * `TM OT C` ) + ( 70 * `TM NT C` ) as `TMCharge`,
	`SD OT` + `SD NT` AS `SDHours`,
	( 70 * 1.5 * `SD OT C` ) + ( 70 * `SD NT C` ) as `SDCharge`,
	`SC OT` + `SC NT` AS `SCHours`,
	( 70 * 1.5 * `SC OT C` ) + ( 70 * `SC NT C` ) as `SCCharge`,
	`SDPaid`,
	`SCPaid`
	
from (

select
	caa_callactivityno as `ActivityID`,
	caa_date as `Date`,
	caa_custno as `CustomerID`,
	cus_name as `CustomerName`,

	/* Overtime column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime'),
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime',
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime',
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `PP OT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime')
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `PP OT C`,
	
	/* Pre pay Normal time column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `PP NT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%Pre-pay%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `PP NT C`,

	/* SD Overtime column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime'),
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime',
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime',
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `SD OT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime')
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `SD OT C`,
	
	/* Normal time column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `SD NT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServiceDesk%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `SD NT C`,

	/* SC Overtime column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime'),
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime',
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime',
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `SC OT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare' AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime')
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `SC OT C`,
	
	/* Normal time column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `SC NT`,
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				contractitem.itm_desc LIKE '%ServerCare%' AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `SC NT C`,

	/* T&M Overtime column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				pro_contract_cuino = 0 AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime'),
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime',
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				pro_contract_cuino = 0 AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime',
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `TM OT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 1 & 2: starting and ending ouside NT
			if ( 
				pro_contract_cuino = 0 AND
				(caa_starttime < '$officeStartTime' AND caa_endtime < '$officeStartTime'
				OR 
				caa_starttime > '$officeEndTime' AND caa_endtime > '$officeEndTime')
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3 & 6: up to or after NT start
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND caa_endtime >= '$officeStartTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( '$officeStartTime' ) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6 & 7 starting on or before NT end
			if ( 
				pro_contract_cuino = 0 AND
				caa_endtime > '$officeEndTime' AND caa_starttime <= '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC('$officeEndTime'),
				0
			)
		) /60 /60,
		2
	)
	AS `TM OT C`,
	
	/* T&M Normal time column */
	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `TM NT`,

	truncate(
		";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "
			# 4: activity starts and ends inside NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 3: starts before NT and ends inside NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime BETWEEN '$officeStartTime' AND '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC(caa_endtime) - TIME_TO_SEC('$officeStartTime'),
				0
			)
			+
			# 7: starts inside NT and ends after NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime BETWEEN '$officeStartTime' AND '$officeEndTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC(caa_starttime),
				0
			)
			+
			# 6: starts before NT and ends after NT
			if ( 
				pro_contract_cuino = 0 AND
				caa_starttime < '$officeStartTime' AND
				caa_endtime > '$officeEndTime'
				AND
				activityitem.itm_sstk_price > 0,
				TIME_TO_SEC('$officeEndTime') - TIME_TO_SEC('$officeStartTime'),
				0
			)

		)  /60 /60,
		2
	)
	AS `TM NT C`,

	IFNULL(
		(SELECT
			";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "custitem.cui_sale_price)
		FROM
			custitem
			JOIN item ON itm_itemno = cui_itemno
		WHERE
			cui_custno = cus_custno
			AND renewalStatus = 'R'
			AND (
				itm_desc LIKE '%ServerCare%'
			)
		)
	,0 )
	AS `SCPaid`,
	
	IFNULL(
		(SELECT
			";
        if ($drill) {
            $query .= "(";
        } else {
            $query .= "SUM(";
        }
        $query .= "custitem.cui_sale_price)
		FROM
			custitem
			JOIN item ON itm_itemno = cui_itemno
		WHERE
			cui_custno = cus_custno
			AND renewalStatus = 'R'
			AND (
				itm_desc LIKE '%ServiceDesk%'
			)
		)
	,0 )
	AS `SDPaid`
	
	FROM
	callactivity
  JOIN problem ON pro_problemno = caa_problemno
	LEFT JOIN customer ON callactivity.caa_custno = customer.cus_custno
	LEFT JOIN custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino
	LEFT JOIN item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno
	JOIN callacttype ON callactivity.caa_callacttypeno = callacttype.cat_callacttypeno
	JOIN item AS activityitem ON callacttype.cat_itemno = activityitem.itm_itemno
WHERE
	caa_date BETWEEN '$fromDate' AND '$toDate'";

        if ($customerID) {

            $query .= " AND caa_custno = $customerID";

        }
        if (!$drill) {

            $query .=

                "	GROUP BY
					caa_custno";
        }

        $query .=

            ") as temp

			) as temp1";

        if (!$drill) {
            $query .=
                " GROUP BY CustomerID";
        }

        $query .=
            ") as temp2 ORDER BY `SalesProfit` DESC";

        $this->setQueryString($query);

        $ret = (parent::getRows());
        return $ret;
    }

}

?>