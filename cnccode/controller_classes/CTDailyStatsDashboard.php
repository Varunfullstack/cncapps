<?php

namespace CNCLTD\Controller;

use CNCLTD\DailyStatsDashboard\Core\DailyStatsDashboardDTO;
use CTCNC;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTDailyStatsDashboard extends CTCNC
{
    const GET_NEAR_SLA        = "getNearSLA";
    const NEAR_FIX_SLA_BREACH = "nearFixSLABreach";
    const RAISED_ON           = "raisedOn";
    const STARTED_ON          = "startedOn";
    const FIXED_ON            = "fixedOn";

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

    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_NEAR_SLA :
            {
                json_encode($this->getNearSLAServiceRequestsController());
                break;
            }
            case self::NEAR_FIX_SLA_BREACH:
            {
                json_encode($this->getNearFixSLABreachServiceRequestsController());
                break;
            }
            case self::RAISED_ON:
            {
                json_encode($this->getRaisedOnServiceRequestsController());
                break;
            }
            case self::STARTED_ON:
            {
                json_encode($this->getStartedOnServiceRequestsController());
                break;
            }
            case self::FIXED_ON:
            {
                json_encode($this->getFixedOnServiceRequestsController());
                break;
            }
        }
    }

    private function getNearSLAServiceRequestsController(): array
    {
        $serviceRequestDB = new \DBEJProblem($this);
        $serviceRequestDB->getNearSLA();
        return $this->getJsonResponse($serviceRequestDB);
    }

    private function getNearFixSLABreachServiceRequestsController(): array
    {
        $serviceRequestDB = new \DBEJProblem($this);
        $serviceRequestDB->getDashBoardRows(1000000, "shortestSLAFixRemaining");
        return $this->getJsonResponse($serviceRequestDB);
    }

    private function getRaisedonServiceRequestsController()
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new \DBEJProblem($this);
        $serviceRequestDB->getRaisedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @param \DBEJProblem $serviceRequestDB
     * @return array
     */
    private function getJsonResponse(\DBEJProblem $serviceRequestDB): array
    {
        $toReturn = [];
        while ($serviceRequestDB->fetchNext()) {
            $toReturn[] = DailyStatsDashboardDTO::fromServiceRequestDB($serviceRequestDB);
        }
        return ["status" => "ok", "data" => $toReturn];
    }

    private function getStartedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new \DBEJProblem($this);
        $serviceRequestDB->getStartedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }

    /**
     * @return \DateTimeImmutable
     */
    private function getRequestedDateOrToday(): \DateTimeImmutable
    {
        $date          = new \DateTimeImmutable();
        $requestedDate = @$_REQUEST['date'];
        if ($requestedDate) {
            $possibleDate = \DateTimeImmutable::createFromFormat(DATE_MYSQL_DATE, $requestedDate);
            if ($possibleDate) {
                $date = $possibleDate;
            }
        }
        return $date;
    }

    private function getFixedOnServiceRequestsController(): array
    {
        $date             = $this->getRequestedDateOrToday();
        $serviceRequestDB = new \DBEJProblem($this);
        $serviceRequestDB->getFixedOn($date);
        return $this->getJsonResponse($serviceRequestDB);
    }
}