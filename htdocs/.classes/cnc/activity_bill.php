<?php
/**
*	CNC_ActivityBill class
*
* @author Karim Ahmed
* (C) CNC Ltd 2005
* @access Public
*/
//require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'db.php');

class CNC_ActivityBill extends SC_DB {
	/**
	* @access public
	*/
	function CNC_ActivityBill() {
		$this->__construct();
	}
	function __construct()
	{
		$this->setDisplayName('Activities');
		parent::__construct('callactivity');
	}
	/*
	*	Parent will call this automatically
	*/
	function addFields()
	{
		$this->addField(
			$this->getTableFieldName('caa_callactivityno'),
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
			'customer.cus_name',
			array(
				'label' 		=> 'Customer',
				'help'			=> '',
				'type'			=> SC_DB_NAME,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'address.add_add1',
			array(
				'label' 		=> 'Site',
				'help'			=> '',
				'type'			=> SC_DB_STRING_50,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'callactivity.reason',
			array(
				'label' 		=> 'Details',
				'help'			=> '',
				'type'			=> SC_DB_STRING_50,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'callactivity.caa_date',
			array(
				'label' 		=> 'Date',
				'help'			=> '',
				'type'			=> SC_DB_UK_DATE,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
	}
	function getPKName(){
		return 'callactivity.caa_callactivityno';
	}
	function getSQLFromSection()
	{
			return
					$this->getTableName() . CR .
					TAB .	'JOIN customer ON customer.cus_custno = callactivity.caa_custno' . CR .
					TAB .	'JOIN address ON address.add_siteno = callactivity.caa_siteno AND' .CR .
					TAB . 'address.add_custno = callactivity.caa_custno';
	}
} //end class
?>