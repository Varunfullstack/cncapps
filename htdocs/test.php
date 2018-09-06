<?php


require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

$buMail = new BUMail($thing);
$thing = null;
$buActivity = new BUActivity($thing);


$buMail->mime->setHTMLBody("<div>this is a test</div>");

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);
$senderEmail = CONFIG_SUPPORT_EMAIL;

$toEmail = "guerreradelviento@gmail.com";

$cc = "fizdalf@gmail.com";

$bcc = "publixavi@gmail.com";

$toEmail = implode(
    ";",
    [$toEmail, $cc, $bcc]
);

$hdrs = array(
    'From'         => $senderEmail,
    'To'           => $toEmail,
    'Subject'      => 'Testeando',
    'Date'         => date("r"),
    'Content-Type' => 'text/html; charset=UTF-8',
    'Cc'           => $cc,
);
$recipients = "xavi@pavilionweb.co.uk";
$hdrs = $buMail->mime->headers($hdrs);

$buMail->putInQueue(
    $senderEmail,
    $recipients,
    $hdrs,
    $body
);
var_dump($buMail->sendQueue());

exit;


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
