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

define('EMAIL_FROM_USER', 'sales@cnc-ltd.co.uk');
define('EMAIL_SUBJECT', 'Activities logged');
define('FORMAT_MYSQL_UK_DATE', '%e/%c/%Y');
define('FORMAT_MYSQL_UK_DATETIME', '%e/%c/%Y %H:%i');

$send_to_email = CONFIG_SUPPORT_ADMINISTRATOR_EMAIL;

if (!$db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}
mysql_select_db(DB_NAME, $db);
$query =
    'SELECT
		caa_callactivityno,
		DATE_FORMAT( caa_date, "' . FORMAT_MYSQL_UK_DATE . '") AS date, 
		cus_name,
		cns_logname,
		reason,
		caa_consno,
		caa_starttime,
		caa_endtime,
		(TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600 as hours,
		DATE_FORMAT( created, "' . FORMAT_MYSQL_UK_DATETIME . '" ) as created' . ' ,
		cat_desc,
		if (itm_sstk_price > 0, "Yes", "No") AS chargable
	FROM callactivity
    JOIN problem on caa_problemno = pro_problemno
		JOIN consultant ON caa_consno = cns_consno
		JOIN customer ON pro_custno = cus_custno
		JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
		JOIN item ON itm_itemno = cat_itemno
	WHERE';

if ($_REQUEST['period'] == 'M') {
    $query .= ' caa_date >= DATE_SUB( CURDATE(), INTERVAL 1 MONTH )';
} else {
    $query .= ' caa_date = CURDATE()';
}

$query .= ' ORDER BY caa_consno, caa_date, caa_starttime';

$result = mysql_query($query);

ob_start()
?>
    <HTML>
    <style type="text/css">
        <!--
        .style1 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        -->
    </style>
    <BODY class="style1">
    <TABLE class="style1">
        <TR bgcolor="#CCCCCC">
            <TD nowrap><B>Ref</B></TD>
            <TD nowrap><B>Created</B></TD>
            <TD nowrap><B>Date</B></TD>
            <TD nowrap><B>Start Time</B></TD>
            <TD nowrap><B>End Time</B></TD>
            <TD nowrap><B>Duration</B></TD>
            <TD nowrap><B>Engineer</B></TD>
            <TD nowrap><B>Activity</B></TD>
            <TD nowrap><B>Customer</B></TD>
            <TD nowrap><B>Reason</B></TD>
            <TD nowrap><B>Charge</B></TD>
        </TR>
        <?php
        $total_duration = 0;
        $total_engineer_duration = 0;
        $last_consno = 99999;
        $last_engineer = '';

        while ($i = mysql_fetch_assoc($result)) {
            if (($last_consno != $i['caa_consno']) AND $last_consno != 99999) {
                ?>
                <TR bgcolor="#FFFFCC">
                    <TD colspan="5"><B>Total for <?php print $last_engineer ?></B></TD>
                    <TD><B><?php print $total_engineer_duration ?></B></TD>
                    <TD>&nbsp;</TD>
                    <TD>&nbsp;</TD>
                    <TD>&nbsp;</TD>
                    <TD>&nbsp;</TD>
                    <TD>&nbsp;</TD>
                </TR>
                <?php
                $total_engineer_duration = 0;

            }
            ?>
            <TR valign="top">
                <TD nowrap>
                    <A href="http://cncapps/Activity.php?action=displayActivity&callActivityID=<?php print $i['caa_callactivityno'] ?>"><?php print $i['caa_callactivityno'] ?></A>
                </TD>
                <TD nowrap><?php print $i['created'] ?> </TD>
                <TD nowrap><?php print $i['date'] ?> </TD>
                <TD nowrap><?php print $i['caa_starttime'] ?></TD>
                <TD nowrap><?php print $i['caa_endtime'] ?></TD>
                <TD nowrap><?php print $i['hours'] ?></TD>
                <TD nowrap><?php print $i['cns_logname'] ?></TD>
                <TD nowrap><?php print $i['cat_desc'] ?></TD>
                <TD nowrap><?php print $i['cus_name'] ?> </TD>
                <TD><?php print $i['reason'] ?> </TD>
                <TD nowrap><?php print $i['chargable'] ?> </TD>
            </TR>
            <?php

            $total_duration += $i['hours'];
            $total_engineer_duration += $i['hours'];

            $last_consno = $i['caa_consno'];
            $last_engineer = $i['cns_logname'];
        } // end while
        ?>
        <TR bgcolor="#FFFFCC">
            <TD colspan="5"><B>Total for <?php print $last_engineer ?></B></TD>
            <TD><B><?php print $total_engineer_duration ?></B></TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
        </TR>
        <TR bgcolor="#FFFF99">
            <TD colspan="5"><B>Grand Total</B></TD>
            <TD><B><?php print $total_duration ?></B></TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
            <TD>&nbsp;</TD>
        </TR>
    </TABLE>
    </BODY>
    </HTML>
<?php

if ($last_consno == 99999) {                    // no activities so exit
    header('Location:/index.php');
    exit;
}

$html = ob_get_contents();
ob_end_clean();

$crlf = "\n";

/*
Build CSV attachment
*/
$csv_attachment =
    'Ref,Created,Date,Start Time,End Time,Duration,Engineer,Activity,Customer,Reason,Charge' . $crlf;

$result = mysql_query($query);

while ($i = mysql_fetch_assoc($result)) {

    $csv_attachment .=
        $i['caa_callactivityno'] . ',' .
        $i['created'] . ',' .
        $i['date'] . ',' .
        $i['caa_starttime'] . ',' .
        $i['caa_endtime'] . ',' .
        $i['hours'] . ',' .
        $i['cns_logname'] . ',' .
        $i['cat_desc'] . ',' .
        $i['cus_name'] . ',' .
        $i['reason'] . ',' .
        $i['chargable'] . $crlf;

} // end while

$hdrs_array = array(
    'From' => EMAIL_FROM_USER,
    'To' => $send_to_email,
    'Subject' => EMAIL_SUBJECT,
    'Content-Type' => 'text/html; charset=UTF-8'
);

$mime = new Mail_mime($crlf);

$mime->setHTMLBody($html);
$mime->addAttachment($csv_attachment, 'text/csv', 'activities.csv', false);

$body = $mime->get();

$hdrs = $mime->headers($hdrs_array);

// Create the mail object using the Mail::factory method
$mail_object =& Mail::factory('mail');

$mail_object->send($send_to_email, $hdrs, $body);

header('Location:/index.php');
?>