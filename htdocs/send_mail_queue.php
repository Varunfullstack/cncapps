<?php
		/* really send the messages */
		require_once("config.inc.php");
		require_once ("Mail.php");
		require_once ("Mail/mime.php");
		require_once ("Mail/Queue.php");
		$mail_queue =& new Mail_Queue($GLOBALS['db_options'], $GLOBALS['mail_options']);
		$mail_queue->sendMailsInQueue(1000);
?>
