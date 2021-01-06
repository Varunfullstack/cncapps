<?php
/**
 * MIS Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUMISReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTKPIReport extends CTCNC
{
    const GET_SRFIXED="SRFixed";
    const GET_PRIORITY_RAISED="priorityRiased";
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        // $roles = [
        //     "accounts",
        // ];
        // if (!self::hasPermissions($roles)) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
        // $this->buMISReport = new BUMISReport ($this);
        // $this->dsSearchForm = new DSForm ($this);
        // $this->dsResults = new DataSet ($this);
    }

    
/**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_SRFIXED:
                echo json_encode($this->getSRFixedData());
                exit;
            case self::GET_PRIORITY_RAISED:
                echo json_encode($this->getPriorityRaisedData());
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }
    function setTemplate()
    {        
        $this->setMenuId(513);
        $this->setPageTitle('KPI Reports');
        $this->setTemplateFiles(
            array('KPIReport' => 'KPIReport.rct')
        );
        $this->loadReactScript('KPIReportComponent.js');
        $this->loadReactCSS('KPIReportComponent.css');
        $this->template->parse(
            'CONTENTS',
            'KPIReport',
            true
        );
        $this->parsePage();
    }

    function getSRFixedData()
    {
        $from=$_REQUEST["from"];
        $to=$_REQUEST["to"];
        $customerID=$_REQUEST["customerID"];
        $query="SELECT
        SUM(1) allTeams,
        SUM(consultant.`teamID` = 1) AS helpDeskFixedActivities,
        SUM(consultant.`teamID` = 2) AS escalationsFixedActivities,
        SUM(consultant.`teamID` = 4) AS smallProjectsActivities,
        SUM(consultant.`teamID` = 5) AS projectsActivities,
        callactivity.caa_date date
      FROM
        callactivity
        JOIN consultant
          ON consultant.`cns_consno` = callactivity.`caa_consno`
        JOIN problem p ON p.`pro_problemno` = callactivity.`caa_problemno`

      WHERE     callactivity.`caa_callacttypeno` = 57
        AND p.`pro_custno`<>282
        AND consultant.`teamID` < 6
        AND consultant.`cns_consno` <> 67
        ";
        $params=array();
        if($from!='')
        {
            $query .="  AND callactivity.caa_date >= :from ";
            $params["from"]=$from;
        }
        if($to!='')
        {
            $query .="  AND callactivity.caa_date <= :to ";
            $params["to"]=$to;
        }
        if($customerID!='')
        {
            $query .="  AND p.pro_custno = :pro_custno ";
            $params["pro_custno"]=$customerID;
        }
        $query .="GROUP BY callactivity.caa_date order by date";        
        return DBConnect::fetchAll($query,$params);
    }
    function getPriorityRaisedData()
    {
        $from=$_REQUEST["from"];
        $to=$_REQUEST["to"];
        $customerID=$_REQUEST["customerID"];
        $query=" SELECT
        
        DATE_FORMAT( problem.pro_date_raised,'%Y-%m-%d') AS date,
        SUM(1) AS raisedAll,
        SUM(pro_priority = 1) AS P1,
        SUM(pro_priority = 2) AS P2,
        SUM(pro_priority = 3) AS P3,
        SUM(pro_priority = 4) AS P4
        FROM
        problem
        JOIN customer
        ON customer.cus_custno = problem.pro_custno

      WHERE     problem.pro_priority < 5
        AND problem.`pro_custno`<>282 ";
        $params=array();
        if($from!='')
        {
            $query .="  AND problem.pro_date_raised >= :from ";
            $params["from"]=$from;
        }
        if($to!='')
        {
            $query .="  AND problem.pro_date_raised <= :to ";
            $params["to"]=$to;
        }
        if($customerID!='')
        {
            $query .="  AND problem.pro_custno = :pro_custno ";
            $params["pro_custno"]=$customerID;
        }
        $query .=" GROUP BY problem.pro_date_raised order by date";        
        return DBConnect::fetchAll($query,$params);
    }
}