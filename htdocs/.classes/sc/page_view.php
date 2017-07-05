<?php
/**
*	SC_PageView class
*
* @author Karim Ahmed
* (C) Sweetcode Ltd 2005
* @access Public
*/
//require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'db.php');

class SC_PageView extends SC_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function SC_PageView() {
		$this->__construct();
	}
	function __construct()
	{
		$this->setDisplayName('Page View');
		parent::__construct('page_view');
	}
	function addFields()
	{
		$this->addField(
			'page_view.page_view_id',
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
			'page_view.script_name',
			array(
				'label' 		=> 'Script Name',
				'help'			=> 'Name of the script page',
				'type'			=> SC_DB_SCRIPT_NAME,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			'page_view.name',
			array(
				'label' 		=> 'Name',
				'help'			=> '',
				'type'			=> SC_DB_NAME,
				'required'	=> true,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'page_view.display_fields',
			array(
				'label' 		=> 'Display Fields',
				'help'			=> '',
				'type'			=> SC_DB_STRING,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'page_view.order_by',
			array(
				'label' 		=> 'Order By',
				'help'			=> '',
				'type'			=> SC_DB_STRING,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		$this->addField(
			'page_view.filters',
			array(
				'label' 		=> 'Filters',
				'help'			=> '',
				'type'			=> SC_DB_STRING,
				'required'	=> false,
				'unique'		=> false,
				'default'		=> '',
				'in_db' 		=> true,
				'can_edit'	=> true,
				'is_select'	=> false
			)
		);
		parent::addFields();
	}
	function getRowByName($name, $script_name)
	{
		$this->checkConnection();
		
		$this->statement =
				$this->connection->prepare(
					'SELECT' . CR .
						TAB .	$this->getDBSelectFieldNames() . CR .
					'FROM' . CR .
						TAB .	$this->getTableName(). CR .
					'WHERE ' . $this->getTableFieldname('name') . '=\'' . $name . '\''.  CR .
					'AND '. $this->getTableFieldname('script_name') . '=\'' . $script_name . '\''
				);


		$this->statement->execute();
		if ($this_row = $this->statement->fetchAssoc()){
			$this->updateRowFrom($this_row);
		}
		$this->statement = false;
	}
} //end class SC_PageView
?>