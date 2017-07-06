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
		$message = '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= 'Dear ' . $dsContact->getValue('firstName') . ',';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= 'Following our recent telephone conversation, I am pleased to confirm your appointment with '. $attendeeName . ' on ' . $_REQUEST['meetingDate']  . ' at ' . $_REQUEST['meetingTime'].'.<BR/>';
		$message .= '</p>';
		$message .= '<p>';
		$message .= $dbeAttendeeUser->getValue('firstName') . ' looks forward to meeting you and if you require any further assistance please do not hesitate to contact us.';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= 'Kind regards,';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
		$message .= $senderName . ' - ' . $dbeUser->getValue('jobTitle');
		$message .= '<BR/><BR/>Computer & Network Consultants Ltd, Unit 9, Riverside Business Centre, Brighton Road,';
		$message .= 'Shoreham-by-Sea, BN43 6RE';
		$message .= '<o:p></o:p></span></font></p>';
		$message .= '<p class=MsoNormal><font size=2 face=Arial><span lang=EN-GB style=\'font-size:10.0pt;mso-ansi-language:EN-GB\'>E-Mail:';
		$message .= '<a href="mailto:"'.$senderEmail.'">'.$senderEmail.'</a><br>';
		$message .= 'Tel: +44(0)1273 386333<BR/>';
		$message .= 'Fax: +44(0)1273 386444<BR/>';
		$message .= 'Web: http://www.cnc-ltd.co.uk<BR/>';
		$subject = 'Meeting confirmation with ' . $attendeeName;
		$headers .= "From: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "BCC: grahaml@cnc-ltd.co.uk\r\n";
		$headers .= "Return-Receipt-To: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "Disposition-Notification-To: ".$senderName." <".$senderEmail.">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html";
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
