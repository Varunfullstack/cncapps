<?php

namespace CNCLTD\Controller;

use CNCLTD\DailyStatsDashboard\Core\DailyStatsDashboardDTO;
use CNCLTD\Data\DBEJProblem;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CTCNC;
use DateTimeImmutable;
use Exception;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTDailyStatsDashboard extends CTCNC
{
    const NEAR_SLA            = "nearSLA";
    const NEAR_FIX_SLA_BREACH = "nearFixSLABreach";
    const RAISED_ON           = "raisedOn";
    const STARTED_ON          = "startedOn";
    const FIXED_ON            = "fixedOn";
    const REOPENED_ON         = "reopenedOn";
    const BREACHED_SLA_ON     = "breachedSLAOn";

    /**
     * CTDailyStatsDashboard constructor.
     */
    public function __construct($requestMethod,
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
            $cfg,
        );
    }

    /**
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::NEAR_SLA :
            {
                $jsonData = json_encode($this->getNearSLAServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::NEAR_FIX_SLA_BREACH:
            {
                $jsonData = json_encode($this->getNearFixSLABreachServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::RAISED_ON:
            {
                $jsonData = json_encode($this->getRaisedOnServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::STARTED_ON:
            {
                $jsonData = json_encode($this->getStartedOnServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::FIXED_ON:
            {
                $jsonData = json_encode($this->getFixedOnServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::REOPENED_ON:
            {
                $jsonData = json_encode($this->getReopenedOnServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            case self::BREACHED_SLA_ON:
            {
                $jsonData = json_encode($this->getBreachedSLAOnServiceRequestsController(), JSON_NUMERIC_CHECK);
                echo $jsonData;
                break;
            }
            default:
            {
                $this->mainPageController();
            }
        }
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getNearSLAServiceRequestsController(): array
    {
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getNearSLA();
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getNearFixSLABreachServiceRequestsController(): array
    {
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getDashBoardRows(1000000, "shortestSLAFixRemaining");
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getRaisedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getRaisedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @param DBEJProblem $serviceRequestDB
     * @return array
     * @throws ColumnOutOfRangeException
     */
    private function getJsonResponse(DBEJProblem $serviceRequestDB): array
    {
        $toReturn = [];
        while ($serviceRequestDB->fetchNext()) {
            $toReturn[] = DailyStatsDashboardDTO::fromServiceRequestDB($serviceRequestDB);
        }
        return ["status" => "ok", "data" => $toReturn];
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getStartedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getStartedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @return DateTimeImmutable
     */
    private function getRequestedDateOrToday(): DateTimeImmutable
    {
        $date          = new DateTimeImmutable();
        $requestedDate = @$_REQUEST['date'];
        if ($requestedDate) {
            $possibleDate = DateTimeImmutable::createFromFormat(DATE_MYSQL_DATE, $requestedDate);
            if ($possibleDate) {
                $date = $possibleDate;
            }
        }
        return $date;
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getFixedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getFixedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getReopenedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getReopenedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function getBreachedSLAOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new DBEJProblem($this);
        $serviceRequestDB->getBreachedSLAOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @throws Exception
     */
    private function mainPageController()
    {
        $this->setMenuId(513);
        $this->setPageTitle('Daily Stats Dashboard');
        $this->setTemplateFiles(
            array('DailyStatsDashboard' => 'DailyStatsDashboard.rct')
        );
        $this->loadReactScript('DailyStatsDashboardComponent.js');
        $this->loadReactCSS('DailyStatsDashboardComponent.css');
        $this->template->parse(
            'CONTENTS',
            'DailyStatsDashboard',
            true
        );
        $this->parsePage();
    }
}