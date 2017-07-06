<?php
/**
*	MPM_InstanceTask class
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_MPM_CLASSES . 'db.php');

class SC_InstanceTask extends MPM_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function SC_InstanceTask() {
		$this->__construct();
	}
	function __construct() {
		$this->setDisplayName('Process Instance Task');
		$this->setTableName('instance_task');
		parent::__construct();
	}
	function addFields(){
		$this->addField(
			$this->getTableFieldName('instance_task_id'),
			array(
				'label' 		=> 'Process Task ID',
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
			'process.name',
			array(
				'label' 		=> '',
				'help'			=> '',
				'type'			=> SC_DB_NAME,
				'required'	=> false,
				'unique'		=> false,
				'default' 	=> '',
				'can_edit'	=> false,
				'is_select'	=> true,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableFieldName('process_instance_id'),
			array(
				'label' 		=> 'Process Instance ID',
				'help'			=> 'Of which this is an task',
				'type'			=> SC_DB_ID,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> null,
				'can_edit'	=> true,
				'is_select'	=> false,
				'is_dropdown'
										=> true,
				'in_db' 		=> true,
				'select_statement'
										=> 
											'SELECT' . CR . TAB .
												'process_instance.process_instance_id as value,' . CR . TAB .
												'concat('.
													'process.name,'.
													' " Process - Instance " ,'.
													' process_instance.process_instance_id)' .
												'as description' . CR .
											'FROM' . CR . TAB .
												'process_instance' . CR .
											TAB . 'JOIN process ON process_instance.process_id = process.process_id' . CR .
											'ORDER BY' . CR . TAB .
												'process.name',
				'select_fields'
										=> ''
			)
		);
		$this->addField(
			$this->getTableFieldName('method_name'),
			array(
				'label' 		=> 'Method name of this task',
				'help'			=> '',
				'type'			=> SC_DB_METHOD_NAME,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableFieldName('start_time'),
			array(
				'label' 		=> 'Task start time',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableFieldName('end_time'),
			array(
				'label' 		=> 'Task end time',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		parent::addFields();
	}
	/*
	* getRow
	*
	*	@access public
	*	@param integer $pk_id Primary key value Optional
	*/
	function getRow($pk_id=false)
	{
		if ($pk_id) {
			$this->checkConnection();
			$this->statement =
				$this->connection->prepare(
					'SELECT ' . CR . 
					TAB .	$this->getDBSelectFieldNames() . CR .
					'FROM' . CR .
					TAB .	$this->getTableName() . CR .
					TAB .	'JOIN process_instance ON process_instance.process_instance_id = instance_task.process_instance_id' . CR .
					TAB .	'JOIN process ON process.process_id = process_instance.process_id' . CR .
					'WHERE' . CR .
					TAB .	$this->getPKName() . '= ~1'
				);
			return parent::getRow($pk_id);
		}
		else{
			return false;
		}
	}
} //end class MPM_Process
?>