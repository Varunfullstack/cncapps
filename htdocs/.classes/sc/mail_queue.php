<?php
/**
*	SC_MailQueue class
*
* @author Karim Ahmed
* (C) Sweetcode Ltd 2005
* @access Public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_MPM_CLASSES . 'db.php');

class SC_MailQueue extends MPM_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function SC_MailQueue() {
		$this->__construct();
	}
	function __construct()
	{
		$this->setDisplayName('Mail Queue');
		$this->setTableName('mail_queue');
		parent::__construct();
	}
	function addFields()
	{
		$this->addField(
			'mail_queue.mail_queue_id',
			array(
				'label' 		=> 'ID',
				'help'			=> 'Internal, unique, system ID',
				'type'			=> SC_DB_ID,
				'required'	=> false,
				'unique'		=> true,
				'default'		=> '',
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			'mail_queue.time_to_send',
			array(
				'label' 		=> 'Scheduled time',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'mail_queue.sent_time',
			array(
				'label' 		=> 'Sent',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'mail_queue.ip',
			array(
				'label' 		=> 'IP Address',
				'help'			=> '',
				'type'			=> SC_DB_IP,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'mail_queue.sender',
			array(
				'label' 		=> 'Sender',
				'help'			=> '',
				'type'			=> SC_DB_EMAIL,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'mail_queue.recipient',
			array(
				'label' 		=> 'Recipient',
				'help'			=> '',
				'type'			=> SC_DB_EMAIL,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'mail_queue.headers',
			array(
				'label' 		=> 'Headers',
				'help'			=> '',
				'type'			=> SC_DB_EMAIL_HEADERS,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			'mail_queue.body',
			array(
				'label' 		=> 'Body',
				'help'			=> '',
				'type'			=> SC_DB_EMAIL_BODY,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			'mail_queue.try_sent',
			array(
				'label' 		=> 'Tries',
				'help'			=> '',
				'type'			=> SC_DB_INTEGER_5,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			'mail_queue.delete_after_send',
			array(
				'label' 		=> 'Delete after send?',
				'help'			=> '',
				'type'			=> SC_DB_BOOL,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		parent::addFields();
	}
	/**
	* send an email by ID
	*/
	function sendMailByID(	$mail_queue_id )
	{
		require_once(CONFIG_PATH_SC_CLASSES . 'mail.php');
		$mail = new SC_Mail();
		$mail->sendMailByID( $mail_queue_id	);	
	}
	/**
	* delete an email by ID
	*/
	function deleteMailByID(	$mail_queue_id )
	{
		require_once(CONFIG_PATH_SC_CLASSES . 'mail.php');
		$mail = new SC_Mail();
		$mail->deleteMailByID( $mail_queue_id	);	
	}
} //end class SC_MailQueue
?>