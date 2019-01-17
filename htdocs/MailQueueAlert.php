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

define(
    'EMAIL_SUBJECT',
    'Email Queue Problem'
);

$error = false;


if ($server_type == MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT) {
    $send_to_email = CONFIG_CATCHALL_EMAIL;
} else {
    $send_to_email = 'MailQueueAlert@cnc-ltd.co.uk';
}

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
$count = $db->Record[0];

if ($count > 0) {

    $body = "$count emails have been in the mail queue for longer than 30 minutes.\n";
    $buMail = new BUMail($this);

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
        'Subject'      => EMAIL_SUBJECT,
        'Content-Type' => 'text/html; charset=UTF-8',
        'To'           => $send_to_email
    );

    $hdrs = $buMail->mime->headers($hdrs);

    $sent = $buMail->send(
        $send_to_email,
        $hdrs,
        $body,
        true
    );

    if ($sent) {
        echo "message sent";
    } else {
        echo "not sent";

    }

}
?>