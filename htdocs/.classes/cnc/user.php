<?php
/**
*	CNC_User class
*
* class to hold authenticate and maintain currently logged-in orgainisation (AKA user)
* @author Karim Ahmed
* (C) Sweetcode Ltd 2005
* @access Public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_CNC_CLASSES . 'db.php');

class CNC_User extends CNC_DB {
/*
YOU MUST KEEP THIS ARRAY IN LINE WITH THE DATABASE OTHERWISE NASTY ERRORS WILL BITE YOUR BOLLOCKS!!!
*/
	/**
	* @access public
	*/
	function CNC_User() {
		$this->__construct();
	}
	function __construct() {
		$this->setDisplayName('User');
		parent::__construct('consultant');
	}
	function addFields(){
		$this->addField(
			$this->getTableName() . '.cns_consno',
			array(
				'label' 		=> 'ID',
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
			$this->getTableName() . '.cns_logname',
			array(
				'label' 		=> 'Login name',
				'help'			=> 'Full User name',
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
			$this->getTableName() . '.cns_password',
			array(
				'label' 		=> 'Password',
				'help'			=> 'password',
				'type'			=> SC_DB_PASSWORD,
				'required'	=> true,
				'unique'		=> true,
				'default' 	=> '',
				'can_edit'	=> true,
				'is_select'	=> false,
				'in_db' 		=> true
			)
		);
		parent::addFields();
	}
	/**
	* Tell SC_Authenticate what the authentication cookie key is
	*/
	function getUserID() {
		return $this->getValue('consultant.cns_consno');
	}
	function setUsername($value) {
		$this->setValue('consultant.cns_logname', $value);
	}
	function getUsername(){
		return $this->getValue('consultant.cns_logname');
	}
	function setPassword($value) {
		$this->setValue('consultant.cns_password', $value);
	}
	/**
	*	checkCredentials()
	*
	* Used by SC_Authenticate class to check user name against DB
	*
	* @access public
	*/
	function checkCredentials() {
		$this->checkConnection();
		$this->statement =
			$this->connection->prepare('
				SELECT '.
						$this->dbFieldNames() .
				' FROM
					consultant
				WHERE 
					cns_logname = ~1
				AND 
					cns_password = ~2'
			);

		$this->statement->execute(
			$this->getValue('consultant.cns_logname'),
			$this->getValue('consultant.cns_password')
		);
		
		if ($this->row = $this->statement->fetchAssoc()){
			return true;
		}
		else{
			return false;
		}	
	}
} //end class CNC_User
?>