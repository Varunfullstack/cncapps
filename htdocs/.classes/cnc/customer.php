<?php
/**
*	CNC_Customer class
*
* @author Karim Ahmed
* (C) CNC Ltd 2005
* @access Public
*/
//require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'db.php');

class CNC_Customer extends SC_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function CNC_Customer() {
		$this->__construct();
	}
	function __construct()
	{
		$this->setDisplayName('Customer');
		parent::__construct('customer');
	}
	/*
	*	Parent will call this automatically
	*/
	function addFields()
	{
		$this->addField(
			$this->getTableFieldName('cus_custno'),
			array(
				'label' 		=> 'Customer ID',
				'help'			=> 'Internal, unique, system ID',
				'type'			=> SC_DB_ID,
				'required'	=> false,
				'unique'		=> true,
				'default'		=> '',
				'can_edit'	=> false,
				'is_select'	=> true,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableFieldName('cus_name'),
			array(
				'label' 		=> 'Name',
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
			$this->getTableFieldName('cus_mailshot'),
			array(
				'label' 		=> 'Mailshot',
				'help'			=> '',
				'type'			=> SC_DB_STRING_1,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
	$this->addField(
			'inv_address.add_add1',
			array(
				'label' 		=> 'Inv Add1',
				'help'			=> '',
				'type'			=> SC_DB_ADDRESS,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'inv_address.add_add2',
			array(
				'label' 		=> 'Inv Add2',
				'help'			=> '',
				'type'			=> SC_DB_ADDRESS,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'inv_address.add_add3',
			array(
				'label' 		=> 'Inv Add2',
				'help'			=> '',
				'type'			=> SC_DB_ADDRESS,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'inv_address.add_town',
			array(
				'label' 		=> 'Inv Town',
				'help'			=> '',
				'type'			=> SC_DB_TOWN,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'inv_address.add_county',
			array(
				'label' 		=> 'Inv County',
				'help'			=> '',
				'type'			=> SC_DB_ADDRESS,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			'inv_address.add_postcode',
			array(
				'label' 		=> 'Inv Postcode',
				'help'			=> '',
				'type'			=> SC_DB_POSTCODE,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
		$this->addField(
			$this->getTableFieldName('modifyDate'),
			array(
				'label' 		=> 'Modified',
				'help'			=> '',
				'type'			=> SC_DB_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> true
			)
		);
	}
	function getPKName()
	{
		return $this->getTableFieldName('cus_custno');
	}
	function getSQLFromSection()
	{
			return
					$this->getTableName() . CR .
					TAB .	'JOIN address as del_address ON customer.cus_del_siteno = del_address.add_siteno AND customer.cus_custno = del_address.add_custno'. CR .
					TAB .	'JOIN address as inv_address ON customer.cus_del_siteno = inv_address.add_siteno AND customer.cus_custno = inv_address.add_custno';
	}
} //end class CNC_Customer
?>