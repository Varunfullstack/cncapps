<?php
/**
 * MIS Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUDailyReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBConnect.php");

use CNCLTD\Utils;
class CTAgedService extends CTCNC
{
    private $buDailyReport;   
    const GET_OUTSTANDING_INCIDENTS ='getoutstandingIncidents';
    const GET_YEARS='years';
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $permissions = [
            'technical'
        ];
        if (!self::hasPermissions($permissions)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buDailyReport = new BUDailyReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) { 
            case self::GET_OUTSTANDING_INCIDENTS:
                echo json_encode($this->getOutStandingIncidents());
                break;
            case self::GET_YEARS:
                echo json_encode($this->getYears());
                break;
            default :
            $this->setTemplateReact();
        }
    }
     
    function setTemplateReact()
    {        
        $this->setPageTitle('Aged Service Requests');
        $this->setTemplateFiles(
            array('DailyReport' => 'DailyReport.rct')
        );
        $this->loadReactScript('DailyReportComponent.js');
        $this->loadReactCSS('DailyReportComponent.css');
        $this->template->parse(
            'CONTENTS',
            'DailyReport',
            true
        );
        $this->setMenuId(110);
        $this->parsePage();
    }
    function getOutStandingIncidents(){
        $daysAgo=$this->getParam("daysAgo")??1;
        $data =[];
        $hd=$this->getParam("hd")=='true';
        $es=$this->getParam("es")=='true';
        $sp=$this->getParam("sp")=='true';
        $p=$this->getParam("p")=='true';
        $p5=$this->getParam("p5")=='true';
        $p5=false;
        $rows=$this->buDailyReport->getOustandingRequests(
            $daysAgo,
            $p5,
            $hd,
            $es,
            $sp,
            $p
        );
        $buHeader   = new BUHeader($this);
        $dsHeader   = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        
        while($row = $rows->fetch_row())
        {
            $urlRequest  = Controller::buildLink(
                SITE_URL . '/SRActivity.php',
                array(
                    'serviceRequestId' => $row[1],
                    "action" => "displayActivity"
                )
            );
            $description = substr(
                Utils::stripEverything($row[3]),
                0,
                50
            );
            $data []= array(
                'customer'            => $row[0],
                'serviceRequestID'    => $row[1],
                'assignedTo'          => $row[2],
                'description'         => substr(
                    Utils::stripEverything($row[3]),
                    0,
                    50
                ),
                'durationHours'       => $row[4],
                'timeSpentHours'      => $row[5],
                'lastUpdatedDate'     => $row[6] ? Controller::dateYMDtoDMY($row[6]) : null,
                'lastUpdatedDateSort' => $row[6],
                'priority'            => $row[7],
                'teamName'            => $row[8],
                'awaiting'            => $row[10] == 'I' ? 'Not Started' : ($row[9] == 'Y' ? 'Customer' : 'CNC'),
                'urlRequest'          => $urlRequest,
                'rowClass'            => $row[4] >= $dsHeader->getValue(
                    DBEHeader::sevenDayerRedDays
                ) ? 'red-row' : ($row[4] >= $dsHeader->getValue(
                    DBEHeader::sevenDayerAmberDays
                ) ? 'amber-row' : ''),
                'amberThreshold'      => $dsHeader->getValue(DBEHeader::sevenDayerAmberDays),
                'redThreshold'        => $dsHeader->getValue(DBEHeader::sevenDayerRedDays),
                'queueNo'             =>$row[11]
                );
        }
        return $data; 
    }
    function getYears(){
        return DBConnect::fetchAll("SELECT  DISTINCT YEAR(date) AS YEAR  FROM  sevenDayersPerformanceLog",[]);
    }

} // end of class
