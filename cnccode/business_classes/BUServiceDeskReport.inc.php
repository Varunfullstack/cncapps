<?php
/**
 * Service desk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

class BUServiceDeskReport extends Business
{
    public $customerID = false;

    public $startDate;

    public $endDate;

    public $period;

    private $startDateOneYearAgo;

    private $buActivity;

    private $year;
    private $month;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->buActivity = new BUActivity($this);
    }

    function setPeriod($period)
    {
        $this->year                = substr($period, 0, 4);
        $this->month               = substr($period, 5, 2);
        $this->period              = $period;
        $endDateUnix               = strtotime($period . 'last day next month');
        $startDateUnix             = strtotime($period);
        $this->startDate           = date('Y-m-d', $startDateUnix);
        $this->endDate             = date('Y-m-d', $endDateUnix);
        $this->startDateOneYearAgo = date('Y-m-d', strtotime('-1 year', $startDateUnix));
    }

    function setStartPeriod(DateTimeInterface $startDate)
    {
        $this->year      = $startDate->format('Y');
        $this->month     = $startDate->format('m');
        $this->startDate = $startDate->format('Y-m-d');
    }

    function setEndPeriod(DateTimeInterface $endDate)
    {
        $this->year    = $endDate->format('Y');
        $this->month   = $endDate->format('m');
        $this->endDate = $endDate->format('Y-m-d');
    }

    function getMonthName()
    {
        return date('F', strtotime($this->period));
    }

    function getYear()
    {
        return $this->year;
    }

    function getCountIncidents($parameters)
    {
        $sql = "SELECT
          COUNT(*) AS count
        FROM
          problem";
        $sql .= $this->buildWhereClause($parameters);
        return $this->db->query($sql)->fetch_object()->count;
    }

    function buildWhereClause($parameters)
    {
        $whereString = null;
        if (isset($parameters['contractKey']) && $parameters['contractKey']) {
            if ($parameters['contractKey'] != 'TM') {
                $whereString = " LEFT JOIN custitem ON cui_cuino = pro_contract_cuino
              LEFT JOIN item ON itm_itemno = cui_itemno";
            }
        }
        if (isset($parameters['ytd']) && $parameters['ytd']) {
            $whereString .= " WHERE
              DATE(pro_date_raised) BETWEEN '" . $this->startDateOneYearAgo . "' AND '" . $this->endDate . "'";
        } else {
            $whereString .= " WHERE
              DATE(pro_date_raised) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "'";
        }
        if (isset($parameters['notFixed']) && $parameters['notFixed'] == 'Y') {
            $whereString .= " AND pro_status IN  ('I', 'P')";    // Not fixed
        } else {
            $whereString .= " AND pro_status =  'C'";            // completed
        }
        if ($this->customerID) {
            $whereString .= " AND pro_custno = " . $this->customerID;
        }
        if (isset($parameters['sla']) && $parameters['sla']) {

            if ($parameters['sla'] == 'Y') {
                $whereString .= " AND pro_sla_response_hours > 0";
            }
            if ($parameters['sla'] == 'N') {
                $whereString .= " AND pro_sla_response_hours = 0";
            }

        }
        if (isset($parameters['withinSla']) && $parameters['withinSla']) {

            if ($parameters['withinSla'] == 'Y') {

                $whereString .= " AND pro_responded_hours <= pro_sla_response_hours";

            }
            if ($parameters['withinSla'] == 'N') {

                $whereString .= " AND pro_responded_hours > pro_sla_response_hours";

            }
        }
        if (isset($parameters['priority']) && $parameters['priority']) {

            $whereString .= " AND pro_priority = " . $parameters['priority'];

        }
        if (isset($parameters['contractKey'])) {

            switch ($parameters['contractKey']) {

                case 'TM':
                    $whereString .= " AND pro_contract_cuino = 0";
                    break;
                case 'PP':
                    $whereString .= " AND itm_itemno = " . CONFIG_DEF_PREPAY_ITEMID;
                    break;
                case 'SC':
                    $whereString .= " AND itm_desc LIKE '%ServerCare%'";
                    break;
                case 'SD':
                    $whereString .= " AND itm_desc LIKE '%ServiceDesk%'";
                    break;

            } // end switch
        } // end if contractKey
        $whereString .= " AND pro_priority < 5"; // exclude all project work
        return $whereString;

    }

    function getCountFirstTimeFix($parameters)
    {

        $sql = "SELECT
          COUNT(*) AS count
        FROM
          problem";
        $sql .= $this->buildWhereClause($parameters);
        $sql .= " AND (SELECT COUNT(*) FROM callactivity WHERE caa_problemno = pro_problemno) <= 3";
        return $this->db->query($sql)->fetch_object()->count;

    }

    function getCountEscalations($parameters)
    {

        $sql = "SELECT
          COUNT(*) AS count
        FROM
          problem";
        $sql .= $this->buildWhereClause($parameters);
        $sql .= " AND pro_escalated_flag = 'Y'";
        return $this->db->query($sql)->fetch_object()->count;

    }

    function getCountReopened($contractKey = false)
    {

        $sql = "SELECT
          COUNT(*) AS count
        FROM
          problem";
        $sql .= $this->buildWhereClause($contractKey, false, true);
        $sql .= " AND pro_reopened_flag = 'Y'";
        return $this->db->query($sql)->fetch_object()->count;

    }

    function getResponseHours($parameters)
    {
        $sql = "SELECT SUM(pro_responded_hours) AS hours
        FROM
          problem";
        $sql .= $this->buildWhereClause($parameters);
        return $this->db->query($sql)->fetch_object()->hours;
    }

    function getFixHours($parameters)
    {
        $sql = "SELECT SUM(pro_working_hours) AS hours
        FROM
          problem";
        $sql .= $this->buildWhereClause($parameters);
        return $this->db->query($sql)->fetch_object()->hours;

    }

    function getIncidentsGroupedByUser()
    {

        $sql = "SELECT
          CONCAT(con_first_name, ' ' , con_last_name) AS name,
           contact.active,
             SUM(
    problem.pro_hide_from_customer_flag <> 'Y'
  ) AS count,
  SUM(
    problem.pro_hide_from_customer_flag = 'Y'
  ) AS hiddenCount
        FROM
          problem
          JOIN contact ON con_contno = pro_contno";
        $sql .= " WHERE
          DATE(pro_date_raised) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "' and con_contno <> 0";
        $sql .= " AND pro_status =  'C'";
        if ($this->customerID) {
            $sql .= " AND pro_custno = " . $this->customerID;
        }
        $sql .= " GROUP BY
          pro_contno
        ORDER BY
          count DESC";
        return $this->db->query($sql);

    }

    function getIncidentsGroupedByRootCause()
    {

        $sql = "SELECT
          rtc_desc AS rootCauseDescription,
          COUNT(*) AS count
        FROM
          problem
          JOIN rootcause ON rootcause.rtc_rootcauseno = problem.pro_rootcauseno ";
        $sql .= " WHERE pro_hide_from_customer_flag <> 'Y' and
          DATE(pro_date_raised) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "'";
        if ($this->customerID) {
            $sql .= " AND pro_custno = " . $this->customerID;
        }
        $sql .= " AND pro_status =  'C'";
        $sql .= " GROUP BY
          problem.pro_rootcauseno
        ORDER BY
          count DESC";
        return $this->db->query($sql);

    }

    function getResolvedIncidentsGroupedByTeamMember()
    {

        $sql = "SELECT
          cns_name AS name,
          COUNT(*) AS count
        FROM
          problem
          JOIN consultant ON cns_consno = pro_fixed_consno";
        $sql .= " WHERE
          DATE(pro_date_raised) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "'";
        $sql .= " AND pro_status =  'C'";
        $sql .= " GROUP BY
          pro_fixed_consno
        ORDER BY
          count DESC";
        return $this->db->query($sql);

    }

    function getRejectedIncidentsGroupedByTeamMember()
    {

        $sql = "SELECT
          cns_name AS name,
          COUNT(*) AS count
        FROM
          problem
          JOIN consultant ON cns_consno = pro_rejected_consno";
        $sql .= " WHERE
          DATE(pro_date_raised) BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "'
          AND pro_rejected_consno IS NOT NULL";
        $sql .= " AND pro_status =  'C'";
        $sql .= " GROUP BY
          pro_rejected_consno
        ORDER BY
          count DESC";
        return $this->db->query($sql);

    }

    function getHoursLoggedGroupedByTeamMember()
    {

        $sql = "SELECT
          cns_name AS name,
          SUM(( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600) AS hours
          FROM callactivity JOIN consultant ON cns_consno = caa_consno
            JOIN problem ON pro_problemno = caa_problemno";
        $sql .= " WHERE
          caa_date BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "'";
        $sql .= " AND pro_status =  'C'";
        $sql .= " GROUP BY
          caa_consno
        ORDER BY
          hours DESC";
        return $this->db->query($sql);

    }

    function getCustomerName()
    {
        if ($this->customerID) {
            $sql          = "SELECT cus_name AS customerName
          FROM customer
          WHERE cus_custno = " . $this->customerID;
            $customerName = $this->db->query($sql)->fetch_object()->customerName;
        } else {
            $customerName = 'All Customers';
        }
        return $customerName;
    }

    function getPeriod()
    {
        return date('d/m/Y', strtotime($this->startDate)) . ' to ' . date('d/m/Y', strtotime($this->endDate));
    }


    function getMonthlyReport()
    {
        global $cfg;
        $template = new Template ($cfg["path_templates"], "remove");
        $template->set_file('page', 'ServiceDeskReportMonthly.inc.html');
        $fields['totalIncidents']               = $this->getCountIncidents(array());
        $fields['totalIncidentsYtd']            = $this->getCountIncidents(array('ytd' => true));
        $fields['totalIncidentsSla']            = $this->getCountIncidents(array('sla' => 'Y'));
        $fields['totalIncidentsSlaYtd']         = $this->getCountIncidents(array('sla' => 'Y', 'ytd' => true));
        $fields['totalIncidentsNonSla']         = $this->getCountIncidents(array('sla' => 'N'));
        $fields['totalIncidentsNonSlaYtd']      = $this->getCountIncidents(array('sla' => 'N', 'ytd' => true));
        $fields['totalIncidentsResolved']       = $this->getCountIncidents(array());
        $fields['totalIncidentsResolvedYtd']    = $this->getCountIncidents(array('ytd' => true));
        $fields['totalIncidentsWithinSla']      = $this->getCountIncidents(array('sla' => 'Y', 'withinSla' => 'Y'));
        $fields['totalIncidentsWithinSlaYtd']   = $this->getCountIncidents(
            array('sla' => 'Y', 'withinSla' => 'Y', 'ytd' => true)
        );
        $fields['totalIncidentsMissedSla']      = $this->getCountIncidents(array('sla' => 'Y', 'withinSla' => 'N'));
        $fields['totalIncidentsMissedSlaYtd']   = $this->getCountIncidents(
            array('sla' => 'Y', 'withinSla' => 'N', 'ytd' => true)
        );
        $fields['percentIncidentsMissedSla']    = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsMissedSla'],
            $fields['totalIncidentsSla']
        );
        $fields['percentIncidentsMissedSlaYtd'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsMissedSla'],
            $fields['totalIncidentsSla']
        );
        $fields['percentIncidentsWithinSla']    = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsWithinSla'],
            $fields['totalIncidentsSla']
        );
        $fields['percentIncidentsWithinSlaYtd'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsWithinSlaYtd'],
            $fields['totalIncidentsSlaYtd']
        );
        $fields['aveResponseHours']             = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('sla' => 'Y')),
            $fields['totalIncidentsSla']
        );
        $fields['aveResponseHoursYtd']          = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('sla' => 'Y', 'ytd' => true)),
            $fields['totalIncidentsSlaYtd']
        );
        $fields['aveFixHours']                  = BUServiceDeskReport::getAve(
            $this->getFixHours(array('sla' => 'Y')),
            $fields['totalIncidentsSla']
        );
        $fields['aveFixHoursYtd']               = BUServiceDeskReport::getAve(
            $this->getFixHours(array('sla' => 'Y', 'ytd' => true)),
            $fields['totalIncidentsSlaYtd']
        );
        $fields['firstTimeFix']                 = $this->getCountFirstTimeFix(array());
        $fields['firstTimeFixYtd']              = $this->getCountFirstTimeFix(array('ytd' => true));
        $fields['percentFirstTimeFix']          = BUServiceDeskReport::getPercent(
            $fields['firstTimeFix'],
            $fields['totalIncidents']
        );
        $fields['percentFirstTimeFixYtd']       = BUServiceDeskReport::getPercent(
            $fields['firstTimeFixYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['escalations']                  = $this->getCountEscalations(array());
        $fields['escalationsYtd']               = $this->getCountEscalations(array('ytd' => true));
        $fields['percentEscalations']           = BUServiceDeskReport::getPercent(
            $fields['escalations'],
            $fields['totalIncidents']
        );
        $fields['percentEscalationsYtd']        = BUServiceDeskReport::getPercent(
            $fields['escalationsYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['reopened']                     = $this->getCountReopened(array());
        $fields['reopenedYtd']                  = $this->getCountReopened(array('ytd' => true));
        $fields['percentReopened']              = BUServiceDeskReport::getPercent(
            $fields['reopened'],
            $fields['totalIncidents']
        );
        $fields['percentReopenedYtd']           = BUServiceDeskReport::getPercent(
            $fields['reopenedYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['severityOne']                  = $this->getCountIncidents(array('priority' => 1));
        $fields['severityOneYtd']               = $this->getCountIncidents(array('priority' => 1, 'ytd' => true));
        $fields['percentSeverityOne']           = BUServiceDeskReport::getPercent(
            $fields['severityOne'],
            $fields['totalIncidents']
        );
        $fields['percentSeverityOneYtd']        = BUServiceDeskReport::getPercent(
            $fields['severityOneYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['severityTwo']                  = $this->getCountIncidents(array('priority' => 2));
        $fields['severityTwoYtd']               = $this->getCountIncidents(array('priority' => 2, 'ytd' => true));
        $fields['percentSeverityTwo']           = BUServiceDeskReport::getPercent(
            $fields['severityTwo'],
            $fields['totalIncidents']
        );
        $fields['percentSeverityTwoYtd']        = BUServiceDeskReport::getPercent(
            $fields['severityTwoYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['severityThree']                = $this->getCountIncidents(array('priority' => 3));
        $fields['severityThreeYtd']             = $this->getCountIncidents(array('priority' => 3, 'ytd' => true));
        $fields['percentSeverityThree']         = BUServiceDeskReport::getPercent(
            $fields['severityThree'],
            $fields['totalIncidents']
        );
        $fields['percentSeverityThreeYtd']      = BUServiceDeskReport::getPercent(
            $fields['severityThreeYtd'],
            $fields['totalIncidentsYtd']
        );
        $fields['severityFour']                 = $this->getCountIncidents(array('priority' => 4));
        $fields['severityFourYtd']              = $this->getCountIncidents(array('priority' => 4, 'ytd' => true));
        $fields['percentSeverityFour']          = BUServiceDeskReport::getPercent(
            $fields['severityFour'],
            $fields['totalIncidents']
        );
        $fields['percentSeverityFourYtd']       = BUServiceDeskReport::getPercent(
            $fields['severityFourYtd'],
            $fields['totalIncidentsYtd']
        );
        foreach ($fields as $key => $value) {

            $template->setVar($key, $value);

        }
        $resolvedCountByTeamMember = $this->getResolvedIncidentsGroupedByTeamMember();
        while ($row = $resolvedCountByTeamMember->fetch_object()) {

            $team[$row->name]['resolved'] = $row->count;

        }
        $rejectedCountByTeamMember = $this->getRejectedIncidentsGroupedByTeamMember();
        while ($row = $rejectedCountByTeamMember->fetch_object()) {

            $team[$row->name]['rejected'] = $row->count;

        }
        $hoursLoggedByTeamMember = $this->getHoursLoggedGroupedByTeamMember();
        while ($row = $hoursLoggedByTeamMember->fetch_object()) {

            $team[$row->name]['hours'] = number_format($row->hours, 2);

        }
        $template->set_block('page', 'teamMemberBlock', 'teamMembers');
        reset($team);
        foreach ($team as $key => $value) {

            $template->set_var(
                array(
                    'teamMemberName'     => $key,
                    'teamMemberResolved' => isset($value['resolved']) ? $value['resolved'] : null,
                    'teamMemberRejected' => isset($value['rejected']) ? $value['rejected'] : null,
                    'teamMemberHours'    => isset($value['hours']) ? $value['hours'] : null
                )
            );
            $template->parse('teamMembers', 'teamMemberBlock', true);

        }
        $template->set_var(
            array(
                'month' => $this->getMonthName(),
                'year'  => $this->getYear()
            )
        );
        $template->parse('output', 'page', true);
        return $template->get_var('output');

    }

    function getCustomerReport()
    {
        global $cfg;
        $template = new Template ($cfg["path_templates"], "remove");
        $template->set_file('page', 'ServiceDeskReportCustomer.inc.html');
        $fields = $this->getCustomerReportFields();
        foreach ($fields as $key => $value) {

            $template->setVar($key, $value);

        }
        if ($this->customerID) {
            $template->set_block('page', 'userBlock', 'users');
            $incidentCountByUser = $this->getIncidentsGroupedByUser();
            while ($row = $incidentCountByUser->fetch_object()) {


                $template->set_var(
                    array(
                        'incidentUserName'   => $row->name,
                        'incidentUserLogged' => $row->count
                    )
                );
                $template->parse('users', 'userBlock', true);

            }
            $template->set_var(
                array(
                    'txtIncidentStatsByUser' => 'Incident Stats By User',
                    'txtLogged'              => 'Logged'
                )
            );
        } else {
            $template->set_var(
                array(
                    'userBlock'              => '',
                    'txtLogged'              => '',
                    'txtIncidentStatsByUser' => '',
                    'incidentUserName'       => '',
                    'incidentUserLogged'     => ''
                )
            );
        }
        $template->set_var(
            array(
                'priority1Desc' => $this->buActivity->priorityArray[1],
                'priority2Desc' => $this->buActivity->priorityArray[2],
                'priority3Desc' => $this->buActivity->priorityArray[3],
                'priority4Desc' => $this->buActivity->priorityArray[4]
            )
        );
        /*
        Root causes
        */
        $template->set_block('page', 'rootCauseBlock', 'rootcauses');
        $incidentCountByRootCause = $this->getIncidentsGroupedByRootCause();
        while ($row = $incidentCountByRootCause->fetch_object()) {


            $template->set_var(
                array(
                    'rootCauseDescription' => $row->rootCauseDescription,
                    'rootCauseLogged'      => $row->count
                )
            );
            $template->parse('rootcauses', 'rootCauseBlock', true);

        }
