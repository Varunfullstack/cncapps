<?php
/**
* Check that the mail queue has no emails older than 15 minutes
*
* If it does then email graham and gary and Karim
*
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");

require_once($cfg["path_bu"]."/BUMail.inc.php");

define('EMAIL_SUBJECT', 'Email Queue Problem'); 

$error = false;

$send_to_email =
  'grahaml@cnc-ltd.co.uk,' .
  'karim@sweetcode.co.uk,' .
	CONFIG_SALES_MANAGER_EMAIL;
	
$sql = "
  SELECT 
    COUNT(*)
  FROM
    `mail_queue`
  WHERE
    TIMEDIFF( NOW(), time_to_send ) > '00:30:00'
    AND sent_time IS NULL";

$db->query( $sql );    
$db->next_record();
$count = $db->Record[0];

if ( $count > 0 ){

	$body = "$count emails have been in the mail queue for longer than 30 minutes.\n";
	
	$hdrs_array = array(
		'From'		=> CONFIG_SALES_MANAGER_EMAIL,
		'Subject'	=> EMAIL_SUBJECT
	);

  $buMail= new BUMail( $this );

  $buMail->mime->setTXTBody( $body );

  $body = $buMail->mime->get ();

  $hdrs = array(
    'From'    => CONFIG_SALES_MANAGER_EMAIL,
    'Subject'  => EMAIL_SUBJECT
  );

  $hdrs = $buMail->mime->headers ( $hdrs );

  $sent = $buMail->send(
    $send_to_email,
    $hdrs,
    $body,
    true
  );
  
  if ( $sent ){
    echo "message sent";
  }
  else{
    echo "not sent";
    
  }

}
?>