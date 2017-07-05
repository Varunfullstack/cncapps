<?php
/**
* notify certain CNC users about outstanding support calls
*
* called as scheduled task at given time every day
*
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");

define('OS_CALL_EMAIL_FROM_USER', 'sales@cnc-ltd.co.uk'); 
define('OS_CALL_EMAIL_SUBJECT', 'Open Call Activities'); 
define( 'FORMAT_MYSQL_UK_DATE', '%e/%c/%Y' );

define( 'OUTPUT_TO_SCREEN', false );


$send_to_email =	CONFIG_SUPPORT_ADMINISTRATOR_EMAIL;// . ',' .				// roger
//	CONFIG_SALES_MANAGER_EMAIL;					// Gary does not need this now 21/10/2007
	
if (!$db=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
	echo 'Could not connect to mysql host ' . DB_HOST;
	exit;
}
mysql_select_db(DB_NAME, $db);
$query =
	'SELECT caa_callactivityno, cus_name, CONCAT(firstName, " ", lastName), caa_date'. 
	' FROM callactivity '.
  ' JOIN problem ON pro_problemno = caa_problemno'.
	' JOIN callacttype ON cat_callacttypeno = caa_callacttypeno'.
	' JOIN consultant ON caa_consno = cns_consno'.
	' JOIN customer ON pro_custno = cus_custno'.
  ' WHERE caa_end_time = ""' .
	' AND caa_date <= NOW()'.																// in the past
	' AND cat_req_check_flag ="Y"' .												// and on-site
	' ORDER BY caa_date, pro_custno, caa_consno';

$result = mysql_query($query);
$sender_name = "Call System";
$sender_email = OS_CALL_EMAIL_FROM_USER;
$headers = "From: " . $sender_name." <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";

if ( !OUTPUT_TO_SCREEN ){

	ob_start();

}
?>
<HTML>
<P>
The following call activities are open:
</P>
<TABLE>
	<TR>
		<TD>
			Ref
		</TD>
		<TD>
			Customer
		</TD>
		<TD>
			Engineer
		</TD>
		<TD>
			Date
		</TD>
	</TR>
	<?php
	while($i = mysql_fetch_row($result)) { 
	?>
	<TR>
		<TD>
			<A href="http://cncapps/Activity.php?action=displayActivity&callActivityID=<?php print $i[0]?>"><?php print $i[0] ?></A>
		</TD>
		<TD>
			<?php print $i[1] ?>
		</TD>
		<TD>
			<?php print $i[2] ?>
		</TD>
		<TD>
			<?php print $i[3] ?>
		</TD>
	</TR>
	<?php
	}
	?>
</TABLE>
<?php
$query =
	'SELECT caa_callactivityno, cus_name, CONCAT(firstName, " ", lastName), caa_date,'.
	'cat_desc'. 
	' FROM callactivity '.
  ' JOIN problem ON pro_problemno = caa_problemno'.
	' JOIN callacttype ON cat_callacttypeno = caa_callacttypeno'.
	' JOIN consultant ON caa_consno = cns_consno'.
	' JOIN customer ON pro_custno = cus_custno'.
	' AND caa_endtime != ""' .				// closed
	' AND caa_status = "O"';					// not checked yet

$result = mysql_query($query);
?>
<P>
The following call activities are closed but not checked:
</P>
<TABLE>
	<TR>
		<TD>
			Call Ref
		</TD>
		<TD>
			Customer
		</TD>
		<TD>
			Engineer
		</TD>
		<TD>
			Date
		</TD>
		<TD>
			Activity
		</TD>
	</TR>
	<?php
	while($i = mysql_fetch_row($result)) { 
	?>
	<TR>
		<TD>
			<A href="http://cncapps/Activity.php?action=editActivity&callActivityID=<?php print $i[0]?>"><?php print $i[0] ?></A>
		</TD>
		<TD>
			<?php print $i[1] ?>
		</TD>
		<TD>
			<?php print $i[2] ?>
		</TD>
		<TD>
			<?php print $i[3] ?>
		</TD>
		<TD>
			<?php print $i[4] ?>
		</TD>
	</TR>
	<?php
	}
	?>
</TABLE>
</HTML>
<?php
if ( !OUTPUT_TO_SCREEN ){
	$body = ob_get_contents();
	ob_end_clean();

	if (!	mail(
		$send_to_email,
		OS_CALL_EMAIL_SUBJECT,
		$body,
		$headers
	)){
		echo "Mail Failed";
		exit;
	}
}
?>