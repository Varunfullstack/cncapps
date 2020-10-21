<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 11/01/2019
 * Time: 13:01
 */


require_once("config.inc.php");
global $cfg;
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg["path_bu"] . "/BUActivity.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");


global $cfg;
$thing = null;

processTimeRequestsEmails();
processChangeRequestsEmails();

function processChangeRequestsEmails()
{
    $dbejCallActivity = new DBEJCallActivity($thing);
    $dbejCallActivity->getPendingChangeRequestRows();

    $pendingHDRequests = [];
    $pendingESRequests = [];
    $pendingIMRequests = [];
    $buActivity = new BUActivity($thing);

    while ($dbejCallActivity->fetchNext()) {
        $problemID = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
        $lastActivity = $buActivity->getLastActivityInProblem($problemID);
        $srLink = SITE_URL . '/Activity.php?callActivityID=' . $lastActivity->getValue(
                DBEJCallActivity::callActivityID
            ) . '&action=displayActivity';


        $srLink = "<a href='$srLink'>SR</a>";

        $processCRLink =
            SITE_URL . '/Activity.php?callActivityID=' . $dbejCallActivity->getValue(
                DBEJCallActivity::callActivityID
            ) . '&action=changeRequestReview';

        $processCRLink = "<a href='$processCRLink'>Process Change Request</a>";

        $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
        $requestingUser = new DBEUser($thing);
        $requestingUser->getRow($requestingUserID);

        $teamID = $requestingUser->getValue(DBEUser::teamID);
        $dbeProblem = new DBEJProblem($thing);
        $dbeProblem->getRow($problemID);
        $changeRequested = $dbejCallActivity->getValue(DBEJCallActivity::reason);
        switch ($teamID) {
            case 1:
                $storeArray = &$pendingHDRequests;
                break;
            case 2:
                $storeArray = &$pendingESRequests;
                break;
            case 4:
                $storeArray = &$pendingIMRequests;
        }


        $storeArray[] = [
            'changeRequested'   => $changeRequested,
            'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
            'srLink'            => $srLink,
            'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userName),
            'requestedDateTime' => $dbejCallActivity->getValue(
                    DBEJCallActivity::date
                ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime),
            'processCRLink'     => $processCRLink,
        ];

    }
    sendChangeRequestsEmail(
        'hdtimerequest@cnc-ltd.co.uk',
        $pendingHDRequests
    );
    sendChangeRequestsEmail(
        'eqtimerequest@cnc-ltd.co.uk',
        $pendingESRequests
    );
    sendChangeRequestsEmail(
        'imptimerequest@cnc-ltd.co.uk',
        $pendingIMRequests
    );
}

function addPendingTimeRequestToArray(&$array,
                                      DBEJCallActivity $DBEJCallActivity,
                                      DBEProblem $DBEProblem,
                                      $assignedMinutes,
                                      $usedMinutes,
                                      $teamName,
                                      $isOverLimit
)
{

    $srURL = SITE_URL . "/Activity.php?problemID=" . $DBEJCallActivity->getValue(
            DBEJCallActivity::problemID
        ) . "&action=displayLastActivity";


    $processURL =
        SITE_URL . '/Activity.php?callActivityID=' . $DBEJCallActivity->getValue(
            DBEJCallActivity::callActivityID
        ) . '&action=timeRequestReview';

    $processURL = "<a href='$processURL'>Process Time Request</a>";
    $leftOnBudget = $assignedMinutes - $usedMinutes;

    $array[] = [
        'customerName'      => $DBEJCallActivity->getValue(DBEJCallActivity::customerName),
        'srLink'            => $srURL,
        'notes'             => $DBEJCallActivity->getValue(DBEJCallActivity::reason),
        'requestedBy'       => $DBEJCallActivity->getValue(DBEJCallActivity::userName),
        'requestedDateTime' => "{$DBEJCallActivity->getValue(                DBEJCallActivity::date            )} {$DBEJCallActivity->getValue(DBEJCallActivity::startTime)}:00",
        'processCRLink'     => $processURL,
        'chargeableHours'   => $DBEProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
        'timeSpentSoFar'    => round($usedMinutes, 2),
        'timeLeftOnBudget'  => round($leftOnBudget, 2),
        'requesterTeam'     => $teamName,
        'approvalLevel'     => $isOverLimit ? 'Mgmt' : 'Team Lead',
    ];
}

