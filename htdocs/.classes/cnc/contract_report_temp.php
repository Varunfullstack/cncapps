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

class CNC_ContractReport extends SC_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function CNC_ContractReport() {
		$this->__construct();
	}
	function __construct()
	{
		$this->setDisplayName('Contracts');
		parent::__construct('custitem');
	}
	/*
	*	Parent will call this automatically
	*/
	function addFields()
	{
    $this->addField(
      $this->getTableFieldName('cui_sla_response_hours'),
      array(
        'label'     => 'SLA',
        'help'      => '',
        'type'      => SC_DB_INTEGER_6,
        'required'  => false,
        'unique'    => true,
        'default'    => '',
        'can_edit'  => false,
        'is_select'  => false,
        'in_db'     => true
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
			'item.itm_desc',
			array(
				'label' 		=> 'Item',
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
			$this->getTableFieldName('cui_serial'),
			array(
				'label' 		=> 'Serial',
				'help'			=> '',
				'type'			=> SC_DB_STRING_10,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			$this->getTableFieldName('cui_desp_date'),
			array(
				'label' 		=> 'Start',
				'help'			=> '',
				'type'			=> SC_DB_UK_DATE,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			$this->getTableFieldName('cui_expiry_date'),
			array(
				'label' 		=> 'Expiry',
				'help'			=> '',
				'type'			=> SC_DB_UK_DATE,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			$this->getTableFieldName('cui_sale_price'),
			array(
				'label' 		=> 'Value',
				'help'			=> '',
				'type'			=> SC_DB_DECIMAL,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			$this->getTableFieldName('curGSCBalance'),
			array(
				'label' 		=> 'Balance',
				'help'			=> '',
				'type'			=> SC_DB_DECIMAL,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'customer.gscTopUpAmount',
			array(
				'label' 		=> 'Top-Up',
				'help'			=> '',
				'type'			=> SC_DB_DECIMAL,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			$this->getTableFieldName('renewalStatus'),
			array(
				'label' 		=> 'Renewal Status',
				'help'			=> 'R',
				'type'			=> SC_DB_STRING_1,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'in_db' 		=> true,
				'is_select'	=> false
			)
		);
	}
	function getPKName(){
		return 'custitem.cui_cuino';
	}
	function getSQLWhereConstraint(){
		return
			'AND customer.cus_custno<>'.CONFIG_SALES_STOCK_CUSTOMERID . CR .
			'AND custitem.cui_expiry_date <> 0000-00-00';
		

	}
	function getSQLFromSection()
	{
			return
					$this->getTableName() . CR .
					TAB .	'JOIN customer ON customer.cus_custno = custitem.cui_custno' . CR .
					TAB .	'JOIN item ON item.itm_itemno = custitem.cui_itemno' . CR .
					TAB .	'JOIN address ON address.add_siteno = custitem.cui_siteno AND' .CR .
					TAB . 'address.add_custno = custitem.cui_custno';
	}
} //end class
?>