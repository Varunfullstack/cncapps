<?php
/**
* Mail sending classes
*
* Uses PEAR mail classes as these are well tested and feature-rich
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @static
*/
require_once(CONFIG_PATH_SC_CLASSES			. 'object.php');
class SC_Mail extends SC_Object{
	
	var $container_options=array();
	
	var $mail_options=array();
	
	function SC_Mail()
	{
		$this->__construct();
	}
	function __construct()
	{
		parent::__construct();
		$this->container_options =
			array(
				'type'		=>	'db',							// type of container (MySQL DB)
				'dsn'     =>	'mysql://' .
											CONFIG_DB_USERNAME .
											':' .
											CONFIG_DB_PASSWORD .
											'@' .
											CONFIG_DB_HOST .
											'/' .
											CONFIG_DB_NAME,
				'mail_table'
									=> 'mail_queue'
			);
		$this->mail_options =	array('driver' => 'mail');
	}
	/**
	* send email now
	*/
	function sendDirect(
		$recipients,
		$headers,
		$body,
		$try_in_background = false					// if not sent carry on trying
	)
	{
		require_once(CONFIG_PATH_PEAR_CLASSES . 'Mail/Mail.php');
		$mail_object =& Mail::factory('mail');
		/*
		Try to send it now
		*/
		$ret =
			$mail_object->send(
			$recipients,
			$headers,
			$body
		);	
		/*
		If couldn't send it now and allowed to carry on trying, try later
		*/
		if ( !$ret & $try_in_background ){
			$this->putInQueue(
				$recipients,
				$headers,
				$body,
				true
			);
		}
	
		return $ret;
	}
	/**
	* Put email in the queue
	*/
	function putInQueue(
		&$recipients,
		&$headers,
		$body,
		$delete_after_send = true,
		$seconds_until_send = 0
	)
	{
		$authenticate = &$GLOBALS['authenticate'];			// for the current userID

		require_once(CONFIG_PATH_PEAR_CLASSES . 'Mail/Queue.php');
		$mail_queue_object =new Mail_Queue(
			$this->container_options,
			$this->mail_options
		);
		return
			$mail_queue_object->put(
				$headers['From'],
				$recipients,
				$headers,
				$body,
				$seconds_until_send,
				$delete_after_send,
				$authenticate->getUserID()
			);
	}
	/**
	* send any emails in the queue up to maximum of $limit
	*/
	function sendMailsInQueue( $limit=1000 )
	{
		require_once(CONFIG_PATH_PEAR_CLASSES . 'Mail/Queue.php');
		$mail_queue_object =new Mail_Queue(
			$this->container_options,
			$this->mail_options
		);
		$mail_queue_object->sendMailsInQueue( $limit );
	}
	function sendMailByID( $mail_queue_id )
	{
		if ( !$mail_queue_id ){
			$this->raiseError('SC_Mail::sendMailByID: no $mail_queue_id passed');
		}
		require_once(CONFIG_PATH_PEAR_CLASSES . 'Mail/Queue.php');
		$mail_queue_object =new Mail_Queue(
			$this->container_options,
			$this->mail_options
		);
		$mail_queue_object->sendMailByID( $mail_queue_id );
	}
	function deleteMailByID( $mail_queue_id )
	{
		require_once(CONFIG_PATH_PEAR_CLASSES . 'Mail/Queue.php');
		$mail_queue_object =new Mail_Queue(
			$this->container_options,
			$this->mail_options
		);
		$mail_queue_object->deleteMail( $mail_queue_id );
	}
} // end class SC_Mail

?>