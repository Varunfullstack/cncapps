<?php
/**
* Email to support administrator with activity details
*
* Parameter:
*	Period:		D=Daily summary today
*						M=Monthly summary this month
*
* called as scheduled task at given time every day
*
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");

require_once("Mail.php");
require_once("Mail/Mime.php");

define('EMAIL_FROM_USER', 'sales@cnc-ltd.co.uk'); 
define('EMAIL_SUBJECT', 'Replication Problem Alert'); 

define('MASTER_HOST', 'cncapps'); 
define('SLAVE_HOST', 'cncdrapps'); 

$error = false;

$send_to_email =
	'karim@sweetcode.co.uk,' .
	CONFIG_SUPPORT_MANAGER_EMAIL . ',' .
	CONFIG_SUPPORT_ADMINISTRATOR_EMAIL . ',' .				// roger
	CONFIG_SALES_MANAGER_EMAIL;	
	
if ( !$master_link = mysql_connect( MASTER_HOST, DB_USER, DB_PASSWORD ) ) {
	$error = "Can not connect to master host at " . MASTER_HOST . "\n";
}
if ( !$slave_link = mysql_connect( SLAVE_HOST, DB_USER, DB_PASSWORD, true ) ) {
	$error .= 'Can not connect to slave host at ' . SLAVE_HOST . "\n";
}

if ( !mysql_select_db(DB_NAME, $master_link) ){
	$error .= 'Can not select master db at ' . MASTER_HOST . "\n";
}

if ( !mysql_select_db(DB_NAME, $slave_link) ){
	$error .= 'Can not select slave db at ' . SLAVE_HOST . "\n";
}

if ( !$error ){

	$query = 'SELECT COUNT(*) FROM callactivity';
	
	$master_result = mysql_query( $query, $master_link );
	
	$slave_result = mysql_query( $query, $slave_link );
	
	$master_row = mysql_fetch_row( $master_result );
	
	$slave_row = mysql_fetch_row( $slave_result );

	/*
	If the number of rows in callactivity differ accross servers then send an alert email
	*/
	if ( $master_row[ 0 ] != $slave_row[ 0 ] ){
	
		$error .= 'The replication slave and master databases have different numbers of callactivity rows' . "\n"; 
		
	}

}

if ($error ){

	$error .= 'You should only be concerned if you get lots of these messages. If you stop getting them then the connection is up  and replication has caught up again.' . "\n"; 

	$hdrs_array = array(
		'From'		=> EMAIL_FROM_USER,
		'To' 			=> $send_to_email,
		'Subject'	=> EMAIL_SUBJECT
	);
	
	$mime = new Mail_mime();
		
	$mime->setTxtBody( $error );
	
	$body = $mime->get();
	
	$hdrs = $mime->headers( $hdrs_array );
	
	// Create the mail object using the Mail::factory method
	$mail_object =& Mail::factory('mail');
	
	$mail_object->send($send_to_email, $hdrs, $body);

}
?>