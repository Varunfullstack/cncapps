<?php
/**
 * Email to support administrator with activity details
 *
 * Parameter:
 *    Period:        D=Daily summary today
 *                        M=Monthly summary this month
 *
 * called as scheduled task at given time every day
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once("config.inc.php");

require_once("Mail.php");
require_once("Mail/Mime.php");
require_once($cfg['path_func'] . '/common.inc.php');
define('EMAIL_FROM_USER', 'support@cnc-ltd.co.uk');
define('EMAIL_SUBJECT', 'Serverguard and FOC Activity Report');

$send_to_email = CONFIG_SUPPORT_ADMINISTRATOR_EMAIL;

/*
Serverguard today
*/
$query =
    "SELECT
	caa_callactivityno AS 'Call Ref',
	cus_name AS 'Customer',
	caa_date AS 'Date',
	cat_desc AS 'Activity',
	reason AS 'Reason'
FROM callactivity
  JOIN problem ON pro_problemno = caa_problemno
	JOIN callacttype ON callacttype.cat_callacttypeno = callactivity.caa_callacttypeno
	JOIN customer ON customer.cus_custno = pro_custno
WHERE
	caa_serverguard = 'Y'
	AND caa_date = CURDATE()
ORDER BY
	caa_starttime
";

$HTMLTables = '<H2>Server Guard</H2>';

$HTMLTables .= common_sqlToHTML($query);

/*
FOC today
*/
$query =
    "SELECT
	caa_callactivityno AS 'Call Ref',
	cus_name AS 'Customer',
	caa_date AS 'Date',
	cat_desc AS 'Activity',
	reason AS 'Reason'
FROM callactivity
  JOIN problem ON pro_problemno = caa_problemno
	JOIN callacttype ON callacttype.cat_callacttypeno = callactivity.caa_callacttypeno
	JOIN customer ON customer.cus_custno = problem.pro_custno
	JOIN item ON cat_itemno = item.itm_itemno 
WHERE
	itm_sstk_price = 0
	AND caa_date = CURDATE()
ORDER BY
	caa_starttime";

$HTMLTables .= '<H2>Free Of Charge Today</H2>';

$HTMLTables .= common_sqlToHTML($query);

ob_start()
?>
    <HTML>
    <style type="text/css">
        <!--
        BODY {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        -->
    </style>
    <BODY>
    <?php echo $HTMLTables ?>
    </BODY>
    </HTML>
<?php

$html = ob_get_contents();
ob_end_clean();

$hdrs_array = array(
    'From' => EMAIL_FROM_USER,
    'To' => $send_to_email,
    'Subject' => EMAIL_SUBJECT,
    'Content-Type' => 'text/html; charset=UTF-8'
);

$mime = new Mail_mime();

$mime->setHTMLBody($html);

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset' => 'UTF-8',
    'html_charset' => 'UTF-8',
    'head_charset' => 'UTF-8'
);
$body = $mime->get($mime_params);

$hdrs = $mime->headers($hdrs_array);

// Create the mail object using the Mail::factory method
$mail_object = Mail::factory('mail');

$mail_object->send($send_to_email, $hdrs, $body);

header('Location:/index.php');
?>