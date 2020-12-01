<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");

class CTChangeRequestDashboard extends CTCNC
{
    const GET_DATA = 'getData';

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
        $this->setMenuId(203);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case self::GET_DATA:
            {
                $showHelpDesk      = isset($_REQUEST['HD']);
                $showEscalation    = isset($_REQUEST['ES']);
                $showSmallProjects = isset($_REQUEST['SP']);
                $showProjects      = isset($_REQUEST['P']);
                $dbejCallActivity = new DBEJCallActivity($this);
                $dbejCallActivity->getPendingChangeRequestRows(
                    $showHelpDesk,
                    $showEscalation,
                    $showSmallProjects,
                    $showProjects
                );
                $result = [];
                while ($dbejCallActivity->fetchNext()) {
                    $result[] = [
                        'customerName'     => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                        'serviceRequestId' => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                        'requestBody'      => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                        'requesterName'    => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                        'requestedAt'      => $dbejCallActivity->getValue(
                                DBEJCallActivity::date
                            ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ':00',
                        'activityId'       => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID)
                    ];
                }
                echo json_encode(["status" => "ok", "data" => $result]);
                exit;
            }
            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');
        $this->setTemplateFiles(
            'ChangeRequestDashboard',
            'ChangeRequestDashboard'
        );
        $this->setPageTitle('Change Request Dashboard');
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $dbejCallActivity = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingChangeRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        $this->template->set_block(
            'ChangeRequestDashboard',
            'ChangeRequestsBlock',
            'changeRequests'
        );
        $buActivity = new BUActivity($this);
        while ($dbejCallActivity->fetchNext()) {

            $lastActivity = $buActivity->getLastActivityInProblem(
                $dbejCallActivity->getValue(DBEJCallActivity::problemID)
            );
            $srLink       = Controller::buildLink(
                'SRActivity.php',
                [
                    "callActivityID" => $lastActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "displayActivity"
                ]
            );
            $srLink = "<a href='$srLink'>SR</a>";
//            http://cncdev7:85/Activity.php?action=changeRequestReview&callActivityID=1813051&fromEmail=true
            $processCRLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "changeRequestReview"
                ]
            );
            $processCRLink = "<a href='$processCRLink'>Process Change Request</a>";
            $this->template->set_var(
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'srLink'            => $srLink,
                    'changeRequested'   => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                    'requestedDateTime' => $dbejCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime),
                    'processCRLink'     => $processCRLink,
                ]
            );
            $this->template->parse(
                'changeRequests',
                'ChangeRequestsBlock',
                true
            );
        }
        $this->template->set_var(
            [
                "helpDeskChecked"      => $showHelpDesk ? "checked" : null,
                "escalationChecked"    => $showEscalation ? "checked" : null,
                "smallProjectsChecked" => $showSmallProjects ? "checked" : null,
                "projectsChecked"      => $showProjects ? "checked" : null
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'ChangeRequestDashboard',
            true
        );
        $this->parsePage();


    }
}