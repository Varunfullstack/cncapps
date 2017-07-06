<?php
/**
*	MPM_Process class
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_MPM_CLASSES . 'db.php');

class SC_Process extends MPM_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function MPM_Process() {
		$this->__construct();
	}
	function __construct() {
		$this->setDisplayName('Process');
		$this->setTableName('process');
		parent::__construct();
	}
	function addFields(){
		$this->addField(
			$this->getTableName() . '.process_id',
			array(
				'label' 		=> 'Process ID',
				'help'			=> 'Internal, unique, system ID',
				'type'			=> SC_DB_ID,
				'required'	=> false,
				'unique'		=> true,
				'default' 	=> '',
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.name',
			array(
				'label' 		=> 'process name',
				'help'			=> 'Full process name',
				'type'			=> SC_DB_NAME,
				'required'	=> true,
				'unique'		=> true,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.class_name',
			array(
				'label' 		=> 'PHP Classname of process',
				'help'			=> '',
				'type'			=> SC_DB_NAME,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> false,
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.expect_duration_hours',
			array(
				'label' 		=> 'Expected duration hours',
				'help'			=> '',
				'type'			=> SC_DB_DECIMAL_POS_5_2,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> 0.2,
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.name',
			array(
				'label' 		=> 'process name',
				'help'			=> 'Full process name',
				'type'			=> SC_DB_NAME,
				'required'	=> true,
				'unique'		=> true,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.is_automatic',
			array(
				'label' 		=> 'Triggered without user',
				'help'			=> '',
				'type'			=> SC_DB_BOOL,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> false,
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		parent::addFields();
	}
} //end class MPM_Process
?>