function processTimeRequestsEmails()
{
    $dbejCallActivity = new DBEJCallActivity($thing);
    $dbejCallActivity->getPendingTimeRequestRows();

    $pendingHDRequests = [];
    $pendingESRequests = [];
    $pendingIMRequests = [];
    $pendingProjectRequests = [];
    $buActivity = new BUActivity($thing);
    $buHeader = new BUHeader($thing);
    $dsHeader = new DataSet($thing);
    $buHeader->getHeader($dsHeader);
    while ($dbejCallActivity->fetchNext()) {
        $problemID = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
        $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
        $requestingUser = new DBEUser($thing);
        $requestingUser->getRow($requestingUserID);
        $teamID = $requestingUser->getValue(DBEUser::teamID);
        $dbeProblem = new DBEJProblem($thing);
        $dbeProblem->getRow($problemID);
        $isOverLimit = false;
        switch ($teamID) {
            case 1:
                $usedMinutes = $buActivity->getHDTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                $teamName = 'Help Desk';
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::hdTeamManagementTimeApprovalMinutes
                    );
                addPendingTimeRequestToArray(
                    $pendingProjectRequests,
                    $dbejCallActivity,
                    $dbeProblem,
                    $assignedMinutes,
                    $usedMinutes,
                    $teamName,
                    $isOverLimit
                );
                break;
            case 2:
                $usedMinutes = $buActivity->getESTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                $teamName = 'Escalation';
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::esTeamManagementTimeApprovalMinutes
                    );
                addPendingTimeRequestToArray(
                    $pendingProjectRequests,
                    $dbejCallActivity,
                    $dbeProblem,
                    $assignedMinutes,
                    $usedMinutes,
                    $teamName,
                    $isOverLimit
                );
                break;
            case 4:
                $usedMinutes = $buActivity->getSPTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                $teamName = 'Small Projects';
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                    );
                addPendingTimeRequestToArray(
                    $pendingProjectRequests,
                    $dbejCallActivity,
                    $dbeProblem,
                    $assignedMinutes,
                    $usedMinutes,
                    $teamName,
                    $isOverLimit
                );
                break;
            case 5:
                $usedMinutes = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                $teamName = 'Projects';
                addPendingTimeRequestToArray(
                    $pendingProjectRequests,
                    $dbejCallActivity,
                    $dbeProblem,
                    $assignedMinutes,
                    $usedMinutes,
                    $teamName,
                    $isOverLimit
                );
        }
    }
    sendTimeRequestsEmail(
        'hdtimerequest@cnc-ltd.co.uk',
        $pendingHDRequests
    );
    sendTimeRequestsEmail(
        'eqtimerequest@cnc-ltd.co.uk',
        $pendingESRequests
    );
    sendTimeRequestsEmail(
        'sptimerequest@cnc-ltd.co.uk',
        $pendingIMRequests
    );
    sendTimeRequestsEmail(
        'projectstimerequest@cnc-ltd.co.uk',
        $pendingProjectRequests
    );
}

function sendTimeRequestsEmail($teamEmail,
                               $requests
)
{

    if (!count($requests)) {
        return;
    }
    $thing = null;
    $buMail = new BUMail($thing);

    $senderEmail = CONFIG_SUPPORT_EMAIL;
    global $twig;
    $body = $twig->render('@internal/pendingTimeRequestsEmail.html.twig', ["items" => $requests]);

    $toEmail = $teamEmail;

    $hdrs = array(
        'From'         => $senderEmail,
        'To'           => $toEmail,
        'Subject'      => "Pending Time Requests",
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );

    $body = $buMail->mime->get($mime_params);

    $hdrs = $buMail->mime->headers($hdrs);


    $buMail->putInQueue(
        $senderEmail,
        $toEmail,
        $hdrs,
        $body
    );
}

function sendChangeRequestsEmail($teamEmail,
                                 $requests
)
{

    if (!count($requests)) {
        return;
    }
    global $cfg;
    $thing = null;
    $buMail = new BUMail($thing);

    $senderEmail = CONFIG_SUPPORT_EMAIL;

    $template = new Template(
        EMAIL_TEMPLATE_DIR,
        "remove"
    );

    $template->set_file(
        'page',
        'PendingChangeRequestsEmail.inc.html'
    );

    $requestsTemplate = new Template(
        $cfg["path_templates"],
        "remove"
    );


    $requestsTemplate->setFile(
        'ChangeRequestDashboard',
        'ChangeRequestDashboard.html'
    );


    $requestsTemplate->set_block(
        'ChangeRequestDashboard',
        'ChangeRequestsBlock',
        'changeRequests'
    );


    foreach ($requests as $request) {
        $requestsTemplate->set_var($request);
        $requestsTemplate->parse(
            'changeRequests',
            'ChangeRequestsBlock',
            true
        );
    }

    $requestsTemplate->parse(
        'output',
        'ChangeRequestDashboard',
        true
    );

    $requestsTable = $requestsTemplate->get_var('output');
    $template->setVar(['requestsTable' => $requestsTable]);

    $template->parse(
        'output',
        'page',
        true
    );

    $body = $template->getVar('output');

    $toEmail = $teamEmail;

    $hdrs = array(
        'From'         => $senderEmail,
        'To'           => $toEmail,
        'Subject'      => "Pending Change Requests",
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );

    $body = $buMail->mime->get($mime_params);

    $hdrs = $buMail->mime->headers($hdrs);


    $buMail->putInQueue(
        $senderEmail,
        $toEmail,
        $hdrs,
        $body
    );
}
