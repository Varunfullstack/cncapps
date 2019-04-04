<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEActivityProfitReport extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("callactivity");
        $this->addColumn(
            "CustomerID",
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            "CustomerName",
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            "PPHours",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "PPCharge",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "TMHours",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "TMCharge",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SDHours",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SDCharge",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SCHours",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SCCharge",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SDPaid",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SCPaid",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "TotalHours",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SDProRata",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "SCProRata",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "CncCost",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            "Profit",
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria(
        $customerID,
        $fromDate,
        $toDate,
        $officeStartTime,
        $officeEndTime
    )
    {

        $this->setMethodName('getRowsBySearchCriteria');
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
	truncate( `PPCharge` + `TMCharge` + `SDProRata` + `SCProRata` -  (" . $dsHeader->getValue('hourlyLabourCost') . " * `TotalHours`), 2 ) AS `Profit`
from(
select
	*,
	`PPHours` + `TMHours` + `SDHours` + `SCHours` AS `TotalHours`,
	truncate( ( `SDPaid` / 365 ) * DATEDIFF('$toDate', '$fromDate') , 2 ) AS `SDProRata`,
	truncate( ( `SCPaid` / 365 ) * DATEDIFF('$toDate', '$fromDate') , 2 ) AS `SCProRata`
from(
select
	`CustomerID`,
	`CustomerName`,
	`PP OT` + `PP OT C` + `PP NT` + `PP NT C` AS `PPHours`,
	( 70 * 1.5 * `PP OT C` ) + ( 70 * `PP NT C` ) as `PPCharge`,
	`TM OT` + `TM NT` + `TM OT C` + `TM NT C` `TMHours`,
	( 70 * 1.5 * `TM OT C` ) + ( 70 * `TM NT C` ) as `TMCharge`,
	`SD OT` + `SD NT` + `SD OT C` + `SD NT C` AS `SDHours`,
	( 70 * 1.5 * `SD OT C` ) + ( 70 * `SD NT C` ) as `SDCharge`,
	`SC OT` + `SC NT` + `SC OT C` + `SC NT C`AS `SCHours`,
	( 70 * 1.5 * `SC OT C` ) + ( 70 * `SC NT C` ) as `SCCharge`,
	`SDPaid`,
	`SCPaid`
	
from (

select
	caa_custno as `CustomerID`,
	cus_name as `CustomerName`,

	/* Overtime column */
	truncate(
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
		SUM(
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
			SUM(custitem.cui_sale_price)
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
			SUM(custitem.cui_sale_price)
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

        $query .=

            "	GROUP BY
				caa_custno
			) as temp
			) as temp1 GROUP BY CustomerID
			) as temp2 ORDER BY `Profit` DESC";

        $this->setQueryString($query);

        $ret = (parent::getRows());
        return $ret;
    }

}

?>