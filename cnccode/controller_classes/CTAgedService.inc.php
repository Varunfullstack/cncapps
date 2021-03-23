<?php
/**
 * MIS Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Html2Text\Html2Text;

global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUDailyReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTAgedService extends CTCNC
{
    private $buDailyReport;
    const GET_OUTSTANDING_INCIDENTS = 'getoutstandingIncidents';
    const GET_YEARS                 = 'years';

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
                echo json_encode($this->getOutStandingIncidents(), JSON_NUMERIC_CHECK);
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

    function getOutStandingIncidents()
    {
        $daysAgo  = $this->getParam("daysAgo") ?? 1;
        $data     = [];
        $hd       = $this->getParam("hd") == 'true';
        $es       = $this->getParam("es") == 'true';
        $sp       = $this->getParam("sp") == 'true';
        $p        = $this->getParam("p") == 'true';
        $p5       = false;
        $rows     = $this->buDailyReport->getOutstandingRequests(
            $daysAgo,
            $p5,
            $hd,
            $es,
            $sp,
            $p
        );
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        while ($row = $rows->fetch_row()) {
            [
                $customer,
                $serviceRequestId,
                $assignedTo,
                $description,
                $durationHours,
                $timeSpentHours,
                $lastUpdatedDate,
                $priority,
                $teamName,
                $awaitingCustomer,
                $status,
                $queueNumber,
            ] = $row;
            $urlRequest  = Controller::buildLink(
                SITE_URL . '/SRActivity.php',
                array(
                    'serviceRequestId' => $serviceRequestId,
                    "action"           => "displayActivity"
                )
            );
            $description = (new Html2Text($description))->getText();
            $data [] = [
                'customer'         => $customer,
                'serviceRequestID' => $serviceRequestId,
                'assignedTo'       => $assignedTo,
                'description'      => $description,
                'durationHours'    => $durationHours,
                'timeSpentHours'   => $timeSpentHours,
                'lastUpdatedDate'  => $lastUpdatedDate,
                'priority'         => $priority,
                'teamName'         => $teamName,
                'awaiting'         => $status == 'I' ? 'Not Started' : ($awaitingCustomer == 'Y' ? 'Customer' : 'CNC'),
                "status"           => $status,
                "awaitingCustomer" => $awaitingCustomer,
                'urlRequest'       => $urlRequest,
                'rowClass'         => $durationHours >= $dsHeader->getValue(
                    DBEHeader::sevenDayerRedDays
                ) ? 'red-row' : ($durationHours >= $dsHeader->getValue(
                    DBEHeader::sevenDayerAmberDays
                ) ? 'amber-row' : ''),
                'amberThreshold'   => $dsHeader->getValue(DBEHeader::sevenDayerAmberDays),
                'redThreshold'     => $dsHeader->getValue(DBEHeader::sevenDayerRedDays),
                'queueNo'          => $queueNumber
            ];
        }
        return $data;
    }

    function getYears()
    {
        return DBConnect::fetchAll("SELECT  DISTINCT YEAR(date) AS YEAR  FROM  sevenDayersPerformanceLog", []);
    }

} // end of class
