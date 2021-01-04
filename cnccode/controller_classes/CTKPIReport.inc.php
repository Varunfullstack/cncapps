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
            $query .="  AND callactivity.caa_date >= :to ";
            $params["to"]=$to;
        }
        $query .="GROUP BY callactivity.caa_date";        
        return DBConnect::fetchAll($query,$params);
    }
}