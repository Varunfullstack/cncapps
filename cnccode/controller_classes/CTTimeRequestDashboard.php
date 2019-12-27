<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");

class CTTimeRequestDashboard extends CTCNC
{
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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {

            default:
                $this->displayReport();
                break;
        }
    }

    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'TimeRequestDashboard',
            'TimeRequestDashboard'
        );

        $this->setPageTitle('Time Request Dashboard');

        $dbejCallActivity = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingTimeRequestRows();

        $this->template->set_block(
            'TimeRequestDashboard',
            'TimeRequestsBlock',
            'timeRequests'
        );

        $buActivity = new BUActivity($this);

        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        while ($dbejCallActivity->fetchNext()) {
            $problemID = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
            $lastActivity = $buActivity->getLastActivityInProblem($problemID);
            $srLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $lastActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "displayActivity"
                ]
            );

            $srLink = "<a href='$srLink' target='_blank'>" . $problemID . "</a>";

            $processCRLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "timeRequestReview"
                ]
            );

            $processCRLink = "<a href='$processCRLink'>Process Time Request</a>";

            $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
            $requestingUser = new DBEUser($this);
            $requestingUser->getRow($requestingUserID);

            $teamID = $requestingUser->getValue(DBEUser::teamID);

            $leftOnBudget = null;
            $usedMinutes = 0;
            $assignedMinutes = 0;

            $dbeProblem = new DBEJProblem($this);
            $dbeProblem->getRow($problemID);
            $teamName = '';
            switch ($teamID) {
                case 1:
                    $usedMinutes = $buActivity->getHDTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                    $teamName = 'Helpdesk';
                    break;
                case 2:
                    $usedMinutes = $buActivity->getESTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                    $teamName = 'Escalation';
                    break;
                case 4:
                    $usedMinutes = $buActivity->getSPTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                    $teamName = 'Implementation';
            }

            $leftOnBudget = $assignedMinutes - $usedMinutes;
            $requestedDateTimeString = $dbejCallActivity->getValue(
                    DBEJCallActivity::date
                ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ":00";
            $requestedDateTime = DateTime::createFromFormat(DATE_MYSQL_DATETIME);
            $alertTime = (new DateTime(''))->sub(
                new DateInterval('PT' . $dsHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes) . "M")
            );

            $this->template->set_var(
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'srLink'            => $srLink,
                    'notes'             => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userName),
                    'requestedDateTime' => $requestedDateTimeString,
                    'processCRLink'     => $processCRLink,
                    'chargeableHours'   => $dbeProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
                    'timeSpentSoFar'    => round($usedMinutes),
                    'timeLeftOnBudget'  => $leftOnBudget,
                    'requesterTeam'     => $teamName,
                    'alertRow'          => $requestedDateTime < $alertTime ? 'warning' : null,
                ]
            );

            $this->template->parse(
                'timeRequests',
                'TimeRequestsBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'TimeRequestDashboard',
            true
        );
        $this->parsePage();


    }
}