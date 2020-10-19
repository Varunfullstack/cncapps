<?php
/**
 * Check that the mail queue has no emails older than 15 minutes
 *
 * If it does then email graham and gary and Karim
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");

global $server_type;

$error = false;
$thing = null;


if ($server_type == MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT) {
    $send_to_email = CONFIG_CATCHALL_EMAIL;
} else {
    $send_to_email = 'MailQueueAlert@cnc-ltd.co.uk';
}

function getCountOfMessagesInQueueForMoreThan30Minutes()
{
    global $db;
    $sql = "
  SELECT 
    COUNT(*)
  FROM
    `mail_queue`
  WHERE
    TIMEDIFF( NOW(), time_to_send ) > '00:30:00'
    AND sent_time IS NULL";

    $db->query($sql);
    $db->next_record();
    return $db->Record[0];
}

function getRowCountInAutomatedRequest()
{
    global $db;
    $sql = "
  SELECT 
    COUNT(*)
  FROM
    automated_request
  ";

    $db->query($sql);
    $db->next_record();
    return $db->Record[0];
}


function sendAlertEmail($body, $toEmail, $subject)
{
    $buMail = new BUMail($thing);

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $body = $buMail->mime->get($mime_params);

    $hdrs = array(
        'From'         => CONFIG_SALES_MANAGER_EMAIL,
        'Subject'      => $subject,
        'Content-Type' => 'text/html; charset=UTF-8',
        'To'           => $toEmail
    );

    $hdrs = $buMail->mime->headers($hdrs);

    return $buMail->send(
        $toEmail,
        $hdrs,
        $body
    );
}

if ($count = getCountOfMessagesInQueueForMoreThan30Minutes()) {
    $body = "$count emails have been in the mail queue for longer than 30 minutes.\n";
    sendAlertEmail($body, $send_to_email, 'Email Queue Problem');
}
$countOfMessagesInQueue = getRowCountInAutomatedRequest();
if ($countOfMessagesInQueue > 30) {
    $body = "There are $countOfMessagesInQueue rows in the automated_request table, please check the import process to confirm it is still working.\n";
    sendAlertEmail($body, $send_to_email, 'Automated Request Import Problem');
}