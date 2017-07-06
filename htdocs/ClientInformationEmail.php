<?php
/**
* Client information email
* CNC Ltd
*
* Send an email to customer contact confirming meeting appointment
* included from ClientInformation.php
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
		$senderEmail = $dbeUser->getValue('username').'@cnc-ltd.co.uk';
		$senderName = $dbeUser->getValue('firstName') . ' ' . $dbeUser->getValue('lastName');
		$attendeeName = $dbeAttendeeUser->getValue('firstName') . ' ' . $dbeAttendeeUser->getValue('lastName');
	// Send email with attachment
		$attendeeEmail = $dbeAttendeeUser->getValue('username').'@cnc-ltd.co.uk';
		$message = '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= 'Dear ' . $dsContact->getValue('title') . ' ' . $dsContact->getValue('lastName') . ',';
    $message .= '<o:p></o:p></span></font></p>';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= 'Following our recent telephone conversation, I am pleased to confirm your appointment with '. $attendeeName . ' on ' . $_REQUEST['meetingDate']  . ' at ' . $_REQUEST['meetingTime'].'.<BR/>';
		$message .= '</p>';
    $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= $dbeAttendeeUser->getValue('firstName') . ' looks forward to meeting you and if you require any further assistance please do not hesitate to contact us.';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
    $message .= 'Kind regards';
		$message .= '<o:p></o:p></span></font></p>';
		$subject = 'Meeting confirmation with ' . $attendeeName;
		$headers .= "From: ".$senderName." <".$senderEmail.">\r\n";
/*
For some reason the BCC must not have the name otherwise sending fails
*/
		$headers .= "BCC: ". $attendeeEmail. "\r\n";
		$headers .= "Return-Receipt-To: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "Return-Path: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "Disposition-Notification-To: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html";

		ini_set ("sendmail_from", $senderEmail);		// the envelope from address
		
		if (mail($dsContact->getValue('email'), $subject, $message, $headers)){
			$_REQUEST['emailStatus'] = 'Email sent to client';
		}
		else{
			$_REQUEST['emailStatus'] = 'Email NOT sent to client - there was a problem';
		}

		// not part of request vars so set here
		$_REQUEST['contactName'] = $dsContact->getValue('firstName'). ' ' .$dsContact->getValue('lastName');

		$controller=new Controller();  // need this for buildLink()
		$next = $controller->buildLink(
			"ClientInformationForm.php",
			$_REQUEST
		);
		header("Location: ".$next);
?>
