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

class SC_ProcessInstance extends MPM_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function SC_ProcessInstance() {
		$this->__construct();
	}
	function __construct() {
		$this->setDisplayName('Process Instance');
		$this->setTableName('process_instance');
		parent::__construct();
	}
	function addFields(){
		$this->addField(
			$this->getTableName() . '.process_instance_id',
			array(
				'label' 		=> 'Process Instance ID',
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
			$this->getTableName() . '.process_id',
			array(
				'label' 		=> 'Process',
				'help'			=> 'Of which this is an instance',
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
												'process.process_id as value,' . CR . TAB .
												'name as description' . CR .
											'FROM' . CR . TAB .
												'process' . CR . 
											'ORDER BY' . CR . TAB .
												'description',
				'select_fields'
										=> ''
			)
		);
		$this->addField(
			$this->getTableName() . '.parent_process_instance_id',
			array(
				'label' 		=> 'parent instance',
				'help'			=> 'To which this process belongs',
				'type'			=> SC_DB_ID,
				'required'	=> false,
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
			$this->getTableName() . '.start_time',
			array(
				'label' 		=> 'start time',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default' 	=> null,
				'can_edit'	=> false,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		$this->addField(
			$this->getTableName() . '.scheduled_time',
			array(
				'label' 		=> 'scheduled start time',
				'help'			=> '',
				'type'			=> SC_DB_MYSQL_DATETIME,
				'required'	=> false,
				'unique'		=> false,
				'default' 	=> null,
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true,
				'validate_function'
										=> 'this:validateScheduledTime'
			)
		);
		$this->addField(
			$this->getTableName() . '.is_queued',
			array(
				'label' 		=> 'Queued for future start?',
				'help'			=> '',
				'type'			=> SC_DB_BOOL,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> false,
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true,
				'validate_function'
										=> 'this:validateIsQueued'
			)
		);
		$this->addField(
			$this->getTableName() . '.owner_user_id',
			array(
				'label' 		=> 'Process owner',
				'help'			=> '',
				'type'			=> SC_DB_ID,
				'required'	=> true,
				'unique'		=> false,
				'default' 	=> false,
				'can_edit'	=> true,
				'is_select'	=> false,
				'is_dropdown'
										=> true,
				'in_db' 		=> true,
				'select_statement'
										=> 
											'SELECT' . CR . TAB .
												'organisation.organisation_id as value,' . CR . TAB .
												'organisation.name as description' . CR .
											'FROM' . CR . TAB .
												'organisation' . CR . 
											'ORDER BY' . CR . TAB .
												'description',
				'select_fields'
										=> ''
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
	function validateScheduledTime($value, $min, $max)
	{
		$ret = true;

		if ( SC_Date::isMysqlDatetime( $value, $min, $max ) ){
			if (
				$this->getValue('process_instance.is_queued') &&
				(
					!SC_Date::isMysqlDatetime( $value ) ||	// no date
					$value < SC_Date::getDateNowMysql()		// in past
				)
		  ) {
				$this->setValidateErrorMessage( 'process_instance.scheduled_time', 'Must be in the future' );
				$ret = false;
			}
		}
		else{
			$ret = false;
		}
		return $ret;
	}
	function validateIsQueued($value, $min, $max)
	{
		$ret = true;

		if ( SC_String::isBoolean( $value, $min, $max ) ){
			if (
				$value  &&									
				(
					!SC_Date::isMysqlDatetime( $this->getValue( 'process_instance.scheduled_time' ) ) ||	// no date
					$this->getValue( 'process_instance.scheduled_time' ) < SC_Date::getDateNowMysql()		// in past
				)
		  ) {
				$this->setValidateErrorMessage( 'process_instance.is_queued', 'Must have a scheduled start time in the future' );
				$ret = false;
			}

		}
		else{
			$ret = false;
		}

		return $ret;
	} // end validateRaisedDate()
} //end class MPM_Process
?>