// end root causes
        $template->set_var(
            array(
                'customerName' => $this->getCustomerName(),
                'period'       => $this->getPeriod()
            )
        );
        $template->parse('output', 'page', true);
        return $template->get_var('output');

    }

    function getCustomerReportFields()
    {
        $fields['totalIncidentsSd']            = $this->getCountIncidents(array('contractKey' => 'SD'));
        $fields['totalIncidentsSc']            = $this->getCountIncidents(array('contractKey' => 'SC'));
        $fields['totalIncidentsPp']            = $this->getCountIncidents(array('contractKey' => 'PP'));
        $fields['totalIncidentsTm']            = $this->getCountIncidents(array('contractKey' => 'TM'));
        $fields['totalIncidentsSdSla']         = $this->getCountIncidents(array('contractKey' => 'SD', 'sla' => 'Y'));
        $fields['totalIncidentsScSla']         = $this->getCountIncidents(array('contractKey' => 'SC', 'sla' => 'Y'));
        $fields['totalIncidentsPpSla']         = $this->getCountIncidents(array('contractKey' => 'PP', 'sla' => 'Y'));
        $fields['totalIncidentsTmSla']         = $this->getCountIncidents(array('contractKey' => 'TM', 'sla' => 'Y'));
        $fields['totalIncidentsSdNonSla']      = $this->getCountIncidents(array('contractKey' => 'SD', 'sla' => 'N'));
        $fields['totalIncidentsScNonSla']      = $this->getCountIncidents(array('contractKey' => 'SC', 'sla' => 'N'));
        $fields['totalIncidentsPpNonSla']      = $this->getCountIncidents(array('contractKey' => 'PP', 'sla' => 'N'));
        $fields['totalIncidentsTmNonSla']      = $this->getCountIncidents(array('contractKey' => 'TM', 'sla' => 'N'));
        $fields['totalIncidentsSdWithinSla']   = $this->getCountIncidents(
            array('contractKey' => 'SD', 'sla' => 'Y', 'withinSla' => 'Y')
        );
        $fields['totalIncidentsScWithinSla']   = $this->getCountIncidents(
            array('contractKey' => 'SC', 'sla' => 'Y', 'withinSla' => 'Y')
        );
        $fields['totalIncidentsPpWithinSla']   = $this->getCountIncidents(
            array('contractKey' => 'PP', 'sla' => 'Y', 'withinSla' => 'Y')
        );
        $fields['totalIncidentsTmWithinSla']   = $this->getCountIncidents(
            array('contractKey' => 'TM', 'sla' => 'Y', 'withinSla' => 'Y')
        );
        $fields['totalIncidentsSdMissedSla']   = $this->getCountIncidents(
            array('contractKey' => 'SD', 'sla' => 'Y', 'withinSla' => 'N')
        );
        $fields['totalIncidentsScMissedSla']   = $this->getCountIncidents(
            array('contractKey' => 'SC', 'sla' => 'Y', 'withinSla' => 'N')
        );
        $fields['totalIncidentsPpMissedSla']   = $this->getCountIncidents(
            array('contractKey' => 'PP', 'sla' => 'Y', 'withinSla' => 'N')
        );
        $fields['totalIncidentsTmMissedSla']   = $this->getCountIncidents(
            array('contractKey' => 'TM', 'sla' => 'Y', 'withinSla' => 'N')
        );
        $fields['percentIncidentsSdMissedSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsSdMissedSla'],
            $fields['totalIncidentsSdSla']
        );
        $fields['percentIncidentsScMissedSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsScMissedSla'],
            $fields['totalIncidentsScSla']
        );
        $fields['percentIncidentsPpMissedSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsPpMissedSla'],
            $fields['totalIncidentsPpSla']
        );
        $fields['percentIncidentsTmMissedSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsTmMissedSla'],
            $fields['totalIncidentsTmSla']
        );
        $fields['percentIncidentsSdWithinSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsSdWithinSla'],
            $fields['totalIncidentsSdSla']
        );
        $fields['percentIncidentsScWithinSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsScWithinSla'],
            $fields['totalIncidentsScSla']
        );
        $fields['percentIncidentsPpWithinSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsPpWithinSla'],
            $fields['totalIncidentsPpSla']
        );
        $fields['percentIncidentsTmWithinSla'] = BUServiceDeskReport::getPercent(
            $fields['totalIncidentsTmWithinSla'],
            $fields['totalIncidentsTmSla']
        );
        $fields['aveResponseHoursSdSla']       = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'SD', 'sla' => 'Y')),
            $fields['totalIncidentsSdSla']
        );
        $fields['aveResponseHoursScSla']       = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'SC', 'sla' => 'Y')),
            $fields['totalIncidentsScSla']
        );
        $fields['aveResponseHoursPpSla']       = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'PP', 'sla' => 'Y')),
            $fields['totalIncidentsPpSla']
        );
        $fields['aveResponseHoursTmSla']       = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'TM', 'sla' => 'Y')),
            $fields['totalIncidentsTmSla']
        );
        $fields['aveResponseHoursSdNonSla']    = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'SD', 'sla' => 'N')),
            $fields['totalIncidentsSdNonSla']
        );
        $fields['aveResponseHoursScNonSla']    = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'SC', 'sla' => 'N')),
            $fields['totalIncidentsScNonSla']
        );
        $fields['aveResponseHoursPpNonSla']    = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'PP', 'sla' => 'N')),
            $fields['totalIncidentsPpNonSla']
        );
        $fields['aveResponseHoursTmNonSla']    = BUServiceDeskReport::getAve(
            $this->getResponseHours(array('contractKey' => 'TM', 'sla' => 'N')),
            $fields['totalIncidentsTmNonSla']
        );
        $fields['aveFixHoursSdSla']            = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'SD', 'sla' => 'Y')),
            $fields['totalIncidentsSdSla']
        );
        $fields['aveFixHoursScSla']            = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'SC', 'sla' => 'Y')),
            $fields['totalIncidentsScSla']
        );
        $fields['aveFixHoursPpSla']            = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'PP', 'sla' => 'Y')),
            $fields['totalIncidentsPpSla']
        );
        $fields['aveFixHoursTmSla']            = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'TM', 'sla' => 'Y')),
            $fields['totalIncidentsTmSla']
        );
        $fields['aveFixHoursSdNonSla']         = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'SD', 'sla' => 'N')),
            $fields['totalIncidentsSdNonSla']
        );
        $fields['aveFixHoursScNonSla']         = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'SC', 'sla' => 'N')),
            $fields['totalIncidentsScNonSla']
        );
        $fields['aveFixHoursPpNonSla']         = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'PP', 'sla' => 'N')),
            $fields['totalIncidentsPpNonSla']
        );
        $fields['aveFixHoursTmNonSla']         = BUServiceDeskReport::getAve(
            $this->getFixHours(array('contractKey' => 'TM', 'sla' => 'N')),
            $fields['totalIncidentsTmNonSla']
        );
        $fields['firstTimeFixSd']              = $this->getCountFirstTimeFix(array('contractKey' => 'SD'));
        $fields['firstTimeFixSc']              = $this->getCountFirstTimeFix(array('contractKey' => 'SC'));
        $fields['firstTimeFixPp']              = $this->getCountFirstTimeFix(array('contractKey' => 'PP'));
        $fields['firstTimeFixTm']              = $this->getCountFirstTimeFix(array('contractKey' => 'TM'));
        $fields['percentFirstTimeFixSd']       = BUServiceDeskReport::getPercent(
            $fields['firstTimeFixSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentFirstTimeFixSc']       = BUServiceDeskReport::getPercent(
            $fields['firstTimeFixSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentFirstTimeFixPp']       = BUServiceDeskReport::getPercent(
            $fields['firstTimeFixPp'],
            $fields['totalIncidentsPp']
        );
        $fields['percentFirstTimeFixTm']       = BUServiceDeskReport::getPercent(
            $fields['firstTimeFixTm'],
            $fields['totalIncidentsTm']
        );
        $fields['escalationsSd']               = $this->getCountEscalations(array('contractKey' => 'SD'));
        $fields['escalationsSc']               = $this->getCountEscalations(array('contractKey' => 'SC'));
        $fields['escalationsPp']               = $this->getCountEscalations(array('contractKey' => 'PP'));
        $fields['escalationsTm']               = $this->getCountEscalations(array('contractKey' => 'TM'));
        $fields['percentEscalationsSd']        = BUServiceDeskReport::getPercent(
            $fields['escalationsSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentEscalationsSc']        = BUServiceDeskReport::getPercent(
            $fields['escalationsSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentEscalationsPp']        = BUServiceDeskReport::getPercent(
            $fields['escalationsPp'],
            $fields['totalIncidentsPp']
        );
        $fields['percentEscalationsTm']        = BUServiceDeskReport::getPercent(
            $fields['escalationsTm'],
            $fields['totalIncidentsTm']
        );
        $fields['reopenedSd']                  = $this->getCountReopened(array('contractKey' => 'SD'));
        $fields['reopenedSc']                  = $this->getCountReopened(array('contractKey' => 'SC'));
        $fields['reopenedPp']                  = $this->getCountReopened(array('contractKey' => 'PP'));
        $fields['reopenedTm']                  = $this->getCountReopened(array('contractKey' => 'TM'));
        $fields['percentReopenedSd']           = BUServiceDeskReport::getPercent(
            $fields['reopenedSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentReopenedSc']           = BUServiceDeskReport::getPercent(
            $fields['reopenedSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentReopenedPp']           = BUServiceDeskReport::getPercent(
            $fields['reopenedPp'],
            $fields['totalIncidentsPp']
        );
        $fields['percentReopenedTm']           = BUServiceDeskReport::getPercent(
            $fields['reopenedTm'],
            $fields['totalIncidentsTm']
        );
        $fields['severityOneSd']               = $this->getCountIncidents(
            array('contractKey' => 'SD', 'priority' => 1)
        );
        $fields['severityOneSc']               = $this->getCountIncidents(
            array('contractKey' => 'SC', 'priority' => 1)
        );
        $fields['severityOnePp']               = $this->getCountIncidents(
            array('contractKey' => 'PP', 'priority' => 1)
        );
        $fields['severityOneTm']               = $this->getCountIncidents(
            array('contractKey' => 'TM', 'priority' => 1)
        );
        $fields['percentSeverityOneSd']        = BUServiceDeskReport::getPercent(
            $fields['severityOneSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentSeverityOneSc']        = BUServiceDeskReport::getPercent(
            $fields['severityOneSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentSeverityOneTm']        = BUServiceDeskReport::getPercent(
            $fields['severityOneTm'],
            $fields['totalIncidentsTm']
        );
        $fields['percentSeverityOnePp']        = BUServiceDeskReport::getPercent(
            $fields['severityOnePp'],
            $fields['totalIncidentsPp']
        );
        $fields['severityTwoSd']               = $this->getCountIncidents(
            array('contractKey' => 'SD', 'priority' => 2)
        );
        $fields['severityTwoSc']               = $this->getCountIncidents(
            array('contractKey' => 'SC', 'priority' => 2)
        );
        $fields['severityTwoPp']               = $this->getCountIncidents(
            array('contractKey' => 'PP', 'priority' => 2)
        );
        $fields['severityTwoTm']               = $this->getCountIncidents(
            array('contractKey' => 'TM', 'priority' => 2)
        );
        $fields['percentSeverityTwoSd']        = BUServiceDeskReport::getPercent(
            $fields['severityTwoSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentSeverityTwoSc']        = BUServiceDeskReport::getPercent(
            $fields['severityTwoSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentSeverityTwoTm']        = BUServiceDeskReport::getPercent(
            $fields['severityTwoTm'],
            $fields['totalIncidentsTm']
        );
        $fields['percentSeverityTwoPp']        = BUServiceDeskReport::getPercent(
            $fields['severityTwoPp'],
            $fields['totalIncidentsPp']
        );
        $fields['severityThreeSd']             = $this->getCountIncidents(
            array('contractKey' => 'SD', 'priority' => 3)
        );
        $fields['severityThreeSc']             = $this->getCountIncidents(
            array('contractKey' => 'SC', 'priority' => 3)
        );
        $fields['severityThreePp']             = $this->getCountIncidents(
            array('contractKey' => 'PP', 'priority' => 3)
        );
        $fields['severityThreeTm']             = $this->getCountIncidents(
            array('contractKey' => 'TM', 'priority' => 3)
        );
        $fields['percentSeverityThreeSd']      = BUServiceDeskReport::getPercent(
            $fields['severityThreeSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentSeverityThreeSc']      = BUServiceDeskReport::getPercent(
            $fields['severityThreeSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentSeverityThreeTm']      = BUServiceDeskReport::getPercent(
            $fields['severityThreeTm'],
            $fields['totalIncidentsTm']
        );
        $fields['percentSeverityThreePp']      = BUServiceDeskReport::getPercent(
            $fields['severityThreePp'],
            $fields['totalIncidentsPp']
        );
        $fields['severityFourSd']              = $this->getCountIncidents(
            array('contractKey' => 'SD', 'priority' => 4)
        );
        $fields['severityFourSc']              = $this->getCountIncidents(
            array('contractKey' => 'SC', 'priority' => 4)
        );
        $fields['severityFourPp']              = $this->getCountIncidents(
            array('contractKey' => 'PP', 'priority' => 4)
        );
        $fields['severityFourTm']              = $this->getCountIncidents(
            array('contractKey' => 'TM', 'priority' => 4)
        );
        $fields['percentSeverityFourSd']       = BUServiceDeskReport::getPercent(
            $fields['severityFourSd'],
            $fields['totalIncidentsSd']
        );
        $fields['percentSeverityFourSc']       = BUServiceDeskReport::getPercent(
            $fields['severityFourSc'],
            $fields['totalIncidentsSc']
        );
        $fields['percentSeverityFourTm']       = BUServiceDeskReport::getPercent(
            $fields['severityFourTm'],
            $fields['totalIncidentsTm']
        );
        $fields['percentSeverityFourPp']       = BUServiceDeskReport::getPercent(
            $fields['severityFourPp'],
            $fields['totalIncidentsPp']
        );
        return $fields;
    }

    function getPercent($fraction, $total)
    {
        if ($total > 0) {
            return number_format(($fraction / $total) * 100, 2);
        } else {
            return '';
        }

    }

    function getAve($total, $divisor)
    {
        if ($divisor > 0) {
            return number_format($total / $divisor, 2);
        } else {
            return '';
        }

    }
} // End of class
