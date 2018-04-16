<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 13/04/2018
 * Time: 12:28
 */

/**
 * Check that the mail queue has no emails older than 15 minutes
 *
 * If it does then email graham and gary and Karim
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

$dateToTest = @$_REQUEST['testDate'];

$error = false;

$bankHolidays = common_getUKBankHolidays(date('Y'));

if (!$dateToTest) {
    $dateToTest = Date('Y-m-d', strtotime("+3 days"));
}


// Exclude bank holidays and weekends

if (!in_array($dateToTest, $bankHolidays)) {
    echo 'The date tested is not a bank holidays .. do nothing';
    exit;
}

echo 'The date tested is a bank holiday... proceed';

if (!$db1 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}
$db1->select_db(DB_NAME);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";

/*
Unprinted purchase orders email to Gary
*/
$query =
    "SELECT 
      con_email,
      con_first_name,
      con_last_name
    FROM
      contact 
      LEFT JOIN address 
        ON address.`add_custno` = con_custno 
        AND address.`add_siteno` = con_siteno 
    WHERE contact.`con_mailflag5` = 'Y' 
      AND address.`add_active_flag` = 'Y'
      AND address.add_non_uk_flag = 'Y'";


$result = $db1->query($query);
$subject = "UK National Holiday - CNC ServiceDesk Availability";

foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {


    $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
    $template->set_file('page', 'NonUKHolidaysEmail.html');

    $template->set_var('contactName', $row['con_first_name']);

    $template->set_var('date', Date('l jS F', strtotime($dateToTest)));

    $template->parse('output', 'page', true);

    $body = $template->get_var('output');

    $buMail = new BUMail($this);

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset' => 'UTF-8',
        'html_charset' => 'UTF-8',
        'head_charset' => 'UTF-8'
    );
    $body = $buMail->mime->get($mime_params);

    $hdrs = array(
        'From' => CONFIG_SUPPORT_EMAIL,
        'Subject' => $subject,
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $hdrs = $buMail->mime->headers($hdrs);

    $sent = $buMail->putInQueue(
        CONFIG_SUPPORT_EMAIL,
        $row['con_email'],
        $hdrs,
        $body,
        true
    );

}

//
//
//if ($server_type == MAIN_CONFIG_SERVER_TYPE_DEVELOPMENT) {
//    $send_to_email = CONFIG_CATCHALL_EMAIL;
//} else {
//    $send_to_email = 'grahaml@cnc-ltd.co.uk,' . CONFIG_SALES_MANAGER_EMAIL;
//}
//
//$sql = "
//  SELECT
//    COUNT(*)
//  FROM
//    `mail_queue`
//  WHERE
//    TIMEDIFF( NOW(), time_to_send ) > '00:30:00'
//    AND sent_time IS NULL";
//
//$db->query($sql);
//$db->next_record();
//$count = $db->Record[0];
//
//if ($count > 0) {
//
//    $body = "$count emails have been in the mail queue for longer than 30 minutes.\n";
//
//    $hdrs_array = array(
//        'From' => CONFIG_SALES_MANAGER_EMAIL,
//        'Subject' => EMAIL_SUBJECT,
//        'Content-Type' => 'text/html; charset=UTF-8'
//    );
//
//    $buMail = new BUMail($this);
//
//    $buMail->mime->setHTMLBody($body);
//
//    $mime_params = array(
//        'text_encoding' => '7bit',
//        'text_charset' => 'UTF-8',
//        'html_charset' => 'UTF-8',
//        'head_charset' => 'UTF-8'
//    );
//    $body = $buMail->mime->get($mime_params);
//
//    $hdrs = array(
//        'From' => CONFIG_SALES_MANAGER_EMAIL,
//        'Subject' => EMAIL_SUBJECT,
//        'Content-Type' => 'text/html; charset=UTF-8'
//    );
//
//    $hdrs = $buMail->mime->headers($hdrs);
//
//    $sent = $buMail->send(
//        $send_to_email,
//        $hdrs,
//        $body,
//        true
//    );
//
//    if ($sent) {
//        echo "message sent";
//    } else {
//        echo "not sent";
//
//    }
//
//}