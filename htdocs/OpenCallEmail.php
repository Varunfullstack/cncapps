<?php
/**
* notify each CNC users about their outstanding support calls
*
* called as scheduled task at given time every day
*
* @authors Karim Ahmed - Sweet Code Limited
*/

require_once("config.inc.php");

define('OS_CALL_EMAIL_FROM_USER', 'sales@cnc-ltd.co.uk'); 
define('OS_CALL_EMAIL_SUBJECT', 'Open SR Activities'); 
define( 'FORMAT_MYSQL_UK_DATE', '%e/%c/%Y' );


if ( $GLOBALS['server_type'] == MAIN_CONFIG_SERVER_TYPE_LIVE ){
	$domain = 'cnc-ltd.co.uk';
}
else{
	$domain = 'sweetcode.co.uk';
}

if (!$db=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
	echo 'Could not connect to mysql host ' . DB_HOST;
	exit;
}

/*
get list of engineers with open calls
*/
mysql_select_db(DB_NAME, $db);
$query =
	'SELECT
		DISTINCT CONCAT(firstName, " ", lastName) AS Engineer,
		cns_logname,
		cns_consno 
	 FROM callactivity '.
  ' JOIN problem ON pro_problemno = caa_problemno'.
	' JOIN callacttype ON cat_callacttypeno = caa_callacttypeno'.
	' JOIN consultant ON caa_consno = cns_consno'.
	' JOIN customer ON pro_custno = cus_custno'.
	' WHERE caa_endtime = ""'.
	' AND caa_date <= NOW()';												// and on-site

echo $query;

$engineers = mysql_query($query);

/*
Send each engineer an email
*/
while($row = mysql_fetch_assoc($engineers)) { 

	$query =
		'SELECT caa_callactivityno, cus_name, CONCAT(firstName, " ", lastName), caa_date'. 
		' FROM callactivity '.
    ' JOIN problem ON pro_problemno = caa_problemno'.
		' JOIN callacttype ON cat_callacttypeno = caa_callacttypeno'.
		' JOIN consultant ON caa_consno = cns_consno'.
		' JOIN customer ON pro_custno = cus_custno'.
    ' WHERE caa_endtime = ""'.
		' AND caa_date <= NOW()'.																// in the past
		' AND caa_consno = ' . $row['cns_consno'] .
		' ORDER BY caa_date, pro_custno';

echo $query;
	
	$result = mysql_query($query);
	$sender_name = "Call System";
	$sender_email = OS_CALL_EMAIL_FROM_USER;
	$headers = "From: " . $sender_name." <" . $sender_email . ">\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html";
	ob_start()
	?>
	<HTML>
	<P>
	Your following activities are open (no end time entered):
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
				Date
			</TD>
		</TR>
		<?php
		while($i = mysql_fetch_row($result)) { 
		?>
		<TR>
			<TD>
				<A href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/Activity.php?action=displayActivity&callActivityID=<?php print $i[0]?>"><?php print $i[0] ?></A>
			</TD>
			<TD>
				<?php print $i[1] ?>
			</TD>
			<TD>
				<?php print $i[3] ?>
			</TD>
		</TR>
		<?php
		}
		?>
	</TABLE>
	</HTML>
	<?php
	$body = ob_get_contents();
	ob_end_clean();
	mail(
		$row['cns_logname'] . '@' . $domain,
		OS_CALL_EMAIL_SUBJECT,
		$body,
		$headers
	);
} // end while
//header( 'Location:/' );
?>
