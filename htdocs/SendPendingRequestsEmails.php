<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 11/01/2019
 * Time: 13:01
 */


require_once("config.inc.php");
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg["path_bu"] . "/BUActivity.inc.php");


global $cfg;

$thing = null;
$dbejCallActivity = new DBEJCallActivity($thing);
$dbejCallActivity->getPendingTimeRequestRows();

$pendingHDRequests = [];
$pendingESRequests = [];
$pendingIMRequests = [];
$buActivity = new BUActivity($thing);

while ($dbejCallActivity->fetchNext()) {

    $row = [];
    $problemID = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
    $lastActivity = $buActivity->getLastActivityInProblem($problemID);
    $srLink = 'http://cncapps/Activity.php?callActivityID=' . $lastActivity->getValue(
            DBEJCallActivity::callActivityID
        ) . '&action=displayActivity';


    $srLink = "<a href='$srLink'>SR</a>";

    $processCRLink =
        'http://cncapps/Activity.php?callActivityID=' . $dbejCallActivity->getValue(
            DBEJCallActivity::callActivityID
        ) . '&action=timeRequestReview';

    $processCRLink = "<a href='$processCRLink'>Process Time Request</a>";

    $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
    $requestingUser = new DBEUser($thing);
    $requestingUser->getRow($requestingUserID);

    $teamID = $requestingUser->getValue(DBEUser::teamID);

    $leftOnBudget = null;
    $usedMinutes = 0;
    $assignedMinutes = 0;

    $dbeProblem = new DBEJProblem($thing);
    $dbeProblem->getRow($problemID);
    $teamName = '';
    switch ($teamID) {
        case 1:
            $usedMinutes = $buActivity->getHDTeamUsedTime($problemID);
            $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
            $teamName = 'Help Desk';
            $storeArray = &$pendingHDRequests;
            break;
        case 2:
            $usedMinutes = $buActivity->getESTeamUsedTime($problemID);
            $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
            $teamName = 'Escalation';
            $storeArray = &$pendingESRequests;
            break;
        case 4:
            $usedMinutes = $buActivity->getIMTeamUsedTime($problemID);
            $assignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);
            $teamName = 'Implementation';
            $storeArray = &$pendingIMRequests;
    }

    $leftOnBudget = $assignedMinutes - $usedMinutes;

    $storeArray[] = [
        'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
        'srLink'            => $srLink,
        'notes'             => $dbejCallActivity->getValue(DBEJCallActivity::reason),
        'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userName),
        'requestedDateTime' => $dbejCallActivity->getValue(
                DBEJCallActivity::date
            ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime),
        'processCRLink'     => $processCRLink,
        'chargeableHours'   => $dbeProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
        'timeSpentSoFar'    => $usedMinutes,
        'timeLeftOnBudget'  => $leftOnBudget,
        'requesterTeam'     => $teamName,
    ];

}

sendRequestsEmail(
    'hdtimerequest@cnc-ltd.co.uk',
    $pendingHDRequests
);
sendRequestsEmail(
    'eqtimerequest@cnc-ltd.co.uk',
    $pendingESRequests
);
sendRequestsEmail(
    'imptimerequest@cnc-ltd.co.uk',
    $pendingIMRequests
);


function sendRequestsEmail($teamEmail,
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
        'PendingTimeRequestsEmail.inc.html'
    );

    $requestsTemplate = new Template(
        $cfg["path_templates"],
        "remove"
    );


    $requestsTemplate->setFile(
        'TimeRequestDashboard',
        'TimeRequestDashboard.html'
    );


    $requestsTemplate->set_block(
        'TimeRequestDashboard',
        'TimeRequestsBlock',
        'timeRequests'
    );


    foreach ($requests as $request) {
        $requestsTemplate->set_var($request);
        $requestsTemplate->parse(
            'timeRequests',
            'TimeRequestsBlock',
            true
        );
    }

    $requestsTemplate->parse(
        'output',
        'TimeRequestDashboard',
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
