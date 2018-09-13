<?php


require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');


/**
 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 8-10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
 * which is a good baseline for systems handling interactive logins.
 */
$timeTarget = 0.05; // 50 milliseconds

$cost = 8;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

echo "Appropriate Cost Found: " . $cost;
exit;

$buMail = new BUMail($thing);
$thing = null;
$buActivity = new BUActivity($thing);

$results = new DataSet($thing);
$buActivity->getActivityByID(
    1640238,
    $results
);
$template = new Template(
    EMAIL_TEMPLATE_DIR,
    "remove"
);
$template->set_file(
    'page',
    'MonitoringEmail.inc.html'
);

$urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' .
    $results->getValue(DBECallActivity::callActivityID);
$activityRef = $results->getValue(DBEJCallActivity::problemID) . ' ' .
    $results->getValue(DBEJCallActivity::customerName);
$durationHours = common_convertHHMMToDecimal(
        $results->getValue(DBEJCallActivity::endTime)
    ) - common_convertHHMMToDecimal($results->getValue(DBEJCallActivity::startTime));

$awaitingCustomerResponse = null;

if ($results->getValue(DBEJCallActivity::requestAwaitingCustomerResponseFlag) == 'Y') {
    $awaitingCustomerResponse = 'Awaiting Customer';
} else {
    $awaitingCustomerResponse = 'Awaiting CNC';
}


$template->setVar(
    array(
        'activityRef'                 => $activityRef,
        'activityDate'                => $results->getValue(DBEJCallActivity::date),
        'activityStartTime'           => $results->getValue(DBEJCallActivity::startTime),
        'activityEndTime'             => $results->getValue(DBEJCallActivity::endTime),
        'activityTypeName'            => $results->getValue(DBEJCallActivity::activityType),
        'urlActivity'                 => $urlActivity,
        'userName'                    => $results->getValue(DBEJCallActivity::userName),
        'durationHours'               => round(
            $durationHours,
            2
        ),
        'requestStatus'               => true,
        'awaitingCustomerResponse'    => $awaitingCustomerResponse,
        'customerName'                => $results->getValue(DBEJCallActivity::customerName),
        'reason'                      => $results->getValue(DBEJCallActivity::reason),
        'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC
    )
);

$template->parse(
    'output',
    'page',
    true
);

$body = $template->get_var('output');


$toEmail = "xavi@pavilionweb.co.uk";

$senderEmail = CONFIG_SUPPORT_EMAIL;
$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => 'Monitored SR ' . $results->getValue(
            DBEJCallActivity::problemID
        ) . ' For ' . $results->getValue(DBEJCallActivity::customerName),
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8'
);
$body = preg_replace(
    '/[\x00-\x1F\x7F-\xFF]/',
    '',
    $body
);
$body = preg_replace(
    '/[\x00-\x1F\x7F]/',
    '',
    $body
);
$body = preg_replace(
    '/[\x00-\x1F\x7F]/u',
    '',
    $body
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
    $body,
    true
);

$buMail->sendQueue();
