<?php
/**
 * Helpdesk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUHelpdeskReport extends Business
{

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function BUActivityProfitReport(&$owner)
    {
        parent::__construct($owner);
    }

    function getTotalActivityCount($today = true)
    {

        $sql =
            "
			SELECT
				count(*) AS `activityCount`
			FROM callactivity
			WHERE caa_date = curdate();
			";

        return $this->db->query($sql)->fetch_object()->activityCount;

    } // end getTotalActivities

    function getServerguardActivityCount($today = true)
    {

        $sql =
            "
			SELECT
			  count(*) AS `activityCount`
			FROM callactivity
			WHERE caa_date = curdate()
			    AND caa_serverguard = 'Y';
			";

        return $this->db->query($sql)->fetch_object()->activityCount;

    } // end getTotalActivities

    function getOutstandingActivityCounts()
    {
        $sql =
            "
			SELECT
			  hed_helpdesk_os_service_desk_count AS `helpDeskOSServiceDeskCount`,
			  hed_helpdesk_os_servercare_count AS `helpDeskOSServerCareCount`,
			  hed_helpdesk_os_prepay_count AS `helpDeskOSPrePayCount`,
			  hed_helpdesk_os_escalation_count AS `helpDeskOSEscalationCount`,
			  hed_helpdesk_os_cust_response_count AS `helpDeskOSCustResponseCount`
			FROM headert;
			";

        return $this->db->query($sql)->fetch_object();

    }

    function getHelpDeskProblems()
    {
        $sql =
            "
			SELECT
			  hed_helpdesk_problems AS `helpDeskProblems`
			FROM headert;
			";

        return $this->db->query($sql)->fetch_object()->helpDeskProblems;

    }

    /**
     * Gets all visits due from tomorrow until the next workday (not weekend or bank holiday)
     *
     * @return unknown
     */
    function getVisits()
    {
        $sql =
            "
			SELECT	
				cns_name AS `engineer`,
				cus_name AS `customer`,
				date_format( caa_date, '%e/%c/%Y' ) AS `date`,
				CASE  
					WHEN caa_starttime < '12:00' THEN 'AM' 
					ELSE 'PM' 
				END AS `timeOfDay`
			FROM
				callactivity
        JOIN problem ON pro_problemno = caa_problemno
				JOIN callacttype ON caa_callacttypeno = cat_callacttypeno
				JOIN customer ON cus_custno = pro_custno
				JOIN consultant ON cns_consno = caa_consno
			WHERE
				caa_date BETWEEN DATE_ADD( CURDATE(), INTERVAL 1 DAY ) AND 
				(
					SELECT
						date_field
					FROM
						date_xref
					WHERE
						date_field > CURDATE()
						AND
							is_bank_holiday = 'N'
						AND
							DAYOFWEEK(date_field) NOT IN(1,7)
						ORDER BY
							date_field
						
					LIMIT 0,1
				)
				AND caa_endtime = ''
				AND allowSCRFlag = 'Y';
			";


        return $this->db->query($sql);

    } // end getVisitsTomorrow


    function getStaffAvailability()
    {

        $sql =
            "
			SELECT	
				cns_name AS `engineer`,
				am,
				pm
			FROM
				staffAvailable
				JOIN consultant ON cns_consno = userID
			WHERE
				date = curdate()
				AND
					( am > 0 OR pm > 0 )
 				AND
 					cns_perms LIKE '%" . PHPLIB_PERM_SERVICE . "%'
					";

        return $this->db->query($sql);

    } // end getStaffAvailablilty


    function getTopTenCustomers($today = true)
    {

        $sql =
            "
			SELECT
				cus_name AS customer,
				count(*) AS activities,
				SUM( (TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) AS hours
			FROM
				callactivity
        JOIN problem ON pro_problemno = caa_problemno
        JOIN customer ON cus_custno = pro_custno
			WHERE";

        if ($today) {
            $sql .=
                "
					caa_date = curdate()
					";
        } else {
            $sql .=
                "
					MONTH(caa_date) = MONTH(curdate())
					AND YEAR(caa_date) = YEAR(curdate())
					";
        }

        $sql .=
            "
				group by
					Customer
				order by
					Hours desc
				limit 0,10;
				";

        return $this->db->query($sql);

    }

    function getPeriodDescription($period)
    {
        if ($period) {
            return 'Today';
        } else {
            return 'This Month';
        }
    }
}// End of class
